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

	$show_error = FALSE; // Maintenance.
	$page_title = 'Kean Career Services';
	include ('includes/header.html');
	include ('includes/db_config.php');

	echo "<div class='menu_help' id='help' style='display: none;'>";
	echo "<p><b>Staff:</b><br>Administrators can set-up availability periods, manage appointments, and view statistics. Administrators must login in order to use these features.</p>";
	echo "<p><b>Appointments:</b><br>Students can book appointments and update personal information from previous meetings.</p>";
	echo "<p><b>Check-In:</b><br>Let the office know that you are waiting for counseling. There are two options: (1) By-Appointment, you will need your student id and a confirmation code which was sent to you by email. (2) Walk-In, no appointment is needed (longer waiting time).</p>";
	echo "<center><p><< CLICK HELP TO CLOSE >></p></center>";
	echo "</div>";
	
	// Check for form submission:
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		echo "<div id='appointment_result' style='display: block;'>";
		// Print the results:
		echo "<h1>Appointment Result</h1>";
		// Minimal form validation:
		if (isset($_POST['student_id'], $_POST['first_name'], $_POST['email'])) {

			if ($_POST['location'] == '' || $_POST['consultant'] == '' || $_POST['reason'] == '' || 
				$_POST['student_id'] == '' || $_POST['first_name'] == '' || $_POST['last_name'] == '' || 
				$_POST['email'] == '' || $_POST['state'] == '' || $_POST['zipcode'] == '' || $_POST['major'] == '' || $_POST['education'] == '' || $_POST['ethnic_racial'] == '' || 
				$_POST['gender'] == '') {
				echo "<h2>The following fields cannot be empty!</h2>";
				echo "<p class='error'>";
				if ($_POST['reason'] == '') echo "\"Reason\", ";
				if ($_POST['student_id'] == '') echo "\"ID\", ";
				if ($_POST['first_name'] == '') echo "\"First Name\", ";
				if ($_POST['last_name'] == '') echo "\"Last Name\", ";
				if ($_POST['email'] == '') echo "\"E-mail\", ";
				if ($_POST['consultant'] == '') echo "\"Consultant\", ";
				if ($_POST['location'] == '') echo "\"Location\", ";
				if ($_POST['state'] == '') echo "\"State\", ";
				if ($_POST['zipcode'] == '') echo "\"Zipcode\", ";
				if ($_POST['major'] == '') echo "\"Major\", ";
				if ($_POST['education'] == '') echo "\"Education (year)\", ";
				if ($_POST['ethnic_racial'] == '') echo "\"Race/Ethnicity\", ";
				if ($_POST['gender'] == '') echo "\"Gender\"";
				echo "</p>";
				echo "<p><button type='button' style='height: 30px;' onclick='mainDisplay(this)'>BACK</button></p>";
			} else {
				# Validate Id and Email.
				$query = sprintf("SELECT * FROM Students WHERE id = '%s' AND email = '%s'", $_POST['student_id'], $_POST['email']);
				$result = mysqli_query($conex, $query);
				if ($result) {
					if (mysqli_num_rows($result) > 0) {
						# Validate Identical data when updating (Rows changed = 0).
						$row = mysqli_fetch_array($result);
						$getFname = $row['first_name'];
						$getLname = $row['last_name'];
						$getMid = $row['major_id'];
						$getEid = $row['edu_id'];
						$getREid = $row['er_id'];
						$getGender = $row['gender'];
						$getBD = $row['birthdate'];
						$getCphone = $row['cell_phone'];
						$getHphone = $row['home_phone'];
						$getAddress = $row['address'];
						$getState = $row['state'];
						$getZC = $row['zipcode'];

						/* TEST
						echo "<br>TEST: " . $_POST['first_name'];
						echo "<br>TEST: " . $_POST['last_name'];
						echo "<br>TEST: " . $_POST['address'];
						echo "<br>TEST: " . $_POST['state'];
						echo "<br>TEST: " . $_POST['zipcode'];
						echo "<br>TEST: " . $_POST['major'];
						echo "<br>TEST: " . $_POST['education'];
						echo "<br>TEST: " . $_POST['ethnic_racial'];
						echo "<br>TEST: " . $_POST['gender'];
						echo "<br>TEST: " . $_POST['birthdate'];
						echo "<br>TEST: " . $_POST['cell_phone'];
						echo "<br>TEST: " . $_POST['home_phone'];
						echo "<br>TEST: " . $_POST['student_id'];
						*/

						# Update Student.
						$query = sprintf("UPDATE Students SET first_name = '%s', last_name = '%s', address = '%s', state = '%s', zipcode = '%s', major_id = '%s', edu_id = '%s', er_id = '%s', gender = '%s', birthdate = '%s', cell_phone = '%s', home_phone = '%s' WHERE id = '%s'", $_POST['first_name'], $_POST['last_name'], $_POST['address'], $_POST['state'], $_POST['zipcode'], $_POST['major'], $_POST['education'], $_POST['ethnic_racial'], $_POST['gender'], $_POST['birthdate'], $_POST['cell_phone'], $_POST['home_phone'], $_POST['student_id']);
						$res1 = mysqli_query($conex, $query);

						if (mysqli_affected_rows($conex) == 0 && ($getFname != $_POST['first_name'] || $getLname != $_POST['last_name'] || $getAddress != $_POST['address'] || $getState != $_POST['state'] || $getZC != $_POST['zipcode'] || $getMid != $_POST['major'] || $getEid != $_POST['education'] || $getREid != $_POST['ethnic_racial'] || $getGender != $_POST['gender'] || $getBD != $_POST['birthdate'] || $getCphone != $_POST['cell_phone'] || $getHphone != $_POST['home_phone'])) {
							echo "<p class='error'>Validating ID and Email... Failed! [No Student found]";
							if ($show_error) {
								echo "<br>[<i>" . mysqli_error() . "</i>]";
							}
							echo "<br>Contact Administrator!</p>";
							echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
						} else {
							# Save appointment.
							$pos = strpos($_POST['btnbook'], ",");
							$getTimeId = substr($_POST['btnbook'], 0, $pos);
							$getDateTime = substr($_POST['btnbook'], $pos+1);
							$getCode = createCode($_POST['student_id'], $getDateTime);
							$query = sprintf("INSERT INTO Students_Appointment VALUES(NULL, '%s', '%s', '%s', '%s', '%s', '%s', 0, 0, '', 0, '')", $_POST['student_id'], $_POST['consultant'], $_POST['location'], $_POST['reason'], $getDateTime, $getCode);
							$res2 = mysqli_query($conex, $query);

							if (mysqli_affected_rows($conex) == 0) {
								echo "<p class='result'>Updating student new data... Done!</p>";
								echo "<p class='error'>Saving Appointment... Failed! [Connection Error]";
								if ($show_error) {
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
								$res3 = mysqli_query($conex, $query);

								if (mysqli_affected_rows($conex) == 0) {
									echo "<p class='error'>Disabling taken time in the book... Failed! [Connection Error]";
									if ($show_error) {
										echo "<br>[<i>" . mysqli_error() . "</i>]";
									}
									echo "<br>Contact Administrator!</p>";
								} else {
									echo "<br>Disabling taken time in the book... Done!";
								}

								# ... Results.
								echo "<br>Student ID: " . $_POST['student_id'];
								echo "<br>Name: " . $_POST['last_name'] . ", " . $_POST['first_name'];

								$query = sprintf("SELECT description FROM Reasons WHERE id = '%s'", $_POST['reason']);
								$res4= mysqli_query($conex, $query);
								if ($res4) {
									if (mysqli_num_rows($res4) > 0) {
										$row = mysqli_fetch_array($res4);
										echo "<br>Reason: " . $row[0];
									} else {
										echo "<br><font size='2' color=red>Reason: [No Reason found]</font>";	
									}
									mysqli_free_result($res4);
								} else {
									echo "<br><font size='2' color=red>Reason: [Connection Error]</font>";
								}

								$query = sprintf("SELECT CONCAT(last_name,', ',first_name) FROM Consultants WHERE id = '%s'", $_POST['consultant']);
								$res5 = mysqli_query($conex, $query);
								if ($res5) {
									if (mysqli_num_rows($res5) > 0) {
										$row = mysqli_fetch_array($res5);
										echo "<br>Consultant: " . $row[0];
									} else {
										echo "<br><font size='2' color=red>Consultant: [No Consultant found]</font>";	
									}
									mysqli_free_result($res5);
								} else {
									echo "<br><font size='2' color=red>Consultant: [Connection Error]</font>";	
								}

								$query = sprintf("SELECT CONCAT(detail,' ',building_id,room) FROM Locations WHERE id = '%s'", $_POST['location']);
								$res6 = mysqli_query($conex, $query);
								if ($res6) {
									if (mysqli_num_rows($res6) > 0) {
										$row = mysqli_fetch_array($res6);
										echo "<br>Location: " . $row[0];
									} else {
										echo "<br><font size='2' color=red>Location: [No Location found]</font>";	
									}
									mysqli_free_result($res6);
								} else {
									echo "<br><font size='2' color=red>Location: [Connection Error]</font>";
								}

								# Send confirmation code (bye email).
								$query = sprintf("SELECT email FROM Consultants WHERE id = '%s'", $_POST['consultant']);
								$res7 = mysqli_query($conex, $query);
								if ($res7) {
									if (mysqli_num_rows($res7) > 0) {
										$row = mysqli_fetch_array($res7);
										if (sendEmail($row['email'], $_POST['email'], $getCode)) {
											echo "<br><h2 style='color: #6CBB3C'>A Confirmation Code was sent to your email!</h2>";
										} else {
											echo "<br><font size='2' color=red>Sending Confirmation Code to Student's email... Failed! [No Email Server]</font>";
										}
									} else {
										if (sendEmail("", $_POST['email'], $getCode)) {
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

					} else {
						# Validate Student.
						$query = sprintf("SELECT * FROM Students WHERE id = '%s'", $_POST['student_id']);
						$res1 = mysqli_query($conex, $query);
						if ($res1) {
							if (mysqli_num_rows($res1) > 0) {
								echo "<h2>This ID \"" . $_POST['student_id'] .  "\" has been used before!</h2>";
								# Validate email.
								$query = sprintf("SELECT * FROM Students WHERE email = '%s'", $_POST['email']);
								$res2 = mysqli_query($conex, $query);
								if ($res2) {
									if (mysqli_num_rows($res2) > 0) {
										echo "<p class='result'><u>NEW DATA</u>
												<br>  Name: " . $_POST['last_name'] . ", " . $_POST['first_name'] .
												"<br>  E-mail: " . $_POST['email'] . 
												"<br>  Cell-Phone: " . $_POST['cell_phone'] . 
												"</p><p class='ad'><u>BLOCKING</u>: 
												<br>The new email is used by another student, please click back and use a different email.</p>";
										echo "<p><button type='button' style='height: 30px;' onclick='mainDisplay(this)'>BACK</button></p>";
									} else {
										# If confirm.
										$passSID = $_POST['student_id'];
										$passSFN = $_POST['first_name'];
										$passSLN = $_POST['last_name'];
										$passSEM = $_POST['email'];
										$passSCP = $_POST['cell_phone'];
										$passBTN = $_POST['btnbook'];
										$passCID = $_POST['consultant'];
										$passLID = $_POST['location'];
										$passRID = $_POST['reason'];

										$passSAD = $_POST['address'];
										$passSST = $_POST['state'];
										$passSZC = $_POST['zipcode'];
										$passSBD = $_POST['birthdate'];
										$passSHP = $_POST['home_phone'];
										$passSGE = $_POST['gender'];
										$passSER = $_POST['ethnic_racial'];
										$passSED = $_POST['education'];
										$passSMA = $_POST['major'];

										echo "<p class='result'><u>NEW DATA</u>
												<br>  Name: " . $_POST['last_name'] . ", " . $_POST['first_name'] .
												"<br>  E-mail: " . $_POST['email'] . 
												"<br>  Cell-Phone: " . $_POST['cell_phone'] . 
												"</p><p class='ad'><u>WARNING</u>: 
												<br>If you choose \"CONFIRM\" student information will be updated with the new data. 
												\"Be aware that a confirmation code will be sent to your email and you 
												will need both your ID and confirmation code at the time of Check-In. 
												Plus, <u>identification will be required</u>.\" If you believe that
												someone else has used your information, please confirm and let the office
												know about it the day of appointment, thank you.</p>";
										echo "<p><button type='button' style='height: 30px;' onclick='mainDisplay(this)'>BACK</button>
												<button style='height: 30px;' onclick='confirmBooking();'>CONFIRM</button></p>";
									}

									mysqli_free_result($res2);
								} else {
									echo "<p class='error'>Email Validation... Failed! [Connection Error]";
									if ($show_error) {
										echo "<br>[<i>" . mysqli_error() . "</i>]";
									}
									echo "<br>Contact Administrator!</p>";
									echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
								}
							} else {
								# Validate email.
								$query = sprintf("SELECT * FROM Students WHERE email = '%s'", $_POST['email']);
								$res2 = mysqli_query($conex, $query);
								if ($res2) {
									if (mysqli_num_rows($res2) > 0) {
										echo "<p class='result'><u>NEW DATA</u>
												<br>  Name: " . $_POST['last_name'] . ", " . $_POST['first_name'] .
												"<br>  E-mail: " . $_POST['email'] . 
												"<br>  Cell-Phone: " . $_POST['cell_phone'] . 
												"</p><p class='ad'><u>BLOCKING</u>: 
												<br>The new email is used by another student, please click back and use a different email.</p>";
										echo "<p><button type='button' style='height: 30px;' onclick='mainDisplay(this)'>BACK</button></p>";
									} else {
										# Save New Student.
										$query = sprintf("INSERT INTO Students VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '', '', '%s', '%s', '%s', '%s')", $_POST['student_id'], $_POST['first_name'], $_POST['last_name'], $_POST['address'], $_POST['state'], $_POST['zipcode'], $_POST['email'], $_POST['birthdate'], $_POST['cell_phone'], $_POST['home_phone'], $_POST['gender'], $_POST['ethnic_racial'], $_POST['education'], $_POST['major']);
										$res3 = mysqli_query($conex, $query);

										if (mysqli_affected_rows($conex) == 0) {
											echo "<p class='error'>Saving new student... Failed! [Connection Error]";
											if ($show_error) {
												echo "<br>[<i>" . mysqli_error() . "</i>]";
											}
											echo "<br>Contact Administrator!</p>";
											echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
										} else {
											# Save appointment.
											$pos = strpos($_POST['btnbook'], ",");
											$getTimeId = substr($_POST['btnbook'], 0, $pos);
											$getDateTime = substr($_POST['btnbook'], $pos+1);
											$getCode = createCode($_POST['student_id'], $getDateTime);
											$query = sprintf("INSERT INTO Students_Appointment VALUES(NULL, '%s', '%s', '%s', '%s', '%s', '%s', 0, 0, '', 0, '')", $_POST['student_id'], $_POST['consultant'], $_POST['location'], $_POST['reason'], $getDateTime, $getCode);
											$res4 = mysqli_query($conex, $query);

											if (mysqli_affected_rows($conex) == 0) {
												echo "<p class='result'>Saving new student... Done!</p>";
												echo "<p class='error'>Saving Appointment... Failed! [Connection Error]";
												if ($show_error) {
													echo "<br>[<i>" . mysqli_error() . "</i>]";
												}
												echo "<br>Contact Administrator!</p>";
												echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
											} else {
												# Show results...
												echo "<p class='result'>Saving new student... Done!";
												echo "<br>Saving Appointment... Done!";

												# Update Time Status.
												$query = sprintf("UPDATE Availability_Times SET free = 0 WHERE id = '%s'", $getTimeId);
												$res5 = mysqli_query($conex, $query);

												if (mysqli_affected_rows($conex) == 0) {
													echo "<p class='error'>Disabling taken time in the book... Failed! [Connection Error]";
													if ($show_error) {
														echo "<br>[<i>" . mysqli_error() . "</i>]";
													}
													echo "<br>Contact Administrator!</p>";
												} else {
													echo "<br>Disabling taken time in the book... Done!";
												}

												# ... Results.
												echo "<br>Student ID: " . $_POST['student_id'];
												echo "<br>Name: " . $_POST['last_name'] . ", " . $_POST['first_name'];
												
												$query = sprintf("SELECT description FROM Reasons WHERE id = '%s'", $_POST['reason']);
												$res6 = mysqli_query($conex, $query);
												if ($res6) {
													if (mysqli_num_rows($res6) > 0) {
														$row = mysqli_fetch_array($res6);
														echo "<br>Reason: " . $row[0];
													} else {
														echo "<br><font size='2' color=red>Reason: [No Reason found]</font>";	
													}
													mysqli_free_result($res6);
												} else {
													echo "<br><font size='2' color=red>Reason: [Connection Error]</font>";
												}
												
												$query = sprintf("SELECT CONCAT(last_name,', ',first_name) FROM Consultants WHERE id = '%s'", $_POST['consultant']);
												$res7 = mysqli_query($conex, $query);
												if ($res7) {
													if (mysqli_num_rows($res7) > 0) {
														$row = mysqli_fetch_array($res7);
														echo "<br>Consultant: " . $row[0];
													} else {
														echo "<br><font size='2' color=red>Consultant: [No Consultant found]</font>";	
													}
													mysqli_free_result($res7);
												} else {
													echo "<br><font size='2' color=red>Consultant: [Connection Error]</font>";	
												}
												
												$query = sprintf("SELECT CONCAT(detail,' ',building_id,room) FROM Locations WHERE id = '%s'", $_POST['location']);
												$res8 = mysqli_query($conex, $query);
												if ($res8) {
													if (mysqli_num_rows($res8) > 0) {
														$row = mysqli_fetch_array($res8);
														echo "<br>Location: " . $row[0];
													} else {
														echo "<br><font size='2' color=red>Location: [No Location found]</font>";	
													}
													mysqli_free_result($res8);
												} else {
													echo "<br><font size='2' color=red>Location: [Connection Error]</font>";
												}
												
												# Send confirmation code (bye email).
												$query = sprintf("SELECT email FROM Consultants WHERE id = '%s'", $_POST['consultant']);
												$res9 = mysqli_query($conex, $query);
												if ($res9) {
													if (mysqli_num_rows($res9) > 0) {
														$row = mysqli_fetch_array($res9);
														if (sendEmail($row['email'], $_POST['email'], $getCode)) {
															echo "<br><h2 style='color: #6CBB3C'>A Confirmation Code was sent to your email!</h2>";
														} else {
															echo "<br><font size='2' color=red>Sending Confirmation Code to Student's email... Failed! [No Email Server]</font>";
														}
													} else {
														if (sendEmail("", $_POST['email'], $getCode)) {
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
									}

									mysqli_free_result($res2);
								} else {
									echo "<p class='error'>Email Validation... Failed! [Connection Error]";
									if ($show_error) {
										echo "<br>[<i>" . mysqli_error() . "</i>]";
									}
									echo "<br>Contact Administrator!</p>";
									echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
								}
							}

							mysqli_free_result($res1);
						} else {
							echo "<p class='error'>Student Validation... Failed! [Connection Error]";
							if ($show_error) {
								echo "<br>[<i>" . mysqli_error() . "</i>]";
							}
							echo "<br>Contact Administrator!</p>";
							echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
						}
					}

					mysqli_free_result($result);
				} else {
					echo "<p class='error'>Student Validation... Failed! [Connection Error]";
					if ($show_error) {
						echo "<br>[<i>" . mysqli_error() . "</i>]";
					}
					echo "<br>Contact Administrator!</p>";
					echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
				}

				mysqli_close($conex);
			}
		} else { // Invalid submitted values.
			echo "<h1>Error!</h1>";
			echo "<p class='error'>Please enter valid data.</p>";
			echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
		}
		echo "</div>";
		echo "<div id='appointment_confirmed' style='display: none'></div>";
	}

?>

<script type="text/javascript">
	function showHelp() {
	    var x = document.getElementById("help");
	    if (x.style.display === "none") {
	        x.style.display = "block";
	    } else {
	        x.style.display = "none";
	    }
	}
</script>
<script>
	function confirmBooking() {
		//
		var x = document.getElementById("appointment_result");
	    var y = document.getElementById("appointment_confirmed");
	    x.style.display = "none";
		y.style.display = "block";
		//
		var passSID = "<?php echo $passSID ?>";
		var passSFN = "<?php echo $passSFN ?>";
		var passSLN = "<?php echo $passSLN ?>";
		var passSEM = "<?php echo $passSEM ?>";
		var passSCP = "<?php echo $passSCP ?>";
		var passBTN = "<?php echo $passBTN ?>";
		var passCID = "<?php echo $passCID ?>";
		var passLID = "<?php echo $passLID ?>";
		var passRID = "<?php echo $passRID ?>";

		var passSAD = "<?php echo $passSAD ?>";
		var passSST = "<?php echo $passSST ?>";
		var passSZC = "<?php echo $passSZC ?>";
		var passSBD = "<?php echo $passSBD ?>";
		var passSHP = "<?php echo $passSHP ?>";
		var passSGE = "<?php echo $passSGE ?>";
		var passSER = "<?php echo $passSER ?>";
		var passSED = "<?php echo $passSED ?>";
		var passSMA = "<?php echo $passSMA ?>";

	    var htm = $.ajax({
	    type: "POST",
	    url: "confirm_booking.php",
	    data: {getSID: passSID, getSFN: passSFN, getSLN: passSLN, getSEM: passSEM, getSCP: passSCP, getBTN: passBTN, getCID: passCID, getLID: passLID, getRID: passRID, getSAD: passSAD, getSST: passSST, getSZC: passSZC, getSBD: passSBD, getSHP: passSHP, getSGE: passSGE, getSER: passSER, getSED: passSED, getSMA: passSMA},
	    async: false
	    }).responseText;

	    if (htm) {
	        $("#appointment_confirmed").html("<p>" + htm + "</p>");
	        return true;
	    } else {
	        $("#appointment_confirmed").html("<p class='error'>Problem trying to set Appointment!</p>");
	        return false;
	    }
	}
</script>
<script type="text/javascript">
	function mainDisplay(btn) {
	    var x = document.getElementById("appointment_process");
	    var y = document.getElementById("appointment_result");
	    if (x.style.display === "block" && y.style.display === "none") {
	    	x.style.display = "none";
		    y.style.display = "block";
	    } else if (x.style.display === "none" && y.style.display === "block") {
	    	x.style.display = "block";
		    y.style.display = "none";
	    }
	}
</script>
<script type="text/javascript">
	function doStep() {
	    var x = document.getElementById("gatherStudentData");
	    var y = document.getElementById("chooseTime");
	    if (x.style.display === "block" && y.style.display === "none") {
	    	x.style.display = "none";
		    y.style.display = "block";
	    } else if (x.style.display === "none" && y.style.display === "block") {
	    	x.style.display = "block";
		    y.style.display = "none";
	    }
	}
</script>
<div id="appointment_process"<?php if (isset($_POST['student_id'])) echo ' style="display: none;"'; ?>>
	<h1>Appointment Process</h1>
	<form action="appointment.php" method="post">	
		<div id="gatherStudentData" style="display: block; width: 100%;">
			<h2>(1) Student Information | * Required Fields  <button type='button' style='font-size:0.9em; height: 30px;' onclick='doStep()'>NEXT</button></h2>
			<table style="width: 550px">
				<tr>
					<td style="vertical-align: top; padding: 0 0 0 10px; border: 0px;">
						<?php 

							include ('includes/db_config.php'); 
							#LOCATIONS.
							$query = "SELECT id, CONCAT(detail,' ',building_id,room) AS location FROM Locations ORDER BY location";
							$loc_res = mysqli_query($conex, $query);
							if ($loc_res) {
								if (mysqli_num_rows($loc_res) > 0) {
									echo "<p>* Location:";
									echo "<br><select name='location' required='required' style='width: 200px'>";
										while ($row = mysqli_fetch_array($loc_res)) {
											$loc_id = $row['id'];
											$loc_location = $row['location'];
											echo "<option value='$loc_id'>$loc_location</option>\n";
										}							
									echo "</select>";
									echo "</p>";
								} else {
									echo "<p>* Location:";
									echo "<br><select name='location' required='required' style='width: 200px'>";
										echo "<option value='' selected>EMPTY LIST</option>\n";
									echo "</select>";
									echo "</p>";
								}

								mysqli_free_result($loc_res);
							} else {
								echo "<p class='error'>* Location:";
								echo "<br>[Connection Error]";
								echo "</p>";
							}
							
							#REASONS.
							$query = "SELECT id, description FROM Reasons ORDER BY description";
							$rea_res = mysqli_query($conex, $query);
							if ($rea_res) {
								if (mysqli_num_rows($rea_res) > 0) {
									echo "<p>* Reason:";
									echo "<br><select name='reason' required='required' style='width: 200px'>";
										echo "<option value=''>#Select</option>\n";
										while ($row = mysqli_fetch_array($rea_res)) {
											$re_id = $row['id'];
											$re_description = $row['description'];
											echo "<option value='$re_id'>$re_description</option>\n";
										}							
									echo "</select>";
									echo "</p>";
								} else {
									echo "<p>* Reason:";
									echo "<br><select name='reason' required='required' style='width: 200px'>";
										echo "<option value='' selected>EMPTY LIST</option>\n";
									echo "</select>";
									echo "</p>";
								}

								mysqli_free_result($rea_res);
							} else {
								echo "<p class='error'>* Reason:";
								echo "<br>[Connection Error]";
								echo "</p>";
							}

						?>

						<p>* Student ID (no leading zeros):
							<br><input type="text" name="student_id" required="required" value="<?php if (isset($_POST['student_id'])) echo $_POST['student_id']; ?>" style="width: 190px" />
						</p>
						<p>* First Name:
							<br><input type="text" name="first_name" required="required" value="<?php if (isset($_POST['first_name'])) echo $_POST['first_name']; ?>" style="width: 190px" />
						</p>
						<p>* Last Name:
							<br><input type="text" name="last_name" required="required" value="<?php if (isset($_POST['last_name'])) echo $_POST['last_name']; ?>" style="width: 190px" />
						</p>
						<p>* E-mail:
							<br><input type="text" name="email" required="required" value="<?php if (isset($_POST['email'])) echo $_POST['email']; ?>" style="width: 190px" />
						</p>
						<p>Address | *State | *Zipcode:
							<br><input type="text" name="address" value="<?php if (isset($_POST['address'])) echo $_POST['address']; ?>" style="width: 190px" />
							<br><select name='state' required='required' style='width: 140px'>
								<option value=''>#Select</option>
								<option value='FL'>Florida</option>
								<option value='NJ'>New Jersey</option>
								<option value='NY'>New York</option>
								<option value='PA'>Pennsylvania</option>
							</select>
							<input type="text" name="zipcode" required="required" value="<?php if (isset($_POST['zipcode'])) echo $_POST['zipcode']; ?>" style="width: 45px" />
						</p>
					</td>
					<td style="vertical-align: top; text-align: left; border: 0px;">
						<?php

							#MAJOR.
							$query = "SELECT id, name FROM Majors ORDER BY name";
							$major_res = mysqli_query($conex, $query);
							if ($major_res) {
								if (mysqli_num_rows($major_res) > 0) {
									echo "<p>* Major:";
									echo "<br><select name='major' required='required' style='width: 200px'>";
										echo "<option value=''>#Select</option>\n";
										while ($row = mysqli_fetch_array($major_res)) {
											$major_id = $row['id'];
											$major_name = $row['name'];
											echo "<option value='$major_id'>$major_name</option>\n";
										}							
									echo "</select>";
									echo "</p>";
								} else {
									echo "<p>* Major:";
									echo "<br><select name='major' required='required' style='width: 200px'>";
										echo "<option value='' selected>EMPTY LIST</option>\n";
									echo "</select>";
									echo "</p>";
								}

								mysqli_free_result($major_res);
							} else {
								echo "<p class='error'>* Major:";
								echo "<br>[Connection Error]";
								echo "</p>";
							}

							#EDUCATION.
							$query = "SELECT id, description FROM Education";
							$edu_res = mysqli_query($conex, $query);
							if ($edu_res) {
								if (mysqli_num_rows($edu_res) > 0) {
									echo "<p>* Education (year):";
									echo "<br><select name='education' required='required' style='width: 200px'>";
										echo "<option value=''>#Select</option>\n";
										while ($row = mysqli_fetch_array($edu_res)) {
											$edu_id = $row['id'];
											$edu_description = $row['description'];
											echo "<option value='$edu_id'>$edu_description</option>\n";
										}							
									echo "</select>";
									echo "</p>";
								} else {
									echo "<p>* Education (year):";
									echo "<br><select name='education' required='required' style='width: 200px'>";
										echo "<option value='' selected>EMPTY LIST</option>\n";
									echo "</select>";
									echo "</p>";
								}

								mysqli_free_result($edu_res);
							} else {
								echo "<p class='error'>* Education (year):";
								echo "<br>[Connection Error]";
								echo "</p>";
							}

							#ETHNIC-RACIAL.
							$query = "SELECT id, description FROM Ethnic_Racial";
							$er_res = mysqli_query($conex, $query);
							if ($er_res) {
								if (mysqli_num_rows($er_res) > 0) {
									echo "<p>* Race/Ethnicity:";
									echo "<br><select name='ethnic_racial' required='required' style='width: 200px'>";
										echo "<option value=''>#Select</option>\n";
										while ($row = mysqli_fetch_array($er_res)) {
											$er_id = $row['id'];
											$er_description = $row['description'];
											echo "<option value='$er_id'>$er_description</option>\n";
										}							
									echo "</select>";
									echo "</p>";
								} else {
									echo "<p>* Race/Ethnicity:";
									echo "<br><select name='ethnic_racial' required='required' style='width: 200px'>";
										echo "<option value='' selected>EMPTY LIST</option>\n";
									echo "</select>";
									echo "</p>";
								}

								mysqli_free_result($er_res);
							} else {
								echo "<p class='error'>* Race/Ethnicity:";
								echo "<br>[Connection Error]";
								echo "</p>";
							}

						?>
						<p>* Gender:
							<br><select name='gender' required='required' style='width: 200px'>
								<option value=''>#Select</option>
								<option value='Female'>Female</option>
								<option value='Male'>Male</option>
							</select>
						</p>
						<p>Birthdate (mm/dd/yyyy):
							<br><input type="date"  name="birthdate" style="font-size: 1.6em; height: 20px; width: 190px">
						</p>
						<p>Cell-Phone:
							<br><input type="text" name="cell_phone" value="<?php if (isset($_POST['cell_phone'])) echo $_POST['cell_phone']; ?>" style="width: 190px" />
						</p>
						<p>Home-Phone:
							<br><input type="text" name="home_phone" value="<?php if (isset($_POST['home_phone'])) echo $_POST['home_phone']; ?>" style="width: 190px" />
						</p>
					</td>
				</tr>
			</table>
		</div>
		<div id="chooseTime" style="display: none; width: 100%;">
			<h2>(2) Select consultant and book a time! <span class='btntitle' style="font-size: 0.8em">ALERT: If booking does not respond, click "BACK" to check "Required Fields."</span></h2>
			<p>
				<?php 

					#CONSULTANTS.
					$query = "SELECT id, CONCAT(last_name,', ',first_name) AS consultant FROM Consultants ORDER BY consultant";
					$cons_res = mysqli_query($conex, $query);
					if ($cons_res) {
						if (mysqli_num_rows($cons_res) > 0) {
							echo " * Consultant: ";
							echo "<select id='consultant' name='consultant' required='required' style='width: 200px' onchange='return populateBook(this);'>";
								echo "<option value=''>#Select</option>\n";
								while ($row = mysqli_fetch_array($cons_res)) {
									$con_id = $row['id'];
									$con_consultant = $row['consultant'];
									echo "<option value='$con_id'>$con_consultant</option>\n";
								}							
							echo "</select>";
						} else {
							echo " * Consultant: ";
							echo "<select name='consultant' required='required' style='width: 200px'>";
								echo "<option value='' selected>EMPTY LIST</option>\n";
							echo "</select>";
						}

						mysqli_free_result($cons_res);
					} else {
						echo " * Consultant: ";
						echo "[Connection Error]";
					}

					echo "   <button type='button' style='font-size:1.1em; height: 30px;' onclick='doStep()'>BACK</button>";
					
					mysqli_close($conex);

				?>
			</p>
			<div id="populate_book" style="width: 100%; text-align: left; padding: 0 0 0 30px;"><br><br><br><center><div class='no_availability'><div>No Availability</div></div></center></div>
			<script>
				function populateBook(sel) {
					var getConsultantId = sel.value;
				    var htm = $.ajax({
				    type: "POST",
				    url: "populate_book.php",
				    data: "bookConsultantId=" + getConsultantId,
				    async: false
				    }).responseText;

				    if (htm) {
				        $("#populate_book").html("<p>" + htm + "</p>");
				        return true;
				    } else {
				        $("#populate_book").html("<p class='error'>Problem trying to get Consultant Availability Book!</p>");
				        return false;
				    }
				}
			</script>
		</div>
	</form>
</div>

<?php include ('includes/footer.html'); ?>
