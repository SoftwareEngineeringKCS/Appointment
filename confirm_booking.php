<?php 
	
	function sendEmail($from, $to, $confirmation) {
		 $subject = "TESTING - Appointment Confirmation Code";
		 $header = "From: " . $from;
		 $message = "Dear Student, #THIS IS ONLY A TEST#
		 		 	\nYour appointment confirmation code is " . $confirmation . ". 
		 		 	\nBest regards,
		 		 	\nKean Career Services";
		 if ($from == "") {
		 	return mail($to, $subject, $message);
		 } else {
		 	return mail($to, $subject, $message, $header);
		 }
	}
	
	function createCode($studentid, $btndatetime) {
		include ('includes/db_config_function.php');
		$code = "";
		$c = 1;	
		while ($c > 0) {
			$random_hash = md5(uniqid(rand(), true));
			$processed_hash = strtoupper("K" . substr($random_hash, 3,3) . substr($random_hash, 9, 3) . substr($random_hash, 15, 3));
			$f_query = sprintf("SELECT confirm_num FROM Students_Appointment WHERE confirm_num = '%s'", $processed_hash);
			$f_result = mysqli_query($f_conex, $f_query);
			if ($f_result) {
				if (mysqli_num_rows($f_result) > 0) {
					$c = 1;
				} else {
					$code = $processed_hash;
					$c = 0;
				}
				mysqli_free_result($f_result);
			} else {
				echo "<p class='error'>Problem trying to create dynamic confirmation code, a static code was created instead.</p>";
				# Manual Code.
				$code = date_format(date_create($btndatetime), "YmdHi") . "-" . $studentid;
				$c = 0;
			}
		}
		mysqli_close($f_conex);
		return $code;
	}
	
	# If ID but no Email.
	include ('includes/db_config.php');

	// Print the results:
	echo "<h1>Appointment Result</h1>";

	$getSID = $_REQUEST['getSID'];
	$getSFN = $_REQUEST['getSFN'];
	$getSLN = $_REQUEST['getSLN'];
	$getSEM = $_REQUEST['getSEM'];
	$getSCP = $_REQUEST['getSCP'];
	$getBTN = $_REQUEST['getBTN'];
	$getCID = $_REQUEST['getCID'];
	$getLID = $_REQUEST['getLID'];
	$getRID = $_REQUEST['getRID'];

	# Update Student.
	$query = sprintf("UPDATE Students SET first_name = '%s', last_name = '%s', email = '%s', cell_phone = '%s' WHERE id = '%s'", $getSFN, $getSLN, $getSEM, $getSCP, $getSID);
	$result = mysqli_query($conex, $query);
	# Save appointment.
	$pos = strpos($getBTN, ",");
	$getTimeId = substr($getBTN, 0, $pos);
	$getDateTime = substr($getBTN, $pos+1);
	$getCode = createCode($getSID, $getDateTime);
	$query = sprintf("INSERT INTO Students_Appointment VALUES(NULL, '%s', '%s', '%s', '%s', '%s', '%s', 0, 0, '', 0, '')", $getSID, $getCID, $getLID, $getRID, $getDateTime, $getCode);
	$result = mysqli_query($conex, $query);
	# Update Time Status.
	$query = sprintf("UPDATE Availability_Times SET free = 0 WHERE id = '%s'", $getTimeId);
	$result = mysqli_query($conex, $query);
	# Show results.
	echo "<p>Student ID: " . $getSID . "</p>";
	echo "<p>Name: " . $getSLN . ", " . $getSFN . "</p>";
	$query = sprintf("SELECT description FROM Reasons WHERE id = '%s'", $getRID);
	$result = mysqli_query($conex, $query);
	$row = mysqli_fetch_array($result);
	if ($result) {
		echo "<p>Reason: " . $row[0] . "</p>";
	} else {
		echo "<p class='error'>Reason: Error!</p>";
	}
	$query = sprintf("SELECT CONCAT(last_name,', ',first_name) FROM Consultants WHERE id = '%s'", $getCID);
	$result = mysqli_query($conex, $query);
	$row = mysqli_fetch_array($result);
	if ($result) {
		echo "<p>Consultant: " . $row[0] . "</p>";
	} else {
		echo "<p class='error'>Consultant: Error!</p>";
	}
	$query = sprintf("SELECT CONCAT(detail,' ',building_id,room) FROM Locations WHERE id = '%s'", $getLID);
	$result = mysqli_query($conex, $query);
	$row = mysqli_fetch_array($result);
	if ($result) {
		echo "<p>Location: " . $row[0] . "</p>";
	} else {
		echo "<p class='error'>Location: Error!</p>";
	}
	# Send confirmation code (bye email).
	$query = sprintf("SELECT email FROM Consultants WHERE id = '%s'", $getCID);
	$result = mysqli_query($conex, $query);
	if ($result) {
		if (mysqli_num_rows($result) > 0) {
			$row = mysqli_fetch_array($result);
			if (sendEmail($row['email'], $getSEM, $getCode)) {
				echo "<p><h2 style='color: #6CBB3C'>A Confirmation Code was sent to your email!</h2></p>";
			} else {
				echo "<br><font size='2' color=red>Sending Confirmation Code to Student's email... Failed! [Email Server]</font>";
			}
		} else {
			if (sendEmail("", $getSEM, $getCode)) {
				echo "<p><h2 style='color: #6CBB3C'>A Confirmation Code was sent to your email!</h2></p>";
			} else {
				echo "<br><font size='2' color=red>Sending Confirmation Code to Student's email... Failed! [Email Server]</font>";
			}
		}
	} else {
		echo "<br>Problem trying to send confirmation code.";
		echo "<br>Contact Administrator!";
	}

	mysqli_free_result($result);
	mysqli_close($conex);

?>
