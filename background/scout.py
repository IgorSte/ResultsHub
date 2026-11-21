import argparse
import json
from datetime import datetime, timezone
from general import (
    db,
    db_results,
    scout_log,
)

# parsers registry
from parsers.allure_testops import AllureTestOps
PARSERS_REGISTRY = {
    "AllureTestOps": AllureTestOps,
}
def get_parser_class(name: str):
    return PARSERS_REGISTRY.get(name, None)


scout_log.debug('Starting new iteration')

# reading credentials
parser = argparse.ArgumentParser(description="Scout")
parser.add_argument(
    "--credentials",
    type=str,
    default='',
    help="File with credentials",
)
args = parser.parse_args()

with open(args.credentials, "r", encoding="utf-8") as creds_f:
    credentials = json.load(creds_f)

runs = db.get_all('SELECT r.*, p.class_name, p.credentials_name FROM runs as r LEFT JOIN parsers as p ON r.scout_parser_id = p.id WHERE r.scout_parser_id > 0 AND r.scout_base_url IS NOT null', ())
for run in runs:
    # print(run)

    if run["mode"] == 0 and run["scout_parser_id"] > 0:
        scout_log.debug(f'Processing run {run["name"]}, URL: {run["scout_base_url"]}, last_build_checked: {run["scout_last_build_checked"]}')

        if run["scout_base_url"]:
            parser_creds = credentials[run["credentials_name"]]
            parsers_class = get_parser_class(run["class_name"])

            parser_instance = parsers_class(
                parser_creds['login'],
                parser_creds['password'],
                run["scout_base_url"],
                run["scout_last_build_checked"],
                run["scout_set_filter"],
                run["test_filter"],
            )
            results_data = parser_instance.results()
            scout_log.info(f'Reseived {len(results_data)} results')

            last_build_checked = run["scout_last_build_checked"]
            in_progress = False
            in_progress_url = ''

            # the results_data are from the most recent to the oldest
            # so need to add to DB in the reverced order
            for data in reversed(results_data):
                if not data['in_progress']:
                    if last_build_checked < data['build_id']:
                        last_build_checked = data['build_id']

                    if len(data["tests"]) > 0:
                        existent_results = db_results.get_all(f"SELECT id FROM results WHERE full_url = '{data["full_url"]}'", ())
                        if not existent_results:
                            result_id = db_results.insert_results(data)

                            for test in data["tests"]:
                                db_results.insert_tests(result_id, test)
                            scout_log.debug(f'Result {result_id} added to the DB with {len(data["tests"])} tests')
                        else:
                            result_id = existent_results[0]['id']
                            scout_log.debug(f'Result {result_id} is already added in the DB')

                        db_results.insert(f"""INSERT into "associations" ("result_id", "run_id") values ({result_id}, {run["id"]});""", ())
                else:
                    in_progress = True
                    in_progress_url = data['in_progress']

            db.update(f"""UPDATE runs SET
                    "scout_last_build_checked" = {last_build_checked},
                    "scout_run_in_progress" = {in_progress},
                    "scout_run_in_progress_url" = '{in_progress_url}'
                WHERE "id" = {run["id"]}""", ())
            scout_log.info('Results processed fine')
    else:
        scout_log.warning(f"Not able to find parser for the run {run["id"]}")
