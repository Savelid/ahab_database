<?php
session_start();

include_once 'res/config.inc.php';
include_once 'res/functions.inc.php';

if (!empty($_GET['dataset_id'])) {

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$sql = " SELECT *
	FROM datasets
	WHERE dataset_id = '$_GET[dataset_id]';";
	$result = $conn->query($sql);
	if (!$result) {
		die("Query 1 failed! <br>Error:" . $sql . "<br>" . $conn->error);
	}
	$query = $result->fetch_array(MYSQLI_ASSOC);

	$conn->close();
}
else {
	$_SESSION['showalert'] = 'true';
	$_SESSION['alert'] = 'No dataset with this id';
	header("Location: main_datasets.php");
	die();
}

$titel = 'Dataset ' . $query['dataset_id'];
include 'res/header.inc.php';
?>
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>

<section class="top_content hidden-print">
	<a href="edit_datasets.php?dataset_id=<?php echo $_GET['dataset_id']; ?>" class="btn btn-default" role="button">Edit Dataset</a>
	<a href="edit_calibration.php?dataset_id=<?php echo $_GET['dataset_id']; ?>" class="btn btn-default" role="button">Make/Edit Calibration</a>
</section>

<section class="content">
	<div class="row">
		<div class="col-sm-7 col-xs-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Info</h3>
				</div>
				<div class="panel-body">

					<div class="row">
						<div class="col-xs-6"><strong>Date</strong></div>
						<div class="col-xs-6"><?php echo $query['datetime'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Dataset id</strong></div>
						<div class="col-xs-6"><?php echo $query['dataset_id'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Disc id</strong></div>
						<div class="col-xs-6"><?php echo $query['disc_id'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Location</strong></div>
						<div class="col-xs-6"><?php echo $query['location'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>System id</strong></div>
						<div class="col-xs-6"><?php echo $query['system_id'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>System model</strong></div>
						<div class="col-xs-6"><?php echo $query['system_model'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Topo sensor 1</strong></div>
						<div class="col-xs-6"><?php echo $query['topo_sensor_1_sn'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Topo sensor 2</strong></div>
						<div class="col-xs-6"><?php echo $query['topo_sensor_2_sn'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Shallow sensor</strong></div>
						<div class="col-xs-6"><?php echo $query['shallow_sensor_sn'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Deep sensor</strong></div>
						<div class="col-xs-6"><?php echo $query['deep_sensor_sn'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>SCU</strong></div>
						<div class="col-xs-6"><?php echo $query['scu_sn'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>IMU 1</strong></div>
						<div class="col-xs-6"><?php echo $query['imu_1_sn'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>IMU 2</strong></div>
						<div class="col-xs-6"><?php echo $query['imu_2_sn'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>PAV</strong></div>
						<div class="col-xs-6"><?php echo $query['leica_pav_sn'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Camera</strong></div>
						<div class="col-xs-6"><?php echo $query['leica_cam_sn'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Type of data</strong></div>
						<div class="col-xs-6"><?php echo $query['type_of_data'];?></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Flight logs</strong></div>
						<div class="col-xs-6"><a href="<?php echo $query['flight_logs'];?>"><?php echo $query['flight_logs'];?></a></div>
					</div>

					<div class="row">
						<div class="col-xs-6"><strong>Calibrations</strong></div>
						<div class="col-xs-6">
							<?php
							$sql = "SELECT calibration_id FROM calibration WHERE dataset_id = '$query[dataset_id]';";
							$calibration_id_list = listAll($sql);
							if ($calibration_id_list != NULL) {
								foreach ($calibration_id_list as $key => $value) {
									echo '<a href="main_calibration.php?search=' .$value. '">' .$value. "</a><br>";
								}
							}
							?>
						</div>
					</div>

				</div>
			</div><!-- end panel -->

			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Purpose of flight</h3>
				</div>
				<div class="panel-body">
					<?php echo nl2br($query['purpose_of_flight']); ?>
				</div>
			</div><!-- end panel -->

			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Flight comments</h3>
				</div>
				<div class="panel-body">
					<?php
						$flight_comments = explode("&|", $query['flight_comments']);
						foreach ($flight_comments as $key => $value) {
							if (!empty($value[0])) {
								if ($value[0] == "A") {
									echo '<b>' .substr($value, 1). '</b>';
								}elseif ($value[0] == "D") {
									echo ' - <i>' .substr($value, 1). '</i>';
								}elseif ($value[0] == "M") {
									echo '<p>' .nl2br(substr($value, 1)). '</p>';
								}else{
									echo $value;
								}
							}
						}
					?>
				</div>
			</div><!-- end panel -->

			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Data comments</h3>
				</div>
				<div class="panel-body">
					<?php
						$data_comments = explode("&|", $query['data_comments']);
						foreach ($data_comments as $key => $value) {
							if (!empty($value[0])) {
								if ($value[0] == "A") {
									echo '<b>' .substr($value, 1). '</b>';
								}elseif ($value[0] == "D") {
									echo ' - <i>' .substr($value, 1). '</i>';
								}elseif ($value[0] == "M") {
									echo '<p>' .nl2br(substr($value, 1)). '</p>';
								}else{
									echo $value;
								}
							}
						}
					?>
					<?php //echo nl2br($query['data_comments']); ?>
				</div>
			</div><!-- end panel -->

		</div><!-- end col -->

		<?php
		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		$checkboxes = array("nav_data_processing_log", "calibration_file", "processing_settings_file", "configuration_file", "calibration_report", "acceptance_report", "system_not_working", "camera_calibration", "delivered_data_in_archive", "raw_data_in_archive", "raw_data_in_back_up_archive");
		foreach ($checkboxes as $key => $value) {
			$sql = "SELECT user FROM log WHERE changes LIKE '%$value%' ORDER BY datetime DESC LIMIT 1;";
			$result = $conn->query($sql);
			$x = $result->fetch_array(MYSQLI_NUM);
			$changed_by[$value] = $x[0];
		}

		$conn->close();
		?>

		<div class="col-lg-3 col-md-3 col-sm-5 col-xs-12">
			<ul class="list-group">
				<li class="list-group-item <?=($query['calibration_file'] == 'Final') ? 'list-group-item-success' : 'list-group-item-warning'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['calibration_file']; ?>">
					Calibration file: <br>
  					<b style="padding-left: 30px !important;"><?php echo $query['calibration_file'] ?></b>
				</li>
				<li class="list-group-item <?=$query['raw_data_in_archive'] ? 'list-group-item-success' : 'list-group-item-warning'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['raw_data_in_archive']; ?>">
					Raw data in archive
				</li>
				<li class="list-group-item <?=$query['raw_data_in_back_up_archive'] ? 'list-group-item-success' : 'list-group-item-warning'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['raw_data_in_back_up_archive']; ?>">
					Raw data in back up archive
				</li>
				<li class="list-group-item <?=$query['nav_data_processing_log'] ? 'list-group-item-success' : 'list-group-item-warning'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['nav_data_processing_log']; ?>">
					Nav. data processing log
				</li>
				<li class="list-group-item <?=$query['processing_settings_file'] ? 'list-group-item-success' : 'list-group-item-warning'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['processing_settings_file']; ?>">
						Processing settings file
				</li>
				<li class="list-group-item <?=$query['configuration_file'] ? 'list-group-item-success' : 'list-group-item-warning'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['configuration_file']; ?>">
					Configuration file
				</li>
				<li class="list-group-item <?=$query['calibration_report'] ? 'list-group-item-success' : 'list-group-item-warning'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['calibration_report']; ?>">
					Calibration report
				</li>
				<li class="list-group-item <?=$query['acceptance_report'] ? 'list-group-item-success' : 'list-group-item-warning'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['acceptance_report']; ?>">
					Acceptance report
				</li>
				<li class="list-group-item <?=$query['delivered_data_in_archive'] ? 'list-group-item-success' : 'list-group-item-warning'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['delivered_data_in_archive']; ?>">
					Delivered data into archive
				</li>
				<li class="list-group-item <?=$query['camera_calibration'] ? 'list-group-item-success' : 'list-group-item-warning'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['camera_calibration']; ?>">
					Camera calibration
				</li>
				<li class="list-group-item <?=$query['system_not_working'] ? 'list-group-item-danger' : 'list-group-item-success'; ?>" data-toggle="tooltip" title="<?php echo $changed_by['system_not_working']; ?>">
					System not working
				</li>
			</ul>

		</div><!-- end col -->

	</div><!-- end row -->


</section>

<footer>

</footer>

<?php include 'res/footer.inc.php'; ?>
