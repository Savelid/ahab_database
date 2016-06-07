<?php
function listUnusedSerialNr($from, $where, $id){
	$list = listAll("SELECT serial_nr FROM " .$from. " WHERE " .$where);
	if ($list == NULL) {
		debug_to_console("listUnusedSerialNr: Receved a NULL list");
	}else {
		$str = formatForSelect($list, $id);
		//debug_to_console($str);
		echo $str;
	}
}
?>

<?php
function listAllX($select, $from, $where, $id){

	$list = listAll('SELECT ' .$select. ' FROM ' .$from. ' ' .$where);
	if ($list == NULL) {
		debug_to_console("listAllX: Receved a NULL list");
	}else {
		$str = formatForSelect($list, $id);
		echo $str;
	}
}
?>

<?php
function listAll($qurey){
	// open db
	include 'res/config.inc.php';
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	$result = $conn->query($qurey);
	if (!$result) {
		debug_to_console("Query failed!" . $qurey . "<br>" . $conn->error);
		die("Query failed!" . $qurey . "<br>" . $conn->error);
	}
	while($row =$result->fetch_array(MYSQLI_NUM)) {
		$list[] = $row[0];
	}
	$conn->close();

	if (isset($list)) {
		return $list;
	}else {
		return NULL;
	}
}
?>

<?php
function formatForSelect($listOfItems, $currentId){
	if($currentId != NULL && $currentId != ''){
		$return_string = '<option value="' . $currentId . '">' . $currentId . '</option>';
	}
	else {
		$return_string = '<option></option>';
	}
	$return_string .= '<option>-</option>';
	foreach ($listOfItems as $key => $value) {
		$return_string .= '<option value="' .$value. '">' .$value. '</option>';
	}

	return $return_string;
}
?>

<?php
/**
* Send debug code to the Javascript console
*/
function debug_to_console($data) {
	if(is_array($data) || is_object($data))
	{
		echo("<script>console.log('PHP: ".json_encode($data)."');</script>");
	} else {
		echo("<script>console.log('PHP: ".$data."');</script>");
	}
}
?>

<?php
// @param id_name - Set what column will be the key when picking one item for updates.
// @param table - What table will the data be stored in.
// @param database_columns - a string with all columns and the values.
// @param redirect - Where will the user be sent after the query is done.
// @return array with results from databse.

function postFunction($id_name, $table, $database_columns, $redirect){

	if(!empty($_GET[$id_name])){
		$row = getDatabaseRow($table, $id_name, $_GET[$id_name]);
	}
	if (isset($_POST[$id_name])) {
		$post_status = postToDatabase($table, $id_name, $_POST[$id_name], $database_columns);

		$_SESSION['showalert'] = 'true';
		$_SESSION['alert'] = $table . ": " . $post_status['status'];
		$_SESSION['alert'] .= "<br><br>";
		$_SESSION['alert'] .= $post_status['updates'];

		$log_status = postToLog($_POST[$id_name], $post_status['type'] . " " . $table, $post_status['query'], $post_status['updates'], $_POST['user'], $_POST['log_comment']);
		header("Location: " . $redirect);
	}

	if (isset($row)) {
		return $row;
	}else {
		debug_to_console("postFunction: Return null");
		return NULL;
	}
}
?>

<?php
// @param table - What table will the data be stored in.
// @param id_name - Set what column will be the key when picking one item for updates.
// @param id - Set the id coresponding to the id_name
// @param database_columns - a string with all columns and the values.
// @return an array with strings.
// 'changes' made in the current row.
// 'query' used to post to database.
// 'type' update or add
//
// Updating a current row if awailable, otherwise posting to a new row.
// Also makes a string with changes done to the affected row.

function postToDatabase($table, $id_name, $id, $database_columns){
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$changes = "";
		$query = "";
		$type = "";
		$status = "";
		//session_start();
		include 'res/config.inc.php';
		// Make sure username is saved between pages.
		if(!empty($_POST['user'])){
			$_SESSION['user'] = $_POST['user'];
		}
		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		// Get the names of the columns. Will be shown toghether with the list of changes
		$sql_col_names = "SHOW COLUMNS FROM $table;";
		$result_col_names = $conn->query($sql_col_names);
		if ($result_col_names->num_rows > 0) {
			// output data of each row
			$i = 0;
			while($row_col_names = $result_col_names->fetch_assoc()) {
				$column_names[$i] = $row_col_names['Field'];
				$i++;
			}
		}

		$sql = "SELECT * FROM $table WHERE $id_name = '$id';";
		$result = $conn->query($sql);
		if ($result->num_rows < 1) {

			debug_to_console("Creating new row");
			$query = "INSERT INTO $table SET $id_name = '$id', " . $database_columns . ";";
			$type = 'Add';
			if ($conn->query($query) === TRUE) {
				$status = "New record created successfully";
				//split string
				$tags = explode(',',$database_columns);
				//print only those that are not empty
				foreach($tags as $key) {
					$pos = strpos($key, "''");
					if ($pos === false) {
						$changes .= $key.'<br/>';
					}
				}
			}else{
				$status = "New record failed <br>" . $sql . "<br>" . $conn->error;
			}

		}else {
			$row = $result->fetch_array(MYSQLI_BOTH);
			debug_to_console("result added to row");
			$query = "UPDATE $table SET " . $database_columns . " WHERE $id_name = '$id' ;";
			$type = 'Update';
			if ($conn->query($query) === TRUE) {
				$status= "Record updated successfully";
				$sql = "SELECT * FROM $table WHERE $id_name = '$id';";
				$result2 = $conn->query($sql);
				if (!$result2) {
					$status .= "Failed to query new data :( <br>" . $sql . "<br>" . $conn->error;
				}else {
					$new_row = $result2->fetch_array(MYSQLI_NUM);
					for ($x = 0; $x <= count($new_row); $x++) {
						if(!empty($new_row[$x]) && !empty($new_row[$x])){
							if(strcmp($new_row[$x], $row[$x]) === 0) {
								debug_to_console("Skip unchanged item");
							}else {
								$this_col = "";
								if (isset($column_names)) {
									//$this_col = $column_names[$x];
									$this_col = sprintf("%20s", $column_names[$x]); // left-justification with spaces
								}
								$changes .=  $this_col ." | ". $row[$x] ." -> ".$new_row[$x]."<br>";
							}
						}else {
							debug_to_console("Minor error: Empty row");
						}
					}
				}
			}else{
				$status= "Update failed <br>" . $sql . "<br>" . $conn->error;
			}
		}
		$conn->close();
		return array(
			'updates'  => $changes,
			'query' => $query,
			'type' => $type,
			'status' => $status
		);
	}
	debug_to_console("postToDatabase: Return null");
	return NULL;
}
?>

