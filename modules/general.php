<?php
    require_once('config.php');

    class Database
    {
        private $dbname;
        private $connection;
        private $host;
        private $port;
        private $user;
        private $pass;

        public function __construct($db_name)
        {
            global $conf_db_host;
            global $conf_db_port;
            global $conf_db_user;
            global $conf_db_password;

            $this->dbname = $db_name;
            $this->host = $conf_db_host;
            $this->port = $conf_db_port;
            $this->user = $conf_db_user;
            $this->pass = $conf_db_password;

            $this->connect();
        }


        public function connect()
        {
            $this->connection = pg_connect("host=$this->host port=$this->port dbname=$this->dbname user=$this->user password=$this->pass")
            or die('Ошибка соединения: ' . pg_last_error());
        }


        public function query($sql, $params = [])
        {
            $result = pg_query_params($this->connection, $sql, $params) or die('Ошибка запроса: ' . pg_last_error());
            $affected_rows = pg_affected_rows($result);
            return $affected_rows;
        }


        public function insert($sql, $params = [])
        {
            $result = pg_query_params($this->connection, $sql, $params) or die('Ошибка запроса: ' . pg_last_error());
            $id_result = pg_query($this->connection, "SELECT LASTVAL()");
            $row = pg_fetch_row($id_result);
            $last_oid = $row[0];
            return $last_oid;
        }


        public function get_all($sql, $params = [])
        {
            $result = pg_query_params($this->connection, $sql, $params) or die('Ошибка запроса: ' . pg_last_error());
            $arr = pg_fetch_all($result, PGSQL_ASSOC);
            return $arr;
        }


        public function get_one($sql, $params = [])
        {
            $result = pg_query_params($this->connection, $sql, $params) or die('Ошибка запроса: ' . pg_last_error());
            $arr = pg_fetch_assoc($result);
            return $arr;
        }


        public function escape($value)
        {
            return pg_escape_string($this->connection, $value);
        }


        public function __destruct()
        {
            $this->connection = null;
        }
    }

    $db = new Database($conf_db_name_boards);
    $db_results = new Database($conf_db_name_results);


    function get_passRate_color($percent)
    {        
        $green = [0, 255, 0];
        $yellow = [255, 255, 0];
        $red = [255, 0, 0];
        
        if ($percent >= 90) {
            $t = ($percent - 90) / 10;
            $r = $yellow[0] + ($green[0] - $yellow[0]) * $t;
            $g = $yellow[1] + ($green[1] - $yellow[1]) * $t;
            $b = $yellow[2] + ($green[2] - $yellow[2]) * $t;
            
        } elseif ($percent >= 80) {
            $t = ($percent - 80) / 10;
            $r = $red[0] + ($yellow[0] - $red[0]) * $t;
            $g = $red[1] + ($yellow[1] - $red[1]) * $t;
            $b = $red[2] + ($yellow[2] - $red[2]) * $t;
            
        } else {
            $r = $red[0];
            $g = $red[1];
            $b = $red[2];
        }
        
        $r = round($r);
        $g = round($g);
        $b = round($b);


        $front_intensity = 0.6;
        $front_r = round($r * (1 - $front_intensity));
        $front_g = round($g * (1 - $front_intensity));
        $front_b = round($b * (1 - $front_intensity));

        $back_intensity = 0.35;
        $back_r = round(255 - (255 - $r) * $back_intensity);
        $back_g = round(255 - (255 - $g) * $back_intensity);
        $back_b = round(255 - (255 - $b) * $back_intensity);
        
        return array("rgb($front_r, $front_g, $front_b)", "rgb($back_r, $back_g, $back_b)");
    }


    function calculate_passrate($total, $passed, $skipped)
    {
        $passrate = 0;
        if ($total && ($total != $skipped)) $passrate = round(($passed + $skipped) / $total * 100, 1);
        return $passrate;
    }


    function verbose_duration($seconds)
    {
        $text = '';

        if ($seconds < 60) $text = $seconds.'s';
        elseif ($seconds < 3600)
        {
            $min = floor($seconds / 60);
            $sec = $seconds - $min * 60;
            $text = $min.'m '.$sec.'s';
        }
        elseif ($seconds < 3600 * 24)
        {
            $hour = floor($seconds / 3600);
            $min = round(($seconds - $hour * 3600) / 60);
            $text = $hour.'h '.$min.'m';
        }
        else
        {
            $day = floor($seconds / (3600 * 24));
            $hour = round(($seconds - $day * (3600 * 24)) / 3600);
            $text = $day.'d '.$hour.'h';
        }

        return $text;
    }


    function verbose_int($n)
    {
        if ($n > 0) return "+$n";
        else return $n;
    }


    function escape_string($string)
    {
        if ($string) return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        else return $string;
    }


    function parse_comment($string)
    {
        $jenkins_id_pattern = "/([A-Z]{2,6}-\d{3,6})/";

        $string = preg_replace(
            $jenkins_id_pattern,
            '<a style="text-decoration: none" target="_blank" href="https://tracker.yandex.ru/$1">$1</a>',
            $string);

        return $string;
    }


    function get_n_latest_results($run_id, $number)
    {
        global $db;
        global $db_results;

        $run = $db->get_one("SELECT * from runs where id = $1", [$run_id]);
        
        # in case results are filtered, the latest results may be not suitable, need to cehck deeper
        $select_limit = $number;
        if ($run['test_filter']) $select_limit = 1000;

        $results = $db_results->get_all("SELECT a.result_id, r.* from associations as a join results as r on r.id = a.result_id where run_id = $1 order by r.id desc limit $2", [$run_id, $select_limit]);

        if ($run['test_filter'] && strlen($run['test_filter']) > 0)
        {
            $filtered_results = [];

            foreach ($results as $result)
            {
                $result['tests_total'] = 0;
                $result['tests_passed'] = 0;
                $result['tests_skipped'] = 0;
                $result['tests_failed'] = 0;
                $result['duration_sec'] = 0;

                $tests = $db_results->get_all("SELECT * from tests where result_id = $1 order by id asc", [$result['id']]);
                foreach ($tests as $test)
                {
                    if (preg_match('!'.$run['test_filter'].'!', $test['name']) || 
                        preg_match('!'.$run['test_filter'].'!', $test['script_path']))
                    {
                        $result['tests_total']++;
                        if ($test['state'] == 0) $result['tests_failed']++;
                        elseif ($test['state'] == 1) $result['tests_passed']++;
                        elseif ($test['state'] == 2) $result['tests_skipped']++;
                        $result['duration_sec'] += $test['duration_sec'];
                    }
                }

                if ($result['tests_total']) $filtered_results[] = $result;

                # to ensure that not more than expeted number of results returned
                if (count($filtered_results) >= $number) break;
            }

            $results = $filtered_results;
        }
        
        return $results;
    }


    function choose_machine_for_executor($board_id)
    {
        global $db;

        # getting all awailable machines
        $machines = $db->get_all("SELECT * from machines where board_id = $1 and status != 2 order by name asc", [$board_id]);

        if ($machines) {
            # returning the first free machine
            foreach ($machines as $machine) {
                if (!$machine['executor_id']) return $machine;
            }

            # returning any machine if all are busy
            return $machines[0];
        }

        return null;
    }
?>
