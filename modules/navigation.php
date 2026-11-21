<?php
    $nav_data = Array();
    $boards_counter = 0;

    $projects = $db->get_all("SELECT * from projects order by name asc");

    foreach ($projects as $project) {
        $project_data = Array();
        $project_data['project'] = $project;

        $boards = $db->get_all("SELECT * from boards where project_id = {$project['id']} order by name asc");
        if (count($boards)) {
            $project_data['boards'] = $boards;
            $boards_counter += count($boards);
        }
        else $project_data['boards'] = Array();

        $nav_data[] = $project_data;
    }

    $machines = $db->get_one("SELECT count(*) from machines");
    $sets = $db->get_one("SELECT count(*) from sets");

    $current_board_id = null;
    $current_project_id = null;
    $boards_button_class = '';
    $machines_button_class = '';
    $sets_button_class = '';
    if (isset($_GET['board_id'])) {
        $current_board_id = $_GET['board_id'];
        $board = $db->get_one('SELECT project_id from boards where id = $1', [$current_board_id]);
        $current_project_id = $board['project_id'];

        $boards_button_class = 'menu-section-active';
    }
    if (isset($_GET['machines'])) $machines_button_class = 'menu-section-active';
    if (isset($_GET['sets'])) $sets_button_class = 'menu-section-active';
?>

<nav class="navbar-modern d-flex align-items-center">
    <div class="logo-wrapper">
        <div class="logo-icon">
            <img src='icon.png' width=32 height=32>
        </div>
        <span class="logo-text">ResultsHub</span>
    </div>
    
    <!-- BOARDS -->
    <div class="dropdown dropdown-modern mx-4">
        <button class="btn dropdown-toggle <?= $boards_button_class ?>" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            Boards
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2"><?= $boards_counter ?></span>
        </button>
        
        <ul class="dropdown-menu">

            <?php
                foreach ($nav_data as $data)
                {
                    $project_class = '';
                    if ($data['project']['id'] == $current_project_id) $project_class = 'fw-bold text-primary';

                    echo '
                        <li class="dropdown-submenu">
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-rocket"></i>
                                
                                <button class="dropdown-item bi btn btn-light py-2 border-0 '.$project_class.'" style="background: white;"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#projectEditModal"
                                    data-project-id="'.$data['project']['id'].'"
                                    data-project-name="'.$data['project']['name'].'">
                                    '.$data['project']['name'].'
                                </button>
                                <span class="badge">'.count($data['boards']).'</span>
                            </a>
                            <ul class="dropdown-menu">';

                            foreach ($data['boards'] as $board_data) {
                                $board_class = '';
                                if ($board_data['id'] == $current_board_id) $board_class = 'fw-bold text-primary';
                                echo '<li><a class="dropdown-item '.$board_class.'" href="?board_id='.$board_data['id'].'"><i class="bi bi-'.$board_data['icon'].'"></i> '.$board_data['name'].'</a></li>';
                            }

                            ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item bi btn btn-light py-2 border-0" style="background: white;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#boardEditModal"
                                        data-board-id="-1"
                                        data-board-name=""
                                        data-board-icon="grid-1x2"
                                        data-board-is_archived="0"
                                        data-board-comment=""
                                        data-board-project_id="<?= $data['project']['id']; ?>">
                                        <i class="bi-plus-square-dotted"></i>Create Board
                                    </button>
                                </li>
                            <?php
                            
                            echo '</ul>
                        </li>';
                }
            ?>
            
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-grid-3x3-gap"></i>Archieved<span class="badge" style='margin-right: 25px'>0</span></a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <button class="dropdown-item bi btn btn-light py-2 border-0" style="background: white;"
                    data-bs-toggle="modal" 
                    data-bs-target="#projectEditModal"
                    data-project-id="-1"
                    data-project-name="">
                    <i class="bi-plus-square-dotted"></i>Create Project
                </button>
            </li>
        </ul>
    </div>


    <!-- MACHINES -->
    <div class="vr"></div>
    <div class="dropdown dropdown-modern mx-4">
        <a href="?machines" class="btn rounded-pill btn-light <?= $machines_button_class ?>">
            <i class="bi bi-pc-display"></i>
            Machines
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2"><?= $machines['count'] ?></span>
        </a>
    </div>


    <!-- SETS -->
    <div class="dropdown dropdown-modern mx-0">
        <a href="?sets" class="btn rounded-pill btn-light <?= $sets_button_class ?>">
            <i class="bi bi-card-checklist"></i>
            Sets
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2"><?= $sets['count'] ?></span>
        </a>
    </div>
    
    <!-- AUTH -->
    <a href="#" class="edit-button">
        <i class="bi bi-person"></i>
        Login
    </a>
</nav>