<?php
// @param table - What table will be queried.
// @param id_name - Set what column will be the key when picking one row
// @param id - Set the id coresponding to the id_name
// @return array with results from databse.
//
// Querries the database and return exactly one row, or NULL

function getDatabaseRow($table, $id_name, $id){

	include 'res/config.inc.php';

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$sql = "SELECT * FROM $table WHERE $id_name = '$id';";
	$result = $conn->query($sql);
	if ($result->num_rows < 1) {
		echo $sql;
		debug_to_console("Query for this id failed, no results");
	}
	elseif ($result->num_rows > 1) {
		echo $sql;
		debug_to_console("Query for this id failed, too many results");
	}else {
		$row = $result->fetch_array(MYSQLI_BOTH);
		debug_to_console("result added to row");
	}

	$conn->close();
	if (isset($row)) {
		return $row;
	}else {
		debug_to_console("getDatabaseRow: Return null");
		return NULL;
	}
}
?>

<?PHP
// Add all requests saved by this page to LOG
function postToLog($id, $type, $query, $changes, $user, $comment) {

	// Create connection
	include 'res/config.inc.php';
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Log: db connection failed: " . $conn->connect_error);
	}
	$query = $conn->real_escape_string($query);
	$changes = $conn->real_escape_string($changes);
	$sql_log = "INSERT INTO log SET type = '$type', user = '$user', sql_string = '$query', changes = '$changes', serial_nr = '$id', comment = '$comment';";
	if ($conn->query($sql_log) === TRUE) {
		$status = "Log created successfully";

	} else {
		$status = "Log Error: " . $sql_log . "<br>" . $conn->error;
	}
	$conn->close();
	return $status;
}
?>

<?php
function uploadFile($input_file_type, $name, $name_prefix, $target_dir, $max_size){
	//debug_to_console("POST true. flight_logs: " . $_FILES["flight_logs"]["name"]);
	if ($_FILES[$name]['size'] > 0 && $_FILES[$name]['error'] == 0){
		debug_to_console($name. " not empty");
		$target_file = $target_dir . $name_prefix . basename($_FILES[$name]["name"]);
		$uploadOk = 1;

		// Check if file already exists
		if (file_exists($target_file)) {
    		$status_msg .= "File already exists. file will be overwriten <br>";
    		//$uploadOk = 0;
		}

		$isRightFileType = _uploadFile_test_fileType($target_file, $input_file_type);
		if(!$isRightFileType){
			$status_msg .= "Sorry, this file format is not allowed.";
			$uploadOk = 0;
		}
		$isRightSize = _uploadFile_test_size($name, $max_size);
		if(!$isRightSize){
			$status_msg .= "Sorry, this file has an invalid size.";
			$uploadOk = 0;
		}

		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
		    $status_msg .= " Your file was not uploaded.";
		// if everything is ok, try to upload file
		} else {
		    if (move_uploaded_file($_FILES[$name]["tmp_name"], $target_file)) {
		        $status_msg .= "The file ". basename( $_FILES[$name]["name"]). " has been uploaded.";
		    } else {
		        $status_msg .= "Sorry, there was an error uploading your file.";
		    }
		}
		return array(	"status_msg" => $status_msg,
									"upload_ok" => $uploadOk,
									"file_name" => basename($_FILES[$name]["name"]),
									"file_path" => $target_file);
	}
	return NULL;
}

function _uploadFile_test_size($name, $max_size){
	// Check file size
	if ($_FILES[$name]["size"] > $max_size) {
		 return false;
	}else {
		return true;
	}
}

function _uploadFile_test_fileType($target_file, $input_file_type){
	$fileType = pathinfo($target_file,PATHINFO_EXTENSION);
	// Allow certain file formats
	if (is_array($input_file_type)) {
		foreach ($input_file_type as $key => $value) {
			if($fileType == $value) {
					return true;
			}
		}
	}else{
		if($fileType == $input_file_type) {
				return true;
		}
	}
	return false;
}
?>
