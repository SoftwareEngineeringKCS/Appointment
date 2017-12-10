<?php #JORGE ESPADA
	
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
				# Creating Random Confirmation Code... Failed! [Connection Error]
				# Creating Static Confirmation Code.
				$code = date_format(date_create($btndatetime), "YmdHi") . "-" . $studentid;
				$c = 0;
			}
		}
		mysqli_close($f_conex);
		return $code;
	}
	
	$conf_show_error = FALSE; // Maintenance.
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

	$getSAD = $_REQUEST['getSAD'];
	$getSST = $_REQUEST['getSST'];
	$getSZC = $_REQUEST['getSZC'];
	$getSBD = $_REQUEST['getSBD'];
	$getSHP = $_REQUEST['getSHP'];
	$getSGE = $_REQUEST['getSGE'];
	$getSER = $_REQUEST['getSER'];
	$getSED = $_REQUEST['getSED'];
	$getSMA = $_REQUEST['getSMA'];

	# Update Student.
	$query = sprintf("UPDATE Students 
						SET first_name = '%s', last_name = '%s', email = '%s', address = '%s', state = '%s', zipcode = '%s', major_id = '%s', edu_id = '%s', er_id = '%s', gender = '%s', birthdate = '%s', cell_phone = '%s', home_phone = '%s' 
						WHERE id = '%s'", 
						$getSFN, $getSLN, $getSEM, $getSAD, $getSST, $getSZC, $getSMA, $getSED, $getSER, $getSGE, $getSBD, $getSCP, $getSHP, $getSID);
	$conf_res1 = mysqli_query($conex, $query);

	if (mysqli_affected_rows($conex) == 0) {
		echo "<p class='error'>Updating student new data... Failed! [No Student found]";
		if ($conf_show_error) {
			echo "<br>[<i>" . mysqli_error() . "</i>]";
		}
		echo "<br>Contact Administrator!</p>";
		echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
	} else {
		# Save appointment. 
		$pos = strpos($getBTN, ",");
		$getTimeId = substr($getBTN, 0, $pos);
		$getDateTime = substr($getBTN, $pos+1);
		$getCode = createCode($getSID, $getDateTime);
		$query = sprintf("INSERT INTO Students_Appointment VALUES(NULL, '%s', '%s', '%s', '%s', '%s', '%s', 0, 0, '', 0, '')", $getSID, $getCID, $getLID, $getRID, $getDateTime, $getCode);
		$conf_res2 = mysqli_query($conex, $query);
		
		if (mysqli_affected_rows($conex) == 0) {
			echo "<p class='result'>Updating student new data... Done!</p>";
			echo "<p class='error'>Saving Appointment... Failed! [Connection Error]";
			if ($conf_show_error) {
				echo "<br>[<i>" . mysqli_error() . "</i>]";
			}
			echo "<br>Contact Administrator!</p>";
			echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
		} else {
			# Show results...
			echo "<p class='result'>Updating student new data... Done!";
			echo "<br>Saving Appointment... Done!";

			# Update Time Status.
			$query = sprintf("UPDATE Availability_Times SET free = 0 WHERE id = '%s'", $getTimeId);
			$conf_res3 = mysqli_query($conex, $query);

			if (mysqli_affected_rows($conex) == 0) {
				echo "<p class='error'>Disabling taken time in the book... Failed! [Connection Error]";
				if ($conf_show_error) {
					echo "<br>[<i>" . mysqli_error() . "</i>]";
				}
				echo "<br>Contact Administrator!</p>";
			} else {
				echo "<br>Disabling taken time in the book... Done!";
			}

			# ... Results.
			echo "<br>Student ID: " . $getSID;
			echo "<br>Name: " . $getSLN . ", " . $getSFN;
			
			$query = sprintf("SELECT description FROM Reasons WHERE id = '%s'", $getRID);
			$conf_res4 = mysqli_query($conex, $query);
			if ($conf_res4) {
				if (mysqli_num_rows($conf_res4) > 0) {
					$row = mysqli_fetch_array($conf_res4);
					echo "<br>Reason: " . $row[0];
				} else {
					echo "<br><font size='2' color=red>Reason: [No Reason found]</font>";	
				}
				mysqli_free_result($conf_res4);
			} else {
				echo "<br><font size='2' color=red>Reason: [Connection Error]</font>";
			}
			
			$query = sprintf("SELECT CONCAT(last_name,', ',first_name) FROM Consultants WHERE id = '%s'", $getCID);
			$conf_res5 = mysqli_query($conex, $query);
			if ($conf_res5) {
				if (mysqli_num_rows($conf_res5) > 0) {
					$row = mysqli_fetch_array($conf_res5);
					echo "<br>Consultant: " . $row[0];
				} else {
					echo "<br><font size='2' color=red>Consultant: [No Consultant found]</font>";	
				}
				mysqli_free_result($conf_res5);
			} else {
				echo "<br><font size='2' color=red>Consultant: [Connection Error]</font>";	
			}
			
			$query = sprintf("SELECT CONCAT(detail,' ',building_id,room) FROM Locations WHERE id = '%s'", $getLID);
			$conf_res6 = mysqli_query($conex, $query);
			if ($conf_res6) {
				if (mysqli_num_rows($conf_res6) > 0) {
					$row = mysqli_fetch_array($conf_res6);
					echo "<br>Location: " . $row[0];
				} else {
					echo "<br><font size='2' color=red>Location: [No Location found]</font>";	
				}
				mysqli_free_result($conf_res6);
			} else {
				echo "<br><font size='2' color=red>Location: [Connection Error]</font>";
			}
			
			# Send confirmation code (bye email).
			$query = sprintf("SELECT email FROM Consultants WHERE id = '%s'", $getCID);
			$conf_res7 = mysqli_query($conex, $query);
			if ($conf_res7) {
				if (mysqli_num_rows($conf_res7) > 0) {
					$row = mysqli_fetch_array($conf_res7);
					if (sendEmail($row['email'], $getSEM, $getCode)) {
						echo "<br><h2 style='color: #6CBB3C'>A Confirmation Code was sent to your email!</h2>";
					} else {
						echo "<br><font size='2' color=red>Sending Confirmation Code to Student's email... Failed! [No Email Server]</font>";
					}
				} else {
					if (sendEmail("", $getSEM, $getCode)) {
						echo "<br><h2 style='color: #6CBB3C'>A Confirmation Code was sent to your email!</h2>";
					} else {
						echo "<br><font size='2' color=red>Sending Confirmation Code to Student's email... Failed! [No Email Server]</font>";
					}
				}
			} else {
				echo "<p class='error'>Getting Consultant's email... Failed! [Connection Error]";
				if ($show_error) {
					echo "<br>[<i>" . mysqli_error() . "</i>]";
				}
				echo "<br>Contact Administrator!</p>";
			}

			echo "</p>";

			# Auto-Redirect


		}	
	}

	mysqli_close($conex);

?>
