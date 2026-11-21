from datetime import datetime
import psycopg2
from psycopg2.extras import RealDictCursor
from datetime import datetime, timezone
import requests
import config

class Database:
    def __init__(self, db_name):
        conn_params ={
            "host": config.CONF_DB_HOST,
            "port": config.CONF_DB_PORT,
            "user": config.CONF_DB_USER,
            "password": config.CONF_DB_PASSWORD,
            "database": db_name
        }

        self._conn = psycopg2.connect(**conn_params)
        self._cursor = self._conn.cursor(cursor_factory = RealDictCursor)

    def query(self, sql, params):
        self._cursor.execute(sql, params)
        result = self._cursor.rowcount
        return self._cursor.rowcount

    def update(self, sql, params):
        self._cursor.execute(sql, params)
        self._conn.commit()
        result = self._cursor.rowcount
        return result

    def insert(self, sql, params, return_id = None):
        self._cursor.execute(sql, params)
        self._conn.commit()

        if return_id:
            if self._cursor.description is not None:
                row = self._cursor.fetchone()
                return row['id'] if row else None
        return None

    def get_all(self, sql, params):
        self._cursor.execute(sql, params)
        result = self._cursor.fetchall()
        return result

    def get_one(self, sql, params):
        self._cursor.execute(sql, params)
        result = self._cursor.fetchone()
        return result

    def insert_results(self, data):
        result_id = self.insert(
            f"""INSERT into "results"
                (
                    "name", 
                    "parser_id", 
                    "build_id", 
                    "scout_base_url", 
                    "full_url", 
                    "start_time", 
                    "duration_sec",
                    "tests_total",
                    "tests_passed",
                    "tests_skipped",
                    "tests_failed",
                    "is_stored_locally"
                )
            values
                (
                    '{data['name']}',
                    {data['parser_id']},
                    {data['build_id']},
                    '{data['base_url']}',
                    '{data['full_url']}',
                    '{datetime.fromtimestamp(data['start_time'], tz=timezone.utc)}'::timestamptz,
                    {data['duration_sec']},
                    {data['tests_total']},
                    {data['tests_passed']},
                    {data['tests_skipped']},
                    {data['tests_failed']},
                    {data['is_stored_locally']}
            ) RETURNING id"""
        , (), True)

        return result_id

    def insert_tests(self, result_id, test):
        self.insert(
            f"""INSERT into "tests"
                (
                    "result_id", 
                    "identifier", 
                    "name", 
                    "description", 
                    "script_path", 
                    "full_url", 
                    "state", 
                    "duration_sec"
                )
            values
                (
                    {result_id},
                    '{test['identifier']}',
                    '{test['name']}',
                    '{test['description']}',
                    '{test['script_path']}',
                    '{test['full_url']}',
                    '{test['state']}',
                    {test['duration_sec']}
            )"""
        , ())

db = Database(config.CONF_DB_NAME_BOARDS)
db_results = Database(config.CONF_DB_NAME_RESULTS)


class Logger():
    _log_levels = {0: "CRITICAL", 1: "WARNING", 2: "INFO", 3: "DEBUG"}

    def __init__(self, file_path):
        self._path = file_path

    def debug(self, message):
        self.write(message, 3)

    def info(self, message):
        self.write(message, 2)

    def warning(self, message):
        self.write(message, 1)

    def crifical(self, message):
        self.write(message, 0)

    #levels: 0 - crifical, 1 - warning, 2 - info, 3 - debug
    def write(self, message, level = 2):
        now = datetime.now()
        current_time = now.strftime('%Y-%m-%d %H:%M:%S')

        log_line = f"[{current_time}] [{self._log_levels[level]}] {message}"
        with open(self._path, 'a') as log:
            print(log_line)
            log.write(log_line + "\n")

scout_log = Logger('../log/scout.log')
executor_log = Logger('../log/executor.log')


class CachedRequest:
    def __init__(self):
        self.post_cache = dict()
        self.get_cache = dict()

    def post(self, url, data, headers, timeout):
        if url not in self.post_cache:
            resp = requests.post(
                url=url,
                data=data,
                headers=headers,
                timeout=timeout
            )

            self.post_cache[url] = resp
        else:
            resp = self.post_cache[url]

        return resp

    def get(self, url, headers, cookies, timeout):
        if url not in self.get_cache:
            resp = requests.get(
                url=url, 
                headers=headers, 
                cookies=cookies,
                timeout=timeout,
            )
            self.get_cache[url] = resp
        else:
            resp = self.get_cache[url]

        return resp

cached_request = CachedRequest()
