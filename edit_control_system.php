<?php

$titel = 'Edit Control System';
include 'res/header.inc.php';
$type = 'add_control_system';
if (!empty($_GET['serial_nr'])) {
	$type = 'update_control_system';

	// Create connection
	include 'res/config.inc.php';
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
    	die("Connection failed: " . $conn->connect_error);
	}

	//
	$sn = $_GET['serial_nr'];
	$sql = "SELECT *
			FROM control_system
			WHERE serial_nr = $sn";
	$result = $conn->query($sql);
	if (!$result) {
		echo "Error: " . $sql_insert . "<br>" . $conn->error;
		die();
	}

    $row = $result->fetch_array(MYSQLI_ASSOC);
    $conn->close();
}
$path = 'post.php?type=' . $type; // path for form
?>
<?php require_once('res/functions.inc.php'); ?>
<script type="text/javascript">
  $(document).ready(function(){
    $('.combobox').combobox();
  });
</script>
<section class="content">
	
<form action= <?php echo $path ?> method="post" class="form-horizontal">
  <div class="row">
	<div class="col-sm-6 col-sm-offset-1">

	<div class="col-xs-8 col-xs-offset-4"><h4>Control System</h4></div>

	  <div class="form-group">
		<label for="serial_nr" class="col-xs-4 control-label">Serial Number</label>
	  	<div class="col-xs-8">
<?php
if (!empty($_GET['serial_nr'])) {
	echo '<input type="hidden" name="serial_nr" value="' . $_GET['serial_nr'] . '" />'
	. '<input type="text" class="form-control" placeholder="' . $_GET['serial_nr'] . '" disabled />';
}else {
	echo '<input type="text" class="form-control" name="serial_nr" />';
}
?>
	  	</div>
	  </div>

  	  <div class="form-group">
		<label for="battery" class="col-xs-4 control-label">Battery</label>
	  <div class="col-xs-8">
	  	<input type="text" class="form-control" name="battery" <?= !empty($row['battery']) ?  'value="' . $row['battery'] . '"' : '' ; ?>>
	  	</div>
	  </div>

  	  <div class="form-group">
		<label for="cc32" class="col-xs-4 control-label">CC32</label>
	  <div class="col-xs-8">
	  	<input type="text" class="form-control" name="cc32" <?= !empty($row['cc32']) ?  'value="' . $row['cc32'] . '"' : '' ; ?>>
	  	</div>
	  </div>

  	  <div class="form-group">
		<label for="pdu" class="col-xs-4 control-label">PDU</label>
	  <div class="col-xs-8">
	  	<input type="text" class="form-control" name="pdu" <?= !empty($row['pdu']) ?  'value="' . $row['pdu'] . '"' : '' ; ?>>
	  	</div>
	  </div>

  	  <div class="form-group">
		<label for="scu" class="col-xs-4 control-label">SCU</label>
	  <div class="col-xs-8">
	  	<select class="combobox form-control" name="scu">
	  	  
<?php
$sn = '';
if(!empty($row['scu'])){ $sn = $row['scu'];}
listUnusedSerialNr('scu', '	serial_nr NOT IN (
	            			SELECT control_system.scu_sn
	            			FROM control_system)'	, $sn);
?>

		</select>
	  	</div>
	  </div>

	</div> <!-- end col -->
	<div class="col-sm-3 col-sm-offset-1">

	  <h4>Log</h4>
		<div class="form-group col-xs-12">
			<label for="user">User</label>
			<div>
				<input type="text" class="form-control" name="user" required />
			</div>
		</div>

		<div class="form-group col-xs-12">
			<label for="log_comment">Log Comment</label>
			<div>
				<textarea class="form-control" name="log_comment" rows="3"><?= !empty($row['log_comment']) ? $row['log_comment'] : ''; ?></textarea>
			</div>
		</div>
	</div>

  </div><!-- end row -->
  <div class="row">
	  <div class="col-sm-12">
	    <button type="submit" class="btn btn-default">Apply</button>
	    <a href="parts.php" class="btn btn-default">Cancel</a>
	  </div>
  </div>
</form>
</section>
<footer>

</footer>

<?php include 'res/footer.inc.php'; ?>