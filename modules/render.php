<?php
    function render_tags($tags_line)
    {
        $tags_html = '';
        if ($tags_line)
        {
            $tags = explode(',', $tags_line);
            foreach ($tags as $tag)
            {
                $tags_html .= '<span class="tag-badge">'.$tag.'</span>';
            }
        }

        return $tags_html;
    }


    function render_priority($priority_id)
    {
        $priority_html = '';
        if ($priority_id == 0) $priority_html .= '<span class="tag-badge smoke">Smoke</span>';
        elseif ($priority_id == 1) $priority_html .= '<span class="tag-badge p1">P1</span>';

        return $priority_html;
    }


    function render_inprogress_block($executor_id)
    {
        global $db;
        $executor = $db->get_one("SELECT e.*, m.name as machine_name, s.name as set_name, r.name as run_name
            from executors as e, machines as m, sets as s, runs as r where e.id = $executor_id and m.id = e.machine_id and s.id = e.set_id and r.id = e.run_id");

        $states_verbose = ['unknown', 'queued', 'starting', 'running', 'processing', 'done'];
        $states_color = [
            '',
            'background-color: #a9cafb',
            'background-color: #a9fbbb',
            'background-color: #198754; color: white;',
            'background-color: #a9fbbb',
            ''];
        $state_verbose = $states_verbose[$executor['state']];
        $state_color = $states_color[$executor['state']];

        $view_log = '';
        if ($executor['log_file_path'])
            $view_log = '| <a href="api/executor_log/'.$executor['id'].'" target="_blank">View Log</a>';

        $html = <<<HTML
            <tr id="run_progress_tr_{$executor['run_id']}" class="small text-muted">
                <td></td>
                <td colspan=7>
                    State: <span class="data-badge" style="{$state_color}">{$state_verbose}</span>
                    | Run: <span class="data-badge">{$executor['run_name']}</span>
                    Machine: <span class="data-badge">{$executor['machine_name']}</span>
                    Set: <span class="data-badge">{$executor['set_name']}</span> $view_log</td>
            </tr>
        HTML;


        return $html;
    }


    function render_run($run)
    {
        $tags_html = render_tags($run['tags']);

        $results = get_n_latest_results($run['id'], 2);
        if ($results)
        {
            $full_url = "/api/result/{$results[0]['id']}";
            $total = $results[0]['tests_total'];
            $passed = $results[0]['tests_passed'];
            $skipped = $results[0]['tests_skipped'];
            
            $passrate = calculate_passrate($total, $passed, $skipped);
            list($passrate_color, $passrate_backgroundColor) = get_passRate_color($passrate);
            $passrate_style = 'style="color: '.$passrate_color.'; background: '.$passrate_backgroundColor.'"';
            
            $duration = $results[0]['duration_sec'];
            $duration_text = verbose_duration($duration);

            $age_text = verbose_duration(time() - strtotime($results[0]['time']));
            
            $total_diff = '';
            $passrate_diff = '';
            $duration_diff = '';

            if (isset($results[1]))
            {
                $total_1 = $results[1]['tests_total'];
                $passed_1 = $results[1]['tests_passed'];
                $skipped_1 = $results[1]['tests_skipped'];
                if ($total - $total_1 != 0)
                    $total_diff = verbose_int($total - $total_1);
                
                $passrate_1 = calculate_passrate($total_1, $passed_1, $skipped_1);
                if ($passrate - $passrate_1 != 0)
                    $passrate_diff = verbose_int($passrate - $passrate_1).'%';

                $duration_1 = $results[1]['duration_sec'];
                if ($duration - $duration_1 != 0) {
                    $duration_diff = verbose_duration(abs($duration - $duration_1));
                    if ($duration > $duration_1) $duration_diff = '+'.$duration_diff;
                    if ($duration < $duration_1) $duration_diff = '-'.$duration_diff;
                }
            }
            
            $stats = ["total" => $total, "passed" => $passed, "skipped" => $skipped, "duration" => $duration];
        }
        else
        {
            $full_url = "";
            $total = '';
            $total_diff = '';
            $passed = 0;
            $passrate = '--';
            $passrate_diff = '';
            $passrate_style = '';
            
            $duration = 0;
            $duration_text = '';
            $duration_diff = '';
            
            $age_text = '';

            $stats = ["total" => 0, "passed" => 0, "skipped" => 0, "duration" => 0];
        }

        $inprogress_block = '';
        $action_button = '';

        if ($run['mode'] == 0)
            $action_button = '
                <button class="btn btn-outline-primary btn-sm rounded-circle p-1" style="width: 36px; height: 36px;">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>';
        elseif ($run['mode'] == 1) {

            // when the run is in progress
            if ($run['executor_id']) {
                $action_button = '
                    <button class="btn btn-outline-danger btn-sm rounded-circle p-1" style="width: 36px; height: 36px;"
                        data-bs-toggle="modal" 
                        data-bs-target="#runStopModal"
                        data-run-id="'.$run['id'].'"
                        data-run-name="'.$run['name'].'">
                        <i class="bi bi-stop"></i>
                    </button>';

                $inprogress_block = render_inprogress_block($run['executor_id']);
            }
            else
                $action_button = '
                    <button class="btn btn-outline-success btn-sm rounded-circle p-1" style="width: 36px; height: 36px;"
                        data-bs-toggle="modal" 
                        data-bs-target="#runStartModal"
                        data-run-id="'.$run['id'].'"
                        data-run-name="'.$run['name'].'">
                        <i class="bi bi-play"></i>
                    </button>';
        }
        elseif ($run['mode'] == 2)
            $action_button = '
                <button class="btn btn-outline-secondary btn-sm rounded-circle p-1" style="width: 36px; height: 36px;">
                    <i class="bi bi-upload"></i>
                </button>';
        else
            $action_button = '
                <button class="btn btn-light btn-sm rounded-circle p-1" style="width: 36px; height: 36px;">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>';

        $html = <<<HTML
                    <tr id="run_tr_{$run['id']}" class="run_tr" data-run-id="{$run['id']}">
                        <td class="run-grip">
                            <i class="bi bi-grip-vertical"></i>
                        </td>
                        <td>
                            <div class="fw-semibold">{$run['name']}</div>
                            <div class="small text-muted">$tags_html</div>
                        </td>
                        <td class="text-center">
                            <a href="{$full_url}" target="_blank">
                                <span class="passrate-badge" $passrate_style>$passrate%</span>
                            </a>
                            <br><span class="small text-muted">$passrate_diff</span>
                        </td>
                        <td class="text-center">
                            <span class="data-badge">$total</span>
                            <br><span class="small text-muted">$total_diff</span>
                        </td>
                        <td class="text-center">
                            <span class="data-badge">$duration_text</span>
                            <br><span class="small text-muted">$duration_diff</span>
                        </td>
                        <td class="text-center"><span class="text-muted small">
                            <span class="data-badge">$age_text</span>
                        </td>
                        <td>
                            <span class="small text-muted">{$run['comment']}</span>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-light btn-sm rounded-circle p-1" style="width: 36px; height: 36px;"
                                data-bs-toggle="modal" 
                                data-bs-target="#runDetailsModal"
                                data-run-id="{$run['id']}"
                                data-run-name="{$run['name']}">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-light btn-sm rounded-circle p-1" style="width: 36px; height: 36px;"
                                data-bs-toggle="modal" 
                                data-bs-target="#runChartModal"
                                data-run-id="{$run['id']}"
                                data-run-name="{$run['name']}">
                                <i class="bi bi-graph-up"></i>
                            </button>
                            <br>

                            <button class="btn btn-light btn-sm rounded-circle p-1" style="width: 36px; height: 36px;"
                                data-bs-toggle="modal" 
                                data-bs-target="#runEditModal"
                                data-run-id="{$run['id']}"
                                data-run-section-id="{$run['section_id']}"
                                data-run-name="{$run['name']}"
                                data-run-tags="{$run['tags']}"
                                data-run-comment="{$run['comment']}"
                                data-run-test-filter="{$run['test_filter']}"
                                data-run-mode="{$run['mode']}"
                                data-scout-parser-id="{$run['scout_parser_id']}"
                                data-scout-base-url="{$run['scout_base_url']}"
                                data-scout-set-filter="{$run['scout_set_filter']}"
                                data-executor-set-id="{$run['executor_set_id']}"
                                data-manual-parser-id="{$run['manual_parser_id']}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            {$action_button}
                        </td>
                    </tr>
                    {$inprogress_block}
        HTML;

        return array($html, $stats);
    }

    
    function render_section($section)
    {
        global $db;
        $section_style = 'style="background: linear-gradient(135deg, #ffffff 0%, '.$section['color'].' 100%);"';

        $html = <<<HTML
            <div id="section_div_{$section['id']}" class="section_div table-wrapper modern-card mb-4" data-section-id="{$section['id']}">
                <div class="card-header-gradient d-flex justify-content-between align-items-center" $section_style>
                    <div class="d-flex align-items-center gap-3">
                        <i class="section-grip bi bi-grip-vertical"></i>
                        <div>
                            <span class="fw-semibold me-2">{$section['name']}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light rounded-pill px-4 py-2 border-0 shadow-sm text-primary" style="background: white;"
                            data-bs-toggle="modal" 
                            data-bs-target="#runEditModal"
                            data-run-id="-1"
                            data-run-section-id="{$section['id']}"
                            data-run-name=""
                            data-run-tags=""
                            data-run-comment=""
                            data-run-test-filter=""
                            data-run-mode=""
                            data-scout-parser-id="1"
                            data-scout-base-url=""
                            data-scout-set-filter=""
                            data-executor-set-id=""
                            data-manual-parser-id="2">
                            <i class="bi bi-plus-square-dotted"></i>
                        </button>
                        <button class="btn btn-light rounded-pill px-4 py-2 border-0 shadow-sm text-primary" style="background: white;"
                            data-bs-toggle="modal" 
                            data-bs-target="#sectionEditModal"
                            data-section-id="{$section['id']}"
                            data-section-name="{$section['name']}"
                            data-section-color="{$section['color']}"
                            data-section-is_archived="{$section['is_archived']}"
                            data-section-comment="{$section['comment']}"
                            data-section-board-id="{$section['board_id']}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </div>
                <div class="p-3">
                    <span>{$section['comment']}</span>
                </div>

                <div class="p-3">
                    <table class="table-modern w-100">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th class="text-center">Pass rate</th>
                                <th class="text-center">Count</th>
                                <th class="text-center">Duration</th>
                                <th class="text-center">Age</th>
                                <th>Comments</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="sortable-rows">
        HTML;

        $sum_stats = ["total" => 0, "passed" => 0, "skipped" => 0, "duration" => 0];
        
        $runs = $db->get_all("SELECT * from runs where section_id = $1 order by sort_order asc", [$section['id']]);
        foreach ($runs as $run)
        {
            list($current_html, $stats) = render_run($run);
            $html .= $current_html;

            foreach ($stats as $key => $val)
                $sum_stats[$key] += $val;
        }

        $passrate = calculate_passrate($sum_stats['total'], $sum_stats['passed'], $sum_stats['skipped']);
        
        $html .= <<<HTML
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top bg-light bg-opacity-30 rounded-bottom d-flex justify-content-between align-items-center small text-muted">
                    <span><i class="bi bi-graph-up me-1"></i>Summary: {$passrate}% passed of {$sum_stats['total']} tests</span>
                    <!-- <span><i class="bi bi-clock me-1"></i>Обновлено 2 мин назад</span> -->
                </div>
            <!-- </div> -->
            </div>
        HTML;

        return $html;
    }


    function render_board($board_id)
    {
        global $db;
        $sections = $db->get_all("SELECT * from sections where board_id = $1 order by sort_order asc", [$board_id]);

        $html = '<div id="tables-container">';
        foreach ($sections as $section)
        {
            $html .= render_section($section);
        }
        $html .= '</div>';

        return $html;
    }


    function render_run_details($run_id)
    {
        global $db_results;

        $max_details_count = 25;
        $html_data = '';

        $results = get_n_latest_results($run_id, $max_details_count);

        if ($results)
        {
            $results_list = array();
            $details_data = array();
            $stats = array();

            foreach ($results as $result)
            {
                if (!$stats)
                    $stats = ["total" => $result['tests_total'], "passed" => $result['tests_passed'], "failed" => $result['tests_failed'], "skipped" => $result['tests_skipped']];

                $results_list[] = [
                    "id" => $result['id'], 
                    "start_time" => $result['start_time'],
                    "duration_sec" => $result['duration_sec']
                ];

                $tests = $db_results->get_all("SELECT * from tests where result_id = $1 order by id asc", [$result['id']]);

                foreach ($tests as $test)
                {
                    if (!isset($details_data[$test['name']]))
                    {
                        $details_data[$test['name']] = array();
                        $details_data[$test['name']]['results'] = array();
                        $details_data[$test['name']]['tags'] = render_tags($test['tags']);
                        $details_data[$test['name']]['priority'] = render_priority($test['priority']);
                        $details_data[$test['name']]['full_url'] = $test['full_url'];
                    }

                    $details_data[$test['name']]['results'][$result['id']] = $test['state'];
                }
            }

            // print_r($details_data);
            // exit;

            $html_data = <<<HTML
                <div class="rundetails-scroll-container">
                    <table class="rundetails-matrix">
                        
                        <thead>
                            <tr class="rundetails-row rundetails-header-row">
                                <th class="rundetails-cell rundetails-test-name-header">Test / Run</th>
                HTML;

                foreach ($results_list as $result)
                {
                    $date = new DateTime($result['start_time']);
                    $date_d = $date->format('d M');
                    $date_t = $date->format('H:i');
                    $duration = verbose_duration($result['duration_sec']);

                    $html_data .= '
                        <th class="rundetails-cell rundetails-run-link">
                            <a href="/api/result/'.$result['id'].'" target="_blank" class="rundetails-run-link-btn">
                                <span class="rundetails-run-id">#'.$result['id'].'</span>
                                <span class="rundetails-run-date">
                                    <span class="nowrap">'.$date_d.'</span><br><span class="nowrap">'.$date_t.'</span>
                                </span>
                                <span class="rundetails-run-duration">
                                    <span class="nowrap">['.$duration.']</span>
                                </span>
                            </a>
                        </th>';
                }
                
                $html_data .= '
                </thead>
                <tbody>';
                
                foreach ($details_data as $name => $data)
                {
                    $html_data .= '
                    <tr class="rundetails-row">
                        <td class="rundetails-cell rundetails-test-name">
                            <div class="rundetails-test-info">
                                <span class="rundetails-test-title">'.$name.'</span><br>
                                '.$data['priority'].'
                                '.$data['tags'].'
                            </div>
                        </td>';

                    foreach ($results_list as $result)
                    {
                        $state = -1;
                        if (isset($data['results'][$result['id']]))
                            $state = $data['results'][$result['id']];

                        $full_url = $data['full_url'];

                        if ($state == -1)
                        {
                            $html_data .= '
                            <td class="rundetails-cell rundetails-result">
                                <div class="rundetails-result-content">
                                </div>
                            </td>';
                        }

                        if ($state == 0)
                        {
                            $html_data .= '
                                <td class="rundetails-cell rundetails-result rundetails-fail">
                                    <a href="'.$full_url.'" target="_blank">
                                        <div class="rundetails-result-content">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </div>
                                    </a>
                                </td>';
                        }

                        if ($state == 1)
                        {
                            $html_data .= '
                                <td class="rundetails-cell rundetails-result rundetails-pass">
                                    <a href="'.$full_url.'" target="_blank">
                                        <div class="rundetails-result-content">
                                            <i class="bi bi-check-circle-fill"></i>
                                        </div>
                                    </a>
                                </td>';
                        }

                        if ($state == 2)
                        {
                            $html_data .= '
                                <td class="rundetails-cell rundetails-result rundetails-skip">
                                    <a href="'.$full_url.'" target="_blank">
                                        <div class="rundetails-result-content">
                                            <i class="bi bi-skip-forward-fill"></i>
                                        </div>
                                    </a>
                                </td>';
                        }
                    }
                    
                    $html_data .= '</tr>';
                }

            $html_data .= '</tbody></table>';

            if ($stats)
            {
                $html_data .= <<<HTML
                    <div class="rundetails-footer">
                        <div class="rundetails-summary">
                            <span class="rundetails-stat">
                                Latest result:
                            </span>
                            <span class="rundetails-stat">
                                <i class="bi bi-filter-circle text-success"></i>
                                total: {$stats['total']}
                            </span>
                            <span class="rundetails-stat">
                                <i class="bi bi-check-circle text-success"></i>
                                passed: {$stats['passed']}
                            </span>
                            <span class="rundetails-stat">
                                <i class="bi bi-x-circle text-danger"></i>
                                failed: {$stats['failed']}
                            </span>
                            <span class="rundetails-stat">
                                <i class="bi bi-skip-forward text-secondary"></i>
                                skipped: {$stats['skipped']}
                            </span>
                        </div>
                    </div>
                HTML;
            }

            // $html_data .= '</div>';
        }

        return $html_data;
    }


    function render_machines()
    {
        global $db;
        
        $boards = $db->get_all("SELECT DISTINCT b.id, b.name as board_name, p.name as project_name 
            from boards as b, projects as p, machines as m
            where b.project_id = p.id and b.id = m.board_id
            order by p.name asc, b.name asc");

        $html = '';
        foreach ($boards as $board)
        {
            $machines = $db->get_all("SELECT * from machines where board_id = {$board['id']} order by name asc");

            $section = array();
            $section['id'] = $board['id'];
            $section['name'] = $board['project_name'].' / '.$board['board_name'];
            $section['comment'] = '';
            $section['color'] = '#cccccc';

            $section_style = 'style="background: linear-gradient(135deg, #ffffff 0%, '.$section['color'].' 100%);"';

            $html .= <<<HTML
                <div id="machines_div_{$section['id']}" class="section_div table-wrapper modern-card mb-4" data-section-id="{$section['id']}">
                    <div class="card-header-gradient d-flex justify-content-between align-items-center" $section_style>
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <span class="fw-semibold me-2">{$section['name']}</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light rounded-pill px-4 py-2 border-0 shadow-sm text-primary" style="background: white;"
                                data-bs-toggle="modal" 
                                data-bs-target="#machineEditModal"
                                data-machine-id="-1"
                                data-machine-name=""
                                data-machine-board-id="{$board['id']}"
                                data-machine-hc=""
                                data-machine-json=""
                                data-machine-comment="">
                                <i class="bi bi-plus-square-dotted"></i>
                            </button>
                        </div>
                    </div>
                    <!-- <div class="p-3">
                        <span>{$section['comment']}</span>
                    </div> -->

                    <div class="p-3">
                        <table class="table-modern w-100">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th class="text-center">Heathcheck IP</th>
                                    <th class="text-center">State</th>
                                    <th class="text-center">Run Status</th>
                                    <th>Comments</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
            HTML;


            foreach ($machines as $machine)
            {
                $html .= render_machine($machine);
            }

            $html .= <<<HTML
                            </tbody>
                        </table>
                    </div>
                    <!-- <div class="p-3 border-top bg-light bg-opacity-30 rounded-bottom d-flex justify-content-between align-items-center small text-muted"> -->
                </div>
            HTML;
        }

        return $html;
    }


    function render_machine($machine)
    {
        $machine_status_verbose = 'unknown';
        if ($machine['status'] == 1) $machine_status_verbose = 'up';
        elseif ($machine['status'] == 2) $machine_status_verbose = 'down';

        $inprogress_block = '';
        if ($machine['executor_id']) {
            $inprogress_block = render_inprogress_block($machine['executor_id']);
        }

        $html = <<<HTML
            <tr id="machine_tr_{$machine['id']}" class="run_tr" data-run-id="{$machine['id']}">
                <td>
                    <div class="fw-semibold">{$machine['name']}</div>
                </td>
                <td class="text-center">
                    <div class="small text-muted">{$machine['hc_ip_port']}</div>
                </td>
                <td class="text-center">
                    <div class="small text-muted">{$machine_status_verbose}</div>
                </td>
                <td class="text-center">
                    <div class="small text-muted">{$machine['executor_id']}</div>
                </td>
                <td>
                    <span class="small text-muted">{$machine['comment']}</span>
                </td>
                <td class="text-end">
                    <button type="button" class="btn btn-light btn-sm rounded-circle p-1" style="width: 36px; height: 36px;"
                        data-bs-toggle="modal" 
                        data-bs-target="#machineEditModal"
                        data-machine-id="{$machine['id']}"
                        data-machine-name="{$machine['name']}"
                        data-machine-board-id="{$machine['board_id']}"
                        data-machine-hc="{$machine['hc_ip_port']}"
                        data-machine-json="{$machine['json']}"
                        data-machine-comment="{$machine['comment']}">
                        <i class="bi bi-pencil"></i>
                    </button>
                </td>
            </tr>
            {$inprogress_block}
        HTML;

        return $html;
    }


    function render_sets()
    {
        global $db;
        $sets = $db->get_all("SELECT s.*, p.name as parser_name from sets as s, parsers as p where s.parser_id = p.id order by s.name asc");

        $section = array();
        $section['id'] = 1;
        $section['name'] = 'Test sets';
        $section['comment'] = '';
        $section['color'] = '#cccccc';

        $section_style = 'style="background: linear-gradient(135deg, #ffffff 0%, '.$section['color'].' 100%);"';

        $html = <<<HTML
            <div id="sets_div" class="section_div table-wrapper modern-card mb-4" data-section-id="{$section['id']}">
                <div class="card-header-gradient d-flex justify-content-between align-items-center" $section_style>
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <span class="fw-semibold me-2">{$section['name']}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light rounded-pill px-4 py-2 border-0 shadow-sm text-primary" style="background: white;"
                            data-bs-toggle="modal"
                            data-bs-target="#setEditModal"
                            data-set-id="-1"
                            data-set-name=""
                            data-set-json=""
                            data-set-git=""
                            data-set-local-artifacts=""
                            data-set-commandline=""
                            data-set-comment="">
                            <i class="bi bi-plus-square-dotted"></i>
                        </button>
                    </div>
                </div>
                <!-- <div class="p-3">
                    <span>{$section['comment']}</span>
                </div> -->

                <div class="p-3">
                    <table class="table-modern w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-center">Git URL</th>
                                <th class="text-center">Local artifacts</th>
                                <th class="text-center">Parser</th>
                                <th>Comments</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
        HTML;


        foreach ($sets as $set)
        {
            $html .= render_set($set);
        }

        $html .= <<<HTML
                        </tbody>
                    </table>
                </div>
                <!-- <div class="p-3 border-top bg-light bg-opacity-30 rounded-bottom d-flex justify-content-between align-items-center small text-muted"> -->
                </div>
            </div>
        HTML;

        return $html;
    }


    function render_set($set)
    {
        $html = <<<HTML
            <tr id="set_tr_{$set['id']}" class="run_tr" data-run-id="{$set['id']}">
                <td>
                    <div class="fw-semibold">{$set['name']}</div>
                </td>
                <td class="text-center">
                    <div class="small text-muted">
                        <a href="{$set['git_url']}" target="_blank">{$set['git_url']}</a>
                    </div>
                </td>
                <td class="text-center">
                    <div class="small text-muted">
                        {$set['local_artifacts']}
                    </div>
                </td>
                <td class="text-center">
                    <div class="small text-muted">{$set['parser_name']}</div>
                </td>
                <td>
                    <span class="small text-muted">{$set['comment']}</span>
                </td>
                <td class="text-end">
                    <button type="button" class="btn btn-light btn-sm rounded-circle p-1" style="width: 36px; height: 36px;"
                        data-bs-toggle="modal" 
                        data-bs-target="#setEditModal"
                        data-set-id="{$set['id']}"
                        data-set-name="{$set['name']}"
                        data-set-parser-id="{$set['parser_id']}"
                        data-set-json="{$set['json']}"
                        data-set-git="{$set['git_url']}"
                        data-set-local-artifacts="{$set['local_artifacts']}"
                        data-set-commandline="{$set['commandline']}"
                        data-set-comment="{$set['comment']}">
                        <i class="bi bi-pencil"></i>
                    </button>
                </td>
            </tr>
        HTML;

        return $html;
    }


    function get_chart_data($run_id)
    {
        $chart_data = ['runs' => array(), 'passRates' => array(), 'testCounts' => array()];

        $results = get_n_latest_results($run_id, 10000);
        $results = array_reverse($results);

        if ($results)
        {
            foreach ($results as $result)
            {
                $passrate = calculate_passrate($result['tests_total'], $result['tests_passed'], $result['tests_skipped']);

                $chart_data['ids'][] = $result['id'];
                $chart_data['start_times'][] = date('d M H:i', strtotime($result['start_time']));
                $chart_data['passRates'][] = $passrate;
                $chart_data['testCounts'][] = $result['tests_total'];
            }
        }

        return $chart_data;
    }


    function render_result_iframe($result)
    {
        $html = <<<HTML
            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    
                    <link rel="icon" type="image/png" href="/icon.png">
                    <link href="/static/styles.css" rel="stylesheet">
                    <link href="/static/bootstrap/css/bootstrap.min.css" rel="stylesheet">
                    <link href="/static/bootstrap/css/bootstrap-icons.min.css" rel="stylesheet">
                    <script src="/static/bootstrap/js/popper.min.js"></script>
                    <script src="/static/bootstrap/js/bootstrap.bundle.min.js"></script>
                    <script src="/static/jquery.min.js"></script>
                    <script src="/static/jquery-ui.min.js"></script>
                    <script src="/static/chart.js"></script>
                    <script src="/static/chartjs-plugin-zoom.min.js"></script>
                    <script src="/static/hammer.js"></script>
                    <script src="/static/toastr.js"></script>
                    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
                    <style>
                        html, body {
                            margin: 0;
                            padding: 0;
                            height: 100%;
                            overflow: hidden;
                        }
                        iframe {
                            display: block;
                            width: 100%;
                            height: calc(100% - 25px);
                            margin-top: 25px;
                            border: 0;
                        }

                        .results_header {
                            position: fixed;
                            top: 0px;
                            width: 100%;
                            height: 25px;
                            background-color: #343434;
                            color: white;
                            padding-left: 5px;
                        }

                        a {
                            color: white;
                        }
                    </style>
                </head>
                <body>
                    <div class='results_header'><b>ResultsHub result data</b> | 
                        <a href="/api/result_log/{$result['id']}" target="_blank">View Log</a></div>
                    <iframe src="/results/{$result['id']}"></iframe>
                </body>
            </html>
        HTML;

        return $html;
    }


    function render_run_start_form($run_data, $set_data, $machine_data)
    {
        $html = <<<HTML
            <h5>Set parameters:</h5>

            <div class="form-group mb-4">
                <label for="record-name" class="form-label"><i class="bi bi-tag me-2"></i>
                    Set name
                </label>
                <input type="text" class="form-control form-control rundetails-input" id="run-start-set-name" 
                    disabled value="{$set_data['name']}">
            </div>

            <div class="form-group mb-4">
                <label for="record-name" class="form-label"><i class="bi bi-tag me-2"></i>
                    Parser for results
                </label>
                <input type="text" class="form-control form-control rundetails-input" id="run-start-set-parser-name" 
                    disabled value="{$set_data['parser_name']}">
            </div>

            <div class="form-group mb-4">
                <label for="record-name" class="form-label"><i class="bi bi-tag me-2"></i>
                    Git URL
                </label>
                <input type="text" class="form-control form-control rundetails-input" id="run-start-set-git" 
                    disabled value="{$set_data['git_url']}">
            </div>

            <div class="form-group mb-4">
                <label for="record-name" class="form-label"><i class="bi bi-tag me-2"></i>
                    Local Artifacts
                </label>
                <input type="text" class="form-control form-control rundetails-input" id="run-start-set-local-artifacts" 
                    disabled value="{$set_data['local_artifacts']}">
            </div>

            <div class="form-group mb-4">
                <label for="record-name" class="form-label"><i class="bi bi-tag me-2"></i>
                    Commandline
                </label>
                <textarea class="form-control rundetails-textarea" id="run-start-set-commandline" rows="2"
                    disabled>{$set_data['commandline']}</textarea>
            </div>

            <div class="form-group mb-4">
                <label for="record-comment" class="form-label"><i class="bi bi-chat me-2"></i>
                    set.json
                </label>
                <textarea class="form-control rundetails-textarea" id="run-start-set-json" rows="7"
                    disabled>{$set_data['json']}</textarea>
            </div>

            <hr>

            <h5>Machine parameters:</h5>

            <div class="form-group mb-4">
                <label for="record-name" class="form-label"><i class="bi bi-tag me-2"></i>
                    Machine name
                </label>
                <input type="text" class="form-control form-control rundetails-input" id="run-start-machine-name" 
                    disabled value="{$machine_data['name']}">
            </div>

            <div class="form-group mb-4">
                <label for="record-comment" class="form-label"><i class="bi bi-chat me-2"></i>
                    machine.json
                </label>
                <textarea class="form-control rundetails-textarea" id="run-start-machine-json" rows="7"
                    disabled>{$machine_data['json']}</textarea>
            </div>

            <input type="text" id="run-start-run-id" style="display: none" value="{$run_data['id']}">
            <input type="text" id="run-start-set-id" style="display: none" value="{$set_data['id']}">
            <input type="text" id="run-start-parser-id" style="display: none" value="{$set_data['parser_id']}">
            <input type="text" id="run-start-machine-id" style="display: none" value="{$machine_data['id']}">
        HTML;

        return $html;
    }

?>