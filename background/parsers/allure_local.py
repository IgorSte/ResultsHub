import os
import re
import json
import subprocess

class AllureLocal:
    parser_id = 2
    json_to_process = 'allure-report/data/suites.json'

    def __init__(self,
        working_dir,
        set_name
    ):
        self.working_dir = working_dir
        self.set_name = set_name


    def results(self):
        subprocess.run(
            "allure generate allure-results -o allure-report --clean",
            cwd=self.working_dir,
            shell=True,
            capture_output=True,
            text=True,
            check=False
        )

        if not os.path.exists(f'{self.working_dir}/{self.json_to_process}'):
            return None
        
        with open(f'{self.working_dir}/{self.json_to_process}', "r", encoding="utf-8") as allure_f:
            allure_data = json.load(allure_f)

        set_start_time = 2000000000
        set_end_time = 0

        tests_total = 0
        tests_passed = 0
        tests_skipped = 0
        tests_failed = 0

        tests_data = []
        for suite in allure_data['children'][0]['children']:
            for test in suite['children']:
                test_data = dict()

                test_data['identifier'] = suite['name'] + test['name']
                test_data['name'] = test['name']
                test_data['description'] = test.get('description', '')
                test_data['script_path'] = ''
                test_data['full_url'] = ''
                test_data['state'] = self._get_test_state(test['status'])
                test_data['duration_sec'] = round(test['time']['duration'] / 1000)

                if test['status'] == 'passed':
                    tests_passed += 1
                elif test['status'] == 'skipped':
                    tests_skipped += 1
                elif test['status'] == 'failed':
                    tests_failed += 1

                if 'trace' in test:
                    test_data['failure_reason'] = test['trace']

                if round(test['time']['start'] / 1000) < set_start_time:
                    set_start_time = round(test['time']['start'] / 1000)
                if round(test['time']['stop'] / 1000) > set_end_time:
                    set_end_time = round(test['time']['stop'] / 1000)

                tests_data.append(test_data)

        tests_total = tests_passed + tests_skipped + tests_failed
        if set_end_time == 0:
            set_start_time = 0

        results = {
            "name": self.set_name,
            "parser_id": self.parser_id,
            "build_id": 0,
            "base_url": '',
            "full_url": '',

            "in_progress": False,
            "is_stored_locally": True,

            "tests_total": tests_total,
            "tests_passed": tests_passed,
            "tests_skipped": tests_skipped,
            "tests_failed": tests_failed,

            "start_time": set_start_time,
            "duration_sec": set_end_time - set_start_time,

            "tests": tests_data,
        }

        return results


    def _get_test_state(self, state) -> int:
            if state == 'failed':
                return 0
            if state == 'passed':
                return 1
            if state == 'skipped':
                return 2
            return -1