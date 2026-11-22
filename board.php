<?php
    if (isset($_GET['board_id'])) $board_id = $_GET['board_id'];
    else $board_id = 1;
    $board = $db->get_one('SELECT b.*, p.name as project_name from boards as b left join projects as p on b.project_id = p.id where b.id = $1', [$board_id]);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="project-avatar"><i class="bi bi-<?= $board['icon'] ?>"></i></div>
        <div>
            <h1 class="h3 fw-bold mb-1"><?= $board['project_name'].' / '.$board['name'] ?></h1>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-light rounded-pill px-4 py-2 border-0 shadow-sm text-primary" style="background: white;"
            data-bs-toggle="modal" 
            data-bs-target="#sectionEditModal"
            data-section-id="-1"
            data-section-name=""
            data-section-color=""
            data-section-is_archived="0"
            data-section-comment=""
            data-section-board-id="<?= $board['id'] ?>">
            <i class="bi bi-plus-square-dotted me-2"></i>Section
        </button>
        <button class="btn btn-light rounded-pill px-4 py-2 border-0 shadow-sm text-primary" style="background: white;"
            data-bs-toggle="modal" 
            data-bs-target="#boardEditModal"
            data-board-id="<?= $board['id'] ?>"
            data-board-name="<?= $board['name'] ?>"
            data-board-icon="<?= $board['icon'] ?>"
            data-board-is_archived="<?= $board['is_archived'] ?>"
            data-board-comment="<?= escape_string($board['comment']) ?>"
            data-board-project_id="<?= $board['project_id'] ?>">
            <i class="bi bi-pencil me-2"></i>Board
        </button>
    </div>
</div>

<div class="modern-card mb-4">
    <div class="card-header-gradient d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-info-circle fs-5 text-primary"></i>
            <span class="fw-semibold">About the dashboard</span>
        </div>
    </div>
    <div class="p-4">
        <div class="row">
            <div class="p-3">
                <?= parse_comment($board['comment']) ?>
            </div>
        </div>
    </div>
</div>

<div id="board_container">
<?= render_board($board_id) ?>
</div>
