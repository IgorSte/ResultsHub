<?php
    require_once('modules/config.php');
    require_once('modules/general.php');
    require_once('modules/render.php');
?>
<!doctype html>
<html lang="en">
	<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link rel="icon" type="image/png" href="icon.png">
		<link href="modules/styles.css" rel="stylesheet">
		<link href="static/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="static/bootstrap/css/bootstrap-icons.min.css" rel="stylesheet">
		<script src="static/bootstrap/js/popper.min.js"></script>
		<script src="static/bootstrap/js/bootstrap.bundle.min.js"></script>
		<script src="static/jquery.min.js"></script>
		<script src="static/jquery-ui.min.js"></script>
		<script src="static/chart.js"></script>
		<script src="static/chartjs-plugin-zoom.min.js"></script>
		<script src="static/hammer.js"></script>
		<script src="static/toastr.js"></script>
        <script src="static/sortable.min.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    </head>
    <body>
        <div class="container-fluid">
            <?php require_once('modules/navigation.php'); ?>
    
            <main class="px-4 mx-4">                    
                <div class="row">
                    <div class="container-fluid px-4">

                        <?php
                            if (isset($_GET['board_id'])) require_once('board.php');
                            elseif (isset($_GET['machines'])) require_once('machines.php');
                            elseif (isset($_GET['sets'])) require_once('sets.php');
                        ?>
                            
                        <div class="text-center text-muted small mt-5 mb-3">
                            <a target="_blank" href="https://github.com/IgorSte/ResultsHub">ResultsHub</a> · See your automation health at one page.
                        </div>

                    </div>
                </div>
            </main>
        </div>

        <?php require_once('modules/modals.php'); ?>
        <script src="modules/modals.js"></script>
        <script src="modules/scripts.js"></script>

        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
            <div id="toastContainer"></div>
        </div>
    </body>
</html>
