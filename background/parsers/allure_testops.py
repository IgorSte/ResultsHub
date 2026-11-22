import re
import json
from general import (
    scout_log,
    cached_request,
)

class AllureTestOps:
    parser_id = 1
    max_results_to_check = 25
    domain_name = None
    project_id = None
    cookies = None
    headers = {
        "Accept": "application/json"
    }

    def __init__(self,
            login,
            password,
            base_url,
            last_build_checked,
            set_filter,
            test_filter
        ):
        self.login = login
        self.password = password
        self.base_url = base_url
        self.last_build_checked = last_build_checked
        self.set_filter = set_filter
        self.test_filter = test_filter

        if match := re.search(r'https://([^/]+)/project/(\d+)/launches', base_url):
            self.domain_name = match.group(1)
            self.project_id = match.group(2)
        else:
            scout_log.warning('Not able to parse base URL')


    def _auth(self):
        api_url = f'https://{self.domain_name}/api/login/system'
        resp = cached_request.post(
            url=api_url,
            data={
                "username": self.login,
                "password": self.password
            },
            headers=self.headers,
            timeout=15
        )
        if resp:
            if resp.status_code == 200:
                self.cookies = {
                    "ALLURE_TESTOPS_SESSION": resp.cookies.get("ALLURE_TESTOPS_SESSION")
                }
            else:
                scout_log.warning(f'Authentication at {api_url} failed, code: {resp.status_code}')
        else:
            scout_log.warning(f'Not able to authenticate to {api_url}')


    def _get_results_list(self, project_id) -> dict:
        api_url = f'https://{self.domain_name}/api/launch?page=0&size={self.max_results_to_check}&projectId={project_id}&preview=true&sort=id%2Cdesc'
        resp = cached_request.get(
            url=api_url,
            headers=self.headers,
            cookies=self.cookies,
            timeout=15,
        )
        if resp:
            if resp.status_code == 200:
                parsed_resp = json.loads(resp.text)
                if len(parsed_resp['content']) > 0:
                    return parsed_resp['content']
            else:
                scout_log.warning(f'API request to {api_url} failed, code: {resp.status_code}')
        return {}


    def _get_result_details(self, result_id) -> dict:
        api_url = f'https://{self.domain_name}/api/testresult?launchId={result_id}&page=0&size=1000&sort=createdDate%2CASC'
        resp = cached_request.get(
            url=api_url,
            headers=self.headers,
            cookies=self.cookies,
            timeout=15,
        )
        if resp:
            if resp.status_code == 200:
                parsed_resp = json.loads(resp.text)
                return parsed_resp
            else:
                scout_log.warning(f'API request to {api_url} failed, code: {resp.status_code}')
        return {}


    def _get_test_state(self, state) -> int:
        if state == 'failed':
            return 0
        if state == 'passed':
            return 1
        if state == 'skipped':
            return 2
        return -1


    def results(self) -> list:
        results = []
        self._auth()
        latest_results = self._get_results_list(self.project_id)

        if latest_results:
            for result in latest_results:
                if result['id'] > self.last_build_checked:
                    is_finished = True
                    in_progress = False
                    in_progress_url = ''
                    set_start_time = 2000000000
                    set_end_time = 0

                    # print(f'!!!!!!!!!!{result}')
                    # exit()

                    for job in result['jobs']:
                        # print(f'!!!!!!!! {job['stage']}')
                        if job['stage'] != 'finished':
                            is_finished = False
                        if job['stage'] == 'in_progress':
                            in_progress = True
                            in_progress_url = job['url']
                            if round(job['createdDate'] / 1000) < set_start_time:
                                set_start_time = round(job['createdDate'] / 1000)

                    # print(f'!!!!!!!! {in_progress}')
                    if in_progress:
                        data = {
                            "name": result['name'],
                            "parser_id": self.parser_id,
                            "build_id": result['id'],
                            "base_url": self.base_url,
                            "full_url": f"https://{self.domain_name}/launch/{result['id']}/tree",
                            "start_time": set_start_time,
                            "in_progress": True,
                            "in_progress_url": in_progress_url,
                        }
                        results.append(data)
                        scout_log.debug(f'Job is running for the URL: {result['full_url']}')
                        continue
                    if not is_finished:
                        scout_log.debug(f'Job if not finished for the result {result['id']} - skipping')
                        continue

                    # filtering tests by the name
                    if not re.search(self.set_filter, result['name']):
                        scout_log.debug(f'The run {result['id']} did not pass the set filter: "{self.set_filter}" vs "{result['name']}"')
                        continue

                    details = self._get_result_details(result['id'])
                    if 'content' in details:
                        tests_total = 0
                        tests_passed = 0
                        tests_skipped = 0
                        tests_failed = 0

                        tests_data = []


                        for test in details['content']:
                            test_data = dict()
                            test_data['identifier'] = test['fullName'] + test['name']
                            test_data['name'] = test['name']
                            test_data['description'] = test.get('description', '')
                            test_data['script_path'] = test['fullName']
                            test_data['full_url'] = f"https://{self.domain_name}/launch/{result['id']}/tree/{test['id']}"
                            test_data['state'] = self._get_test_state(test['status'])
                            test_data['duration_sec'] = round(test['duration'] / 1000)

                            if test['status'] == 'passed':
                                tests_passed += 1
                            elif test['status'] == 'skipped':
                                tests_skipped += 1
                            elif test['status'] == 'failed':
                                tests_failed += 1

                            if 'trace' in test:
                                test_data['failure_reason'] = test['trace']

                            if round(test['start'] / 1000) < set_start_time:
                                set_start_time = round(test['start'] / 1000)
                            if round(test['stop'] / 1000) > set_end_time:
                                set_end_time = round(test['stop'] / 1000)

                            tests_data.append(test_data)

                        tests_total = tests_passed + tests_skipped + tests_failed
                        if set_end_time == 0:
                            set_start_time = 0

                        data = {
                            "name": result['name'],
                            "parser_id": self.parser_id,
                            "build_id": result['id'],
                            "base_url": self.base_url,
                            "full_url": f"https://{self.domain_name}/launch/{result['id']}/tree",

                            "in_progress": False,
                            "is_stored_locally": False,

                            "tests_total": tests_total,
                            "tests_passed": tests_passed,
                            "tests_skipped": tests_skipped,
                            "tests_failed": tests_failed,

                            "start_time": set_start_time,
                            "duration_sec": set_end_time - set_start_time,

                            "tests": tests_data,
                        }

                        results.append(data)
                    else:
                        scout_log.debug(f'"content" is not found in the API response for launch: {result['id']}')

        return results

