import argparse
import os
import re
import json
import shutil
import subprocess
from pathlib import Path
from datetime import datetime
from general import (
    db,
    db_results,
    executor_log,
)

# parsers registry
from parsers.allure_local import AllureLocal
PARSERS_REGISTRY = {
    "AllureLocal": AllureLocal,
}
def get_parser_class(name: str):
    return PARSERS_REGISTRY.get(name, None)


executor_dir = 'executors/'
results_dir = '../results/'
instance_log = 'log.txt'


def is_process_running(pid: int) -> bool:
    try:
        os.kill(pid, 0)      # signal 0 = "does this PID exist?"
        return True
    except ProcessLookupError:
        return False         # no such process
    except PermissionError:
        return True          # process exists, but owned by another user
    except OSError:
        return False
    

executor_log.debug('Starting new iteration')

# reading credentials
parser = argparse.ArgumentParser(description="Executor")
parser.add_argument(
    "--credentials",
    type=str,
    default='',
    help="File with credentials",
)
args = parser.parse_args()

with open(args.credentials, "r", encoding="utf-8") as creds_f:
    credentials = json.load(creds_f)


# runs = db.get_all('SELECT * FROM runs WHERE parser_id > 0 AND scout_base_url IS NOT null', ())
executors = db.get_all('''
    SELECT e.*, p.class_name, p.credentials_name, s.name as set_name
    from executors as e, parsers as p, sets as s
    where e.parser_id = p.id and e.set_id = s.id order by state desc''', ())

for current_executor in executors:
    # "state" int default null, -- 1 - queued, 2 - starting, 3 - running, 4 - processing, 5 - done

    # getting the current executor data again to minimise race condition propability
    executor = db.get_one(f'''
        SELECT e.*, p.class_name, p.credentials_name, s.name as set_name
        from executors as e, parsers as p, sets as s
        where e.id = {current_executor['id']} and e.parser_id = p.id and e.set_id = s.id order by state desc''', ())

    executor_log.debug(f"Processing executor. ID: {executor['id']}, state: {executor['state']}")

    if executor['state'] == 1:
        # checking whether the machine is free
        concurrent_executors = db.get_all(f'''
            SELECT * from executors
            where state > 1 and state < 5 and machine_id = {executor['machine_id']} and id != {executor['id']}
        ''', ())

        if len(concurrent_executors) == 0:
            # starting run
            db.update(f'UPDATE executors SET "state" = 2 WHERE "id" = {executor["id"]}', ())
            db.update(f'UPDATE machines set executor_id = {executor["id"]} where id = {executor['machine_id']}', ())

            executor_log.info(f"Machine {executor['machine_id']} is free - starting run")

            working_dir = f"{executor_dir}{executor['id']}"
            shutil.rmtree(working_dir)
            os.mkdir(working_dir)


            with open(f"{working_dir}/set.json", "w", encoding="utf-8") as f:
                f.write(executor['set_json'])

            with open(f"{working_dir}/machine.json", "w", encoding="utf-8") as f:
                f.write(executor['machine_json'])

            with open(f"{working_dir}/commandline.sh", "w", encoding="utf-8") as f:
                f.write(executor['commandline'])

            if executor['git_url']:
                executor_log.debug(f"Syncing Git from '{executor['git_url']}' to '{working_dir}'")
                # TODO: download git
                pass

            if executor['local_artifacts']:
                executor_log.debug(f"Copying local artifacts from '{executor['local_artifacts']}' to '{working_dir}'")
                src = Path(executor['local_artifacts'])
                if src.is_dir():
                    shutil.copytree(
                        executor['local_artifacts'],
                        working_dir,
                        dirs_exist_ok=True)
                else:
                    shutil.copy2(executor['local_artifacts'], working_dir)

            subprocess.run(
                f"chmod a+x {working_dir}/commandline.sh",
                shell=True,
                text=True,
                check=False
            )

            # starting the run itself
            result = subprocess.run(
                f"nohup ./commandline.sh > {instance_log} 2>&1 < /dev/null & echo pid=$!",
                cwd=working_dir,
                shell=True,
                capture_output=True,
                text=True,
                check=False
            )

            pid = None
            match = re.search(r'pid=(\d+)', result.stdout)
            if match:
                pid = int(match.group(1))
                executor_log.info(f"Run process is started with PID: {pid}")

                with open(f"{working_dir}/pid.txt", "w", encoding="utf-8") as f:
                    f.write(str(pid))

                db.update(f'''
                    UPDATE executors SET 
                    "state" = 3, 
                    pid = {pid},
                    working_dir = '{working_dir}',
                    log_file_path = '{working_dir}/{instance_log}'
                    WHERE "id" = {executor["id"]}''', ())
            else:
                executor_log.crifical(f"Not able to start the process: {result.stderr}")
        else:
            executor_log.info("No free machine found")


    if executor['state'] == 3:
        if not is_process_running(executor['pid']):
            db.update(f'UPDATE executors SET "state" = 4 WHERE "id" = {executor["id"]}', ())
            executor_log.info(f"Execition for run ID {executor['run_id']} is done - processing")

            parsers_class = get_parser_class(executor["class_name"])
            parser_instance = parsers_class(
                working_dir=executor['working_dir'],
                set_name=executor['set_name'],
            )
            results_data = parser_instance.results()
            executor_log.info('Results received - uploading to the DB')

            if len(results_data["tests"]) > 0:
                result_id = db_results.insert_results(results_data)

                for test in results_data["tests"]:
                    db_results.insert_tests(result_id, test)
                executor_log.debug(f'Result {result_id} added to the DB with {len(results_data["tests"])} tests')
                
                db_results.insert(f'''INSERT into "associations" ("result_id", "run_id") values ({result_id}, {executor["run_id"]})''', ())

                execution_log = None
                with open(f'{executor['log_file_path']}', 'r', encoding='utf-8') as f:
                    execution_log = f.read()
                if execution_log:
                    db_results.insert(f'''UPDATE "results" set "execution_log" = %s where "id" = {result_id}''', (execution_log,))

                os.mkdir(f'{results_dir}{result_id}')
                shutil.copytree(
                    f'{executor['working_dir']}/allure-report/',
                    f'{results_dir}{result_id}/',
                    dirs_exist_ok=True)

            db.update(f'''UPDATE executors SET 
                "state" = 5,
                "finish_time" = '{datetime.now()}'::timestamptz
                WHERE "id" = {executor["id"]}''', ())

            db.update(f'UPDATE runs SET "executor_id" = null WHERE "id" = {executor["run_id"]}', ())
            db.update(f'UPDATE machines SET "executor_id" = null WHERE "id" = {executor["machine_id"]}', ())
            executor_log.info(f"Results for run ID {executor['run_id']} processed")

            shutil.rmtree(executor['working_dir'])
        else:
            executor_log.debug(f"Execition for run ID {executor['run_id']} is still in progress")
