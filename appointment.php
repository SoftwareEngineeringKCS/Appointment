<?php 
	
	function sendEmail($from, $to, $confirmation) {
		 $subject = "Appointment Confirmation Code";
		 $header = "From: " . $from;
		 $message = "Dear Student,
		 		 	\nYour appointment confirmation code is " . $confirmation . ". 
		 		 	\nBest regards,
		 		 	\nKean Career Services";
		 if ($from == "") {
		 	mail($to, $subject, $message);
		 } else {
		 	mail($to, $subject, $message, $header);
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

	$page_title = 'Kean Career Services';
	include ('includes/header.html');
	include ('includes/db_config.php');

	// Check for form submission:
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		echo "<div id='appointment_result' style='display: block;'>";
		// Print the results:
		echo "<h1>Appointment Result</h1>";
		// Minimal form validation:
		if (isset($_POST['student_id'], $_POST['first_name'], $_POST['email'])) {

			if ($_POST['location'] == '' || $_POST['consultant'] == '' || $_POST['reason'] == '' || 
				$_POST['student_id'] == '' || $_POST['first_name'] == '' || $_POST['last_name'] == '' || 
				$_POST['email'] == '') {
				echo "<h2>The following fields cannot be empty!</h2>";
				if ($_POST['reason'] == '') echo "<p class='error'>\"Reason\"</p>";
				if ($_POST['student_id'] == '') echo "<p class='error'>\"ID\"</p>";
				if ($_POST['first_name'] == '') echo "<p class='error'>\"First Name\"</p>";
				if ($_POST['last_name'] == '') echo "<p class='error'>\"Last Name\"</p>";
				if ($_POST['email'] == '') echo "<p class='error'>\"E-mail\"</p>";
				if ($_POST['consultant'] == '') echo "<p class='error'>\"Consultant\"</p>";
				if ($_POST['location'] == '') echo "<p class='error'>\"Location\"</p>";
				echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
			} else {
				# Validate Id and Email.
				$query = sprintf("SELECT * FROM Students WHERE id = '%s' AND email = '%s'", $_POST['student_id'], $_POST['email']);
				$result = mysqli_query($conex, $query);
				if ($result) {
					if (mysqli_num_rows($result) > 0) {
						# Update Student.
						$query = sprintf("UPDATE Students SET first_name = '%s', last_name = '%s', cell_phone = '%s' WHERE id = '%s'", $_POST['first_name'], $_POST['last_name'], $_POST['cell_phone'], $_POST['student_id']);
						$result = mysqli_query($conex, $query);
						# Save appointment.
						$pos = strpos($_POST['btnbook'], ",");
						$getTimeId = substr($_POST['btnbook'], 0, $pos);
						$getDateTime = substr($_POST['btnbook'], $pos+1);
						$getCode = createCode($_POST['student_id'], $getDateTime);
						$query = sprintf("INSERT INTO Students_Appointment VALUES(NULL, '%s', '%s', '%s', '%s', '%s', '%s', 0, 0, '', 0, '')", $_POST['student_id'], $_POST['consultant'], $_POST['location'], $_POST['reason'], $getDateTime, $getCode);
						$result = mysqli_query($conex, $query);
						# Update Time Status.
						$query = sprintf("UPDATE Availability_Times SET free = 0 WHERE id = '%s'", $getTimeId);
						$result = mysqli_query($conex, $query);
						# Show results.
						echo "<p>Student ID: " . $_POST['student_id'] . "</p>";
						echo "<p>Name: " . $_POST['last_name'] . ", " . $_POST['first_name'] . "</p>";
						$query = sprintf("SELECT description FROM Reasons WHERE id = '%s'", $_POST['reason']);
						$result = mysqli_query($conex, $query);
						$row = mysqli_fetch_array($result);
						if ($result) {
							echo "<p>Reason: " . $row[0] . "</p>";
						} else {
							echo "<p class='error'>Reason: Error!</p>";
						}
						$query = sprintf("SELECT CONCAT(last_name,', ',first_name) FROM Consultants WHERE id = '%s'", $_POST['consultant']);
						$result = mysqli_query($conex, $query);
						$row = mysqli_fetch_array($result);
						if ($result) {
							echo "<p>Consultant: " . $row[0] . "</p>";
						} else {
							echo "<p class='error'>Consultant: Error!</p>";
						}
						$query = sprintf("SELECT CONCAT(detail,' ',building_id,room) FROM Locations WHERE id = '%s'", $_POST['location']);
						$result = mysqli_query($conex, $query);
						$row = mysqli_fetch_array($result);
						if ($result) {
							echo "<p>Location: " . $row[0] . "</p>";
						} else {
							echo "<p class='error'>Location: Error!</p>";
						}
						# Send confirmation code (bye email).
						$query = sprintf("SELECT email FROM Consultants WHERE id = '%s'", $_POST['consultant']);
						$result = mysqli_query($conex, $query);
						if ($result) {
							if (mysqli_num_rows($result) > 0) {
								$row = mysqli_fetch_array($result);
								sendEmail($row['email'], $_POST['email'], $getCode);
								echo "<p><h2 style='color: #6CBB3C'>A Confirmation Code was sent to your email!</h2></p>";
							} else {
								sendEmail("", $_POST['email'], $getCode);
								echo "<p><h2 style='color: #6CBB3C'>A Confirmation Code was sent to your email!</h2></p>";
							}
						} else {
							echo "<br>Problem trying to send confirmation code.";
							echo "<br>Contact Administrator!";
						}
					} else {
						# Validate Student.
						$query = sprintf("SELECT * FROM Students WHERE id = '%s'", $_POST['student_id']);
						$result = mysqli_query($conex, $query);
						if ($result) {
							if (mysqli_num_rows($result) > 0) {
								echo "<h2>This ID \"" . $_POST['student_id'] .  "\" has been used before!</h2>";
								# Validate email.
								$query = sprintf("SELECT * FROM Students WHERE email = '%s'", $_POST['email']);
								$result = mysqli_query($conex, $query);
								if ($result) {
									if (mysqli_num_rows($result) > 0) {
										echo "<p><u>NEW DATA</u>
												<br>  Name: " . $_POST['last_name'] . ", " . $_POST['first_name'] .
												"<br>  E-mail: " . $_POST['email'] . 
												"<br>  Cell-Phone: " . $_POST['cell_phone'] . 
												"</p><p class='ad'><u>BLOCKING</u>: 
												<br>The new email is used by another student, please click back and use a different email.</p>";
										echo "<p><button type='button' style='height: 30px;' onclick='goBack()'>BACK</button>";
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

										echo "<p><u>NEW DATA</u>
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
										echo "<p><button type='button' style='height: 30px;' onclick='goBack()'>BACK</button>


												<button style='height: 30px;' onclick='confirmBooking();'>CONFIRM</button></p>";
									}
								} else {
									echo "<br>Problem trying to validate email: " . mysqli_error();
									echo "<br>Contact Administrator!";
									echo "<br><a href='appointment.php'>TRY AGAIN</a>";
								}
							} else {
								# Validate email.
								$query = sprintf("SELECT * FROM Students WHERE email = '%s'", $_POST['email']);
								$result = mysqli_query($conex, $query);
								if ($result) {
									if (mysqli_num_rows($result) > 0) {
										echo "<p><u>NEW DATA</u>
												<br>  Name: " . $_POST['last_name'] . ", " . $_POST['first_name'] .
												"<br>  E-mail: " . $_POST['email'] . 
												"<br>  Cell-Phone: " . $_POST['cell_phone'] . 
												"</p><p class='ad'><u>BLOCKING</u>: 
												<br>The new email is used by another student, please click back and use a different email.</p>";
										echo "<p><button type='button' style='height: 30px;' onclick='goBack()'>BACK</button>";
									} else {
										# Save New Student.
										$query = sprintf("INSERT INTO Students VALUES('%s', '%s', '%s', '', '', '', '%s', '', '%s', '', '', '')", $_POST['student_id'], $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['cell_phone']);
										$result = mysqli_query($conex, $query);
										# Save appointment.
										$pos = strpos($_POST['btnbook'], ",");
										$getTimeId = substr($_POST['btnbook'], 0, $pos);
										$getDateTime = substr($_POST['btnbook'], $pos+1);
										$getCode = createCode($_POST['student_id'], $getDateTime);
										$query = sprintf("INSERT INTO Students_Appointment VALUES(NULL, '%s', '%s', '%s', '%s', '%s', '%s', 0, 0, '', 0, '')", $_POST['student_id'], $_POST['consultant'], $_POST['location'], $_POST['reason'], $getDateTime, $getCode);
										$result = mysqli_query($conex, $query);
										# Update Time Status.
										$query = sprintf("UPDATE Availability_Times SET free = 0 WHERE id = '%s'", $getTimeId);
										$result = mysqli_query($conex, $query);
										# Show results.
										echo "<p>Student ID: " . $_POST['student_id'] . "</p>";
										echo "<p>Name: " . $_POST['last_name'] . ", " . $_POST['first_name'] . "</p>";
										$query = sprintf("SELECT description FROM Reasons WHERE id = '%s'", $_POST['reason']);
										$result = mysqli_query($conex, $query);
										$row = mysqli_fetch_array($result);
										if ($result) {
											echo "<p>Reason: " . $row[0] . "</p>";
										} else {
											echo "<p class='error'>Reason: Error!</p>";
										}
										$query = sprintf("SELECT CONCAT(last_name,', ',first_name) FROM Consultants WHERE id = '%s'", $_POST['consultant']);
										$result = mysqli_query($conex, $query);
										$row = mysqli_fetch_array($result);
										if ($result) {
											echo "<p>Consultant: " . $row[0] . "</p>";
										} else {
											echo "<p class='error'>Consultant: Error!</p>";
										}
										$query = sprintf("SELECT CONCAT(detail,' ',building_id,room) FROM Locations WHERE id = '%s'", $_POST['location']);
										$result = mysqli_query($conex, $query);
										$row = mysqli_fetch_array($result);
										if ($result) {
											echo "<p>Location: " . $row[0] . "</p>";
										} else {
											echo "<p class='error'>Location: Error!</p>";
										}
										# Send confirmation code (bye email).
										$query = sprintf("SELECT email FROM Consultants WHERE id = '%s'", $_POST['consultant']);
										$result = mysqli_query($conex, $query);
										if ($result) {
											if (mysqli_num_rows($result) > 0) {
												$row = mysqli_fetch_array($result);
												sendEmail($row['email'], $_POST['email'], $getCode);
												echo "<p><h2 style='color: #6CBB3C'>A Confirmation Code was sent to your email!</h2></p>";
											} else {
												sendEmail("", $_POST['email'], $getCode);
												echo "<p><h2 style='color: #6CBB3C'>A Confirmation Code was sent to your email!</h2></p>";
											}
										} else {
											echo "<br>Problem trying to send confirmation code.";
											echo "<br>Contact Administrator!";
										}
									}
								} else {
									echo "<br>Problem trying to validate email: " . mysqli_error();
									echo "<br>Contact Administrator!";
									echo "<br><a href='appointment.php'>TRY AGAIN</a>";
								}
							}
						} else {
							echo "<br>Problem trying to validate student: " . mysqli_error();
							echo "<br>Contact Administrator!";
							echo "<br><a href='appointment.php'>TRY AGAIN</a>";
						}
					}
				} else {
					echo "<br>Problem trying to validate Student: " . mysqli_error();
					echo "<br>Contact Administrator!";
					echo "<br><a href='appointment.php'>TRY AGAIN</a>";
				}

				mysqli_free_result($result);
				mysqli_close($conex);
			}
		} else { // Invalid submitted values.
			echo '<h1>Error!</h1>
			<p class="error">Please enter valid data.</p>';
		}
		echo "</div>";
		echo "<div id='appointment_confirmed' style='display: none'></div>";
	}

?>
<script>
	/*function testing() {

	}*/
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

	    var htm = $.ajax({
	    type: "POST",
	    url: "confirm_booking.php",
	    data: {getSID: passSID, getSFN: passSFN, getSLN: passSLN, getSEM: passSEM, getSCP: passSCP, getBTN: passBTN, getCID: passCID, getLID: passLID, getRID: passRID},
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
<div id="appointment_process"<?php if (isset($_POST['student_id'])) echo ' style="display: none;"'; ?>>
	<h1>Appointment Process</h1>
	<h2>* Required Fields | Select consultant and book a time of any date!</h2>
	<form onsubmit="return confirm('Confirm Booking');" action="appointment.php" method="post">	
		<table style="width: 100%">
			<tr>
				<td style="vertical-align: top; border: 0px;">
					<div id="appointment_get_info" style="display: block;">
						<?php 

							include ('includes/db_config.php'); 
							#LOCATIONS.
							$query = "SELECT id, CONCAT(detail,' ',building_id,room) AS location FROM Locations ORDER BY location";
							$result = mysqli_query($conex, $query);
							if ($result) {
								if (mysqli_num_rows($result) > 0) {
									echo "<p>* Location:";
									echo "<br><select name='location' required='required' style='width: 200px'>";
										//echo "<option value=''>#Select</option>\n";
										while ($row = mysqli_fetch_array($result)) {
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
							} else {

							}
							
							#CONSULTANTS.
							$query = "SELECT id, CONCAT(last_name,', ',first_name) AS consultant FROM Consultants ORDER BY consultant";
							$result = mysqli_query($conex, $query);
							if ($result) {
								if (mysqli_num_rows($result) > 0) {
									echo "<p>* Consultant:";
									echo "<br><select id='consultant' name='consultant' required='required' style='width: 200px' onchange='return populateBook(this);'>";
										echo "<option value=''>#Select</option>\n";
										while ($row = mysqli_fetch_array($result)) {
											$con_id = $row['id'];
											$con_consultant = $row['consultant'];
											echo "<option value='$con_id'>$con_consultant</option>\n";
										}							
									echo "</select>";
									echo "</p>";
								} else {
									echo "<p>* Consultant:";
									echo "<br><select name='consultant' required='required' style='width: 200px'>";
										echo "<option value='' selected>EMPTY LIST</option>\n";
									echo "</select>";
									echo "</p>";
								}
							} else {

							}

							#REASONS.
							$query = "SELECT id, description FROM Reasons ORDER BY description";
							$result = mysqli_query($conex, $query);
							if ($result) {
								if (mysqli_num_rows($result) > 0) {
									echo "<p>* Reason:";
									echo "<br><select name='reason' required='required' style='width: 200px'>";
										echo "<option value=''>#Select</option>\n";
										while ($row = mysqli_fetch_array($result)) {
											$re_id = $row['id'];
											$re_description = $row['description'];
											echo "<option value='$re_id'>$re_description</option>\n";
										}							
									echo "</select>";
									echo "</p>";
								} else {
									echo "<p>* Reasons:";
									echo "<br><select name='reason' required='required' style='width: 200px'>";
										echo "<option value='' selected>EMPTY LIST</option>\n";
									echo "</select>";
									echo "</p>";
								}
							} else {

							}
							mysqli_free_result($result);
							mysqli_close($conex);

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
						<p>Cell-Phone:
							<br><input type="text" name="cell_phone" value="<?php if (isset($_POST['cell_phone'])) echo $_POST['cell_phone']; ?>" style="width: 190px" />
						</p>
					</div>
				</td>
				<td style="vertical-align: top; text-align: center; border: 0px;">
					<div id="populate_book" style="width: 500px;"></div>
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
						<script type="text/javascript">
							function goBack() {
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
				</td>
			</tr>
		</table>
	</form>
</div>

<?php include ('includes/footer.html'); ?>
