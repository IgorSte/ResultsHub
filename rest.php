<?php
    require_once('modules/config.php');
    require_once('modules/general.php');
    require_once('modules/render.php');
    
    $method = $_SERVER['REQUEST_METHOD'];
    $request = isset($_REQUEST['request']) ? explode('/', rtrim($_REQUEST['request'], '/')) : [];

    if ($method == 'GET' && count($request) == 2 && $request[0] == 'details') {
        $response = rest_details_get();
    }
    elseif ($method == 'GET' && count($request) == 2 && $request[0] == 'chart') {
        $response = rest_chart_get();
    }
    elseif ($method == 'GET' && count($request) == 2 && $request[0] == 'result') {
        $response = rest_result_get_html();
        header("Content-Type: text/html; charset=UTF-8");
        echo $response;
        exit;
    }
    elseif ($method == 'GET' && count($request) == 2 && $request[0] == 'run_start') {
        $response = rest_run_start_get();
    }
    elseif ($method == 'POST' && count($request) == 2 && $request[0] == 'run_start') {
        $response = rest_run_start_post();
    }
    elseif ($method == 'GET' && count($request) == 2 && $request[0] == 'executor_log') {
        $response = executor_log_get();
        header("Content-Type: text/plain; charset=UTF-8");
        echo $response;
        exit;
    }
    elseif ($method == 'GET' && count($request) == 2 && $request[0] == 'result_log') {
        $response = result_log_get();
        header("Content-Type: text/plain; charset=UTF-8");
        echo $response;
        exit;
    }

    // UI change APIs
    elseif ($method == 'PATCH' && count($request) == 2 && $request[0] == 'project') {
        $response = rest_project_patch();
    }
    elseif ($method == 'POST' && count($request) == 2 && $request[0] == 'project') {
        $response = rest_project_post();
    }
    elseif ($method == 'DELETE' && count($request) == 2 && $request[0] == 'project') {
        $response = rest_project_delete();
    }
    elseif ($method == 'PATCH' && count($request) == 2 && $request[0] == 'board') {
        $response = rest_board_patch();
    }
    elseif ($method == 'POST' && count($request) == 2 && $request[0] == 'board') {
        $response = rest_board_post();
    }
    elseif ($method == 'DELETE' && count($request) == 2 && $request[0] == 'board') {
        $response = rest_board_delete();
    }
    elseif ($method == 'PATCH' && count($request) == 2 && $request[0] == 'section') {
        $response = rest_section_patch();
    }
    elseif ($method == 'POST' && count($request) == 2 && $request[0] == 'section') {
        $response = rest_section_post();
    }
    elseif ($method == 'DELETE' && count($request) == 2 && $request[0] == 'section') {
        $response = rest_section_delete();
    }
    elseif ($method == 'PATCH' && count($request) == 2 && $request[0] == 'set') {
        $response = rest_set_patch();
    }
    elseif ($method == 'POST' && count($request) == 2 && $request[0] == 'set') {
        $response = rest_set_post();
    }
    elseif ($method == 'DELETE' && count($request) == 2 && $request[0] == 'set') {
        $response = rest_set_delete();
    }
    elseif ($method == 'PATCH' && count($request) == 2 && $request[0] == 'machine') {
        $response = rest_machine_patch();
    }
    elseif ($method == 'POST' && count($request) == 2 && $request[0] == 'machine') {
        $response = rest_machine_post();
    }
    elseif ($method == 'DELETE' && count($request) == 2 && $request[0] == 'machine') {
        $response = rest_machine_delete();
    }
    elseif ($method == 'PATCH' && count($request) == 2 && $request[0] == 'run') {
        $response = rest_run_patch();
    }
    elseif ($method == 'POST' && count($request) == 2 && $request[0] == 'run') {
        $response = rest_run_post();
    }
    elseif ($method == 'DELETE' && count($request) == 2 && $request[0] == 'run') {
        $response = rest_run_delete();
    }
    elseif ($method == 'PATCH' && count($request) == 2 && $request[0] == 'order') {
        $response = rest_order_patch();
    }
    else {
        $response = [
            'success' => false,
            'error' => 'unknown request'
        ];
    }


    // sleep(3);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($response);


    function get_payload()
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }


    function rest_details_get()
    {
        global $request;

        $run_id = $request[1];
        $html_data = render_run_details($run_id);

        $response = [
            'success' => true,
            'data' => $html_data
        ];

        return $response;
    }


    function rest_chart_get()
    {
        global $request;

        $run_id = $request[1];
        $chart_data = get_chart_data($run_id);

        $response = [
            'success' => true,
            'data' => $chart_data
        ];

        return $response;
    }


    function rest_run_start_get()
    {
        global $request;
        global $db;

        $id = $request[1];

        $run_data = $db->get_one("SELECT r.*, s.board_id from runs as r, sections as s where r.id = $1 and r.section_id = s.id", [$id]);
        $set_data = $db->get_one("SELECT s.*, p.name as parser_name from sets as s, parsers as p where s.id = $1 and s.parser_id = p.id", [$run_data['executor_set_id']]);
        $machine_data = choose_machine_for_executor($run_data['board_id']);

        if ($machine_data) {
            $html = render_run_start_form($run_data, $set_data, $machine_data);

            $response = [
                'success' => true,
                'data' => $html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => '',
                'error' => 'No suitable machine found'
            ];
        }

        return $response;
    }


    function rest_run_start_post()
    {
        global $request;
        global $db;

        $run_id = $request[1];
        $payload = get_payload();
        $set_id = $payload['set_id'];
        $machine_id = $payload['machine_id'];
        $parser_id = $payload['parser_id'];
        $set_git_url = $payload['set_git_url'];
        $set_local_artifacts = $payload['set_local_artifacts'];
        $set_commandline = $payload['set_commandline'];
        $set_json = $payload['set_json'];
        $machine_json = $payload['machine_json'];

        $new_id = $db->insert("INSERT into executors 
            (state, run_id, set_id, machine_id, parser_id, git_url, local_artifacts, commandline, set_json, machine_json) values 
            (1, $run_id, $set_id, $machine_id, $parser_id, '$set_git_url', '$set_local_artifacts', $1, $2, $3)", 
            [$set_commandline, $set_json, $machine_json]);
        
        if ($new_id > 0) {
            $result = $db->query("UPDATE runs set executor_id = $new_id where id = $run_id");
            $run = $db->get_one("SELECT * from runs where id = $1", [$run_id]);
            list($data_html, $stats) = render_run($run);

            $response = [
                'success' => true,
                'data' => $data_html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }


    function executor_log_get()
    {
        global $request;
        global $db;

        $id = $request[1];
        $response = '';

        $executor_data = $db->get_one("SELECT log_file_path from executors where id = $1", [$id]);

        $log_file_path = 'background/'.$executor_data['log_file_path'];
        if (file_exists($log_file_path)) {
            $response = file_get_contents($log_file_path);
        }

        return $response;
    }


    function result_log_get()
    {
        global $request;
        global $db_results;

        $id = $request[1];
        $response = '';

        $result_data = $db_results->get_one("SELECT execution_log from results where id = $1", [$id]);
        $response = $result_data['execution_log'];

        return $response;
    }


    function rest_project_patch()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $id = $request[1];
        $name = $payload['name'];

        $result = $db->query("UPDATE projects set name = $1 where id = $id", [$name]);

        if ($result > 0) {
            $response = [
                'success' => true,
                'data' => ''
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_project_post()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $name = $payload['name'];

        $result = $db->insert("INSERT into projects (name) values ($1)", [$name]);

        if ($result > 0) {
            $response = [
                'success' => true,
                'data' => ''
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_project_delete()
    {
        global $request;
        global $db;

        $id = $request[1];

        $validation = $db->get_one("SELECT count(*) from boards where project_id = $1", [$id]);
        if ($validation['count'] > 0) {
            $response = [
                'success' => false,
                'data' => '',
                'error' => 'Project is not empty!'
            ];

            return $response;
        }

        $result = $db->query("DELETE from projects where id = $1", [$id]);

        if ($result > 0) {
            $response = [
                'success' => true,
                'data' => ''
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }


    function rest_board_patch()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $id = $request[1];
        $name = $payload['name'];
        $comment = $payload['comment'];
        $project_id = $payload['project_id'];

        $result = $db->query("UPDATE boards set
            name = $1,
            comment = $2,
            project_id = $project_id
        where id = $id", [$name, $comment]);

        if ($result > 0) {
            $data_html = render_board($id);

            $response = [
                'success' => true,
                'data' => $data_html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_board_post()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $name = $payload['name'];
        $comment = $payload['comment'];
        $project_id = $payload['project_id'];

        $result = $db->insert("INSERT into boards (name, comment, project_id) values ($1, $2, $project_id)", 
            [$name, $comment]);

        if ($result > 0) {
            $response = [
                'success' => true,
                'data' => ''
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }


    function rest_board_delete()
    {
        global $request;
        global $db;

        $payload = get_payload();
        $id = $request[1];

        $validation = $db->get_one("SELECT count(*) from sections where board_id = $1", [$id]);
        if ($validation['count'] > 0) {
            $response = [
                'success' => false,
                'data' => '',
                'error' => 'Board is not empty or have a machine assigned!'
            ];

            return $response;
        }

        $result = $db->query("DELETE from boards where id = $1", [$id]);

        if ($result > 0) {
            $response = [
                'success' => true,
                'data' => ''
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }


    function rest_section_patch()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $id = $request[1];
        $name = $payload['name'];
        $comment = $payload['comment'];
        $color = $payload['color'];
        $board_id = $payload['board_id'];

        $result = $db->query("UPDATE sections set
            name = $1,
            comment = $2,
            color = '$color',
            board_id = $board_id
        where id = $id", [$name, $comment]);

        if ($result > 0) {
            $section = $db->get_one("SELECT * from sections where id = $1", [$id]);
            $data_html = render_section($section);

            $response = [
                'success' => true,
                'data' => $data_html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_section_post()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $name = $payload['name'];
        $comment = $payload['comment'];
        $color = $payload['color'];
        $board_id = $payload['board_id'];

        $result = $db->get_one("SELECT max(sort_order) from sections where board_id = $1;", [$board_id]);
        $max_sort_order = $result['max'] + 1;

        $new_id = $db->insert("INSERT into sections (name, comment, color, board_id, sort_order)
            values ($1, $2, '$color', $board_id, $max_sort_order)", [$name, $comment]);

        if ($new_id > 0) {
            $section = $db->get_one("SELECT * from sections where id = $1", [$new_id]);
            $data_html = render_section($section);

            $response = [
                'success' => true,
                'data' => $data_html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_section_delete()
    {
        global $request;
        global $db;

        $payload = get_payload();
        $id = $request[1];

        $validation = $db->get_one("SELECT count(*) from runs where section_id = $1", [$id]);
        if ($validation['count'] > 0) {
            $response = [
                'success' => false,
                'data' => '',
                'error' => 'Section is not empty!'
            ];

            return $response;
        }


        $result = $db->query("DELETE from sections where id = $1", [$id]);

        if ($result > 0) {
            $response = [
                'success' => true,
                'data' => ''
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_set_patch()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $id = $request[1];
        $name = $payload['name'];
        $parser_id = $payload['parser_id'];
        $comment = $payload['comment'];
        $json = $payload['json'];
        $git_url = $payload['git_url'];
        $local_artifacts = $payload['local_artifacts'];
        $commandline = $payload['commandline'];

        $result = $db->query("UPDATE sets set
            name = $1,
            parser_id = $parser_id,
            comment = $2,
            json = $3,
            git_url = '$git_url',
            local_artifacts = '$local_artifacts',
            commandline = $4
        where id = $id", [$name, $comment, $json, $commandline]);

        if ($result > 0) {
            $data_html = render_sets();

            $response = [
                'success' => true,
                'data' => $data_html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_set_post()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $name = $payload['name'];
        $parser_id = $payload['parser_id'];
        $comment = $payload['comment'];
        $json = $payload['json'];
        $git_url = $payload['git_url'];
        $local_artifacts = $payload['local_artifacts'];
        $commandline = $payload['commandline'];

        $new_id = $db->insert("INSERT into sets (name, parser_id, comment, json, git_url, local_artifacts, commandline)
            values ($1, $parser_id, $2, $3, '$git_url', '$local_artifacts', $4)", 
            [$name, $comment, $json, $commandline]);

        if ($new_id > 0) {
            $data_html = render_sets();

            $response = [
                'success' => true,
                'data' => $data_html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_set_delete()
    {
        global $request;
        global $db;

        $payload = get_payload();
        $id = $request[1];

        $validation = $db->get_all("SELECT name from runs where executor_set_id = $1", [$id]);
        if (count($validation)) {
            $used_by = array();

            foreach ($validation as $v) {
                $used_by[] = $v['name'];
            }

            $response = [
                'success' => false,
                'data' => '',
                'error' => 'Set is used by the runs: '.implode(', ', $used_by)
            ];

            return $response;
        }

        $result = $db->query("DELETE from sets where id = $1", [$id]);

        if ($result > 0) {
            $response = [
                'success' => true,
                'data' => ''
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_machine_patch()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $id = $request[1];
        $name = $payload['name'];
        $comment = $payload['comment'];
        $json = $payload['json'];
        $hc_ip_port = $payload['hc_ip_port'];
        $board_id = $payload['board_id'];

        $result = $db->query("UPDATE machines set
            name = $1,
            comment = $2,
            json = $3,
            board_id = $board_id,
            hc_ip_port = '$hc_ip_port'
        where id = $id", [$name, $comment, $json]);

        if ($result > 0) {
            $machine = $db->get_one("SELECT * from machines where id = $1", [$id]);
            $data_html = render_machine($machine);

            $response = [
                'success' => true,
                'data' => $data_html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_machine_post()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $name = $payload['name'];
        $comment = $payload['comment'];
        $json = $payload['json'];
        $board_id = $payload['board_id'];
        $hc_ip_port = $payload['hc_ip_port'];

        $new_id = $db->insert("INSERT into machines (name, comment, json, board_id, hc_ip_port)
            values ($1, $2, $3, $board_id, '$hc_ip_port')", [$name, $comment, $json]);

        if ($new_id > 0) {
            $data_html = render_machines();

            $response = [
                'success' => true,
                'data' => $data_html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_machine_delete()
    {
        global $request;
        global $db;

        $payload = get_payload();
        $id = $request[1];

        $result = $db->query("DELETE from machines where id = $1", [$id]);

        if ($result > 0) {
            $response = [
                'success' => true,
                'data' => ''
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_run_patch()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $id = $request[1];
        $name = $payload['name'];
        $section_id = $payload['section_id'];
        $tags = $payload['tags'];
        $test_filter = $payload['test_filter'];
        $comment = $payload['comment'];
        $mode = $payload['mode'];
        $scout_parser_id = $payload['scout_parser_id'];
        $scout_base_url = $payload['scout_base_url'];
        $scout_set_filter = $payload['scout_set_filter'];
        $executor_set_id = $payload['executor_set_id'];
        $manual_parser_id = $payload['manual_parser_id'];

        if ($mode != 1) $executor_set_id = 0;

        $result = $db->query("UPDATE runs set
            name = $1,
            comment = $2,
            section_id = $section_id,
            tags = $3,
            test_filter = $4,
            mode = $mode,
            scout_parser_id = $scout_parser_id,
            scout_base_url = '$scout_base_url',
            scout_set_filter = $5,
            executor_set_id = $executor_set_id,
            manual_parser_id = $manual_parser_id
        where id = $id", [$name, $comment, $tags, $test_filter, $scout_set_filter]);

        if ($result > 0) {
            $run = $db->get_one("SELECT * from runs where id = $1", [$id]);
            list($data_html, $stats) = render_run($run);

            $response = [
                'success' => true,
                'data' => $data_html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_run_post()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $id = $request[1];
        $name = $payload['name'];
        $section_id = $payload['section_id'];
        $tags = $payload['tags'];
        $test_filter = $payload['test_filter'];
        $comment = $payload['comment'];
        $mode = $payload['mode'];
        $scout_parser_id = $payload['scout_parser_id'];
        $scout_base_url = $payload['scout_base_url'];
        $scout_set_filter = $payload['scout_set_filter'];
        $executor_set_id = $payload['executor_set_id'];
        $manual_parser_id = $payload['manual_parser_id'];

        if ($mode != 1) $executor_set_id = 0;

        $result = $db->get_one("SELECT max(sort_order) from runs where section_id = $1;", [$section_id]);
        $max_sort_order = $result['max'] + 1;

        $new_id = $db->insert("INSERT into runs 
            (name, comment, section_id, tags, test_filter, mode, scout_parser_id, scout_base_url, scout_set_filter, executor_set_id, manual_parser_id, sort_order)
            values 
            ($1, $2, $section_id, $3, $4, $mode, $scout_parser_id, '$scout_base_url', $5, $executor_set_id, $manual_parser_id, $max_sort_order)",
            [$name, $comment, $tags, $test_filter, $scout_set_filter]);

        if ($new_id > 0) {
            $section = $db->get_one("SELECT * from sections where id = $1", [$section_id]);
            $data_html = render_section($section);

            $response = [
                'success' => true,
                'data' => $data_html
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_run_delete()
    {
        global $request;
        global $db;

        $payload = get_payload();
        $id = $request[1];

        $result = $db->query("DELETE from runs where id = $1", [$id]);

        if ($result > 0) {
            $response = [
                'success' => true,
                'data' => ''
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => ''
            ];
        }

        return $response;
    }

    function rest_result_get_html()
    {
        global $request;
        global $db_results;

        $result_id = $request[1];
        $result = $db_results->get_one('SELECT * from results where id = $1', [$result_id]);
        if ($result['is_stored_locally'] === 't')
        {
            $html_data = render_result_iframe($result);
            return $html_data;
        }
        else
        {
            header("Location: {$result['full_url']}", true, 302);
            exit;
        }

    }

    function rest_order_patch()
    {
        global $request;
        global $db;

        $payload = get_payload();

        $type = $request[1];
        $ids_list = $payload['ids_list'];
      
        $i = 1;
        $success = true;
        foreach ($ids_list as $id) {
            if ($id) {
                $result = $db->query("UPDATE $type set sort_order = $i where id = $id");
                if (!$result) $success = false;
                $i++;
            }
        }

        if ($success) {
            $response = [
                'success' => true,
                'data' => ''
            ];
        }
        else {
            $response = [
                'success' => false,
                'data' => 'Not all orders are updated'
            ];
        }

        return $response;
    }
?>