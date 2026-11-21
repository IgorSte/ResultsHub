<?php
    require_once('modules/config.php');
    require_once('modules/general.php');
    require_once('modules/render.php');

    if (isset($_GET['id'])) $result_id = $_GET['id'];
    else $result_id = 0;
    $result = $db->get_one('SELECT * from results where id = $1', [$result_id]);
?>
<!doctype html>
<html lang="en">
	<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <link rel="icon" type="image/png" href="icon.png">
		<link href="static/styles.css" rel="stylesheet">
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
        <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    </head>
    <body>
        <!-- <iframe src="<?php echo $result['full_url']; ?>"></iframe> -->
        
    </body>
</html>
