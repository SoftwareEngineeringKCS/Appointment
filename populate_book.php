<?php #JORGE ESPADA
	
	date_default_timezone_set('America/New_York');
	$system_datetime = date("Y-m-d H:i");

	$book_show_error = FALSE; // Maintenance.
	include ('includes/db_config.php');
	$consultant_id = $_REQUEST['bookConsultantId'];

	$query = sprintf("SELECT DISTINCT D.set_date FROM Availability_Setting S 
						INNER JOIN Availability_Dates D ON D.aset_id = S.id
						WHERE S.consultant_id = '%s' AND S.cancelled = 0 AND S.closed = 0 AND S.finished = 0 ORDER BY D.set_date", $consultant_id);
    $book_res1 = mysqli_query($conex, $query);
    if ($book_res1) {
    	if (mysqli_num_rows($book_res1) > 0) { 
			$i = 1;
			while ($row = mysqli_fetch_array($book_res1)) {
				
				include ('includes/db_config.php');
				$set_date = $row['set_date'];
				$unixTimestamp = strtotime($set_date);
				$dayOfWeek = date("l", $unixTimestamp);
						
				$que = sprintf("CALL usp_Populate_Book('%s','%s')", $consultant_id, $set_date);
				$res = mysqli_query($conex, $que);
				if ($res) {
					if (mysqli_num_rows($res) > 0) {
						echo "<div style='display: inline-block; width: 100px; vertical-align: top; text-align: center; margin-right: 10px; margin-top: 10px'>";
						echo "<b>" . $dayOfWeek . "</b>";
						echo "<button type='button' class='btntitle'>" . date_format(date_create($set_date), "m/d/Y") . "</button><br>";
						while ($ro = mysqli_fetch_array($res)) {
							$set_time = date("g:i A", strtotime($ro['set_time']));
							$id_date_time = $ro['id'] . "," . $ro['set_date'] . " " . $ro['set_time'];
							if ($ro['free'] == TRUE && date_create($set_date . " " . $set_time) <= date_create($system_datetime)) {
								echo "<button type='button' name='btnbook' value='" . $id_date_time . "' class='btnbook_taken'>" . $set_time . "</button><br>";
							} else if ($ro['free'] == TRUE && date_create($set_date . " " . $set_time) > date_create($system_datetime)) {
								echo "<button type='submit' name='btnbook' value='" . $id_date_time . "' class='btnbook'>" . $set_time . "</button><br>";
							} else {
								echo "<button type='button' name='btnbook' value='" . $id_date_time . "' class='btnbook_taken'>" . $set_time . "</button><br>";
							}
						}
						echo "</div>";	
					} else {
						echo "<div style='display: inline-block; width: 100px; vertical-align: top; text-align: center; margin-right: 10px; margin-top: 10px'>";
						echo "NO TIME";
						echo "</div>";
					}
					
					mysqli_free_result($res);
				} else {
					echo "<div style='display: inline-block; width: 100px; vertical-align: top; text-align: center; margin-right: 10px; margin-top: 10px'>";
					echo "ERROR";
					echo "</div>";
				}

				$i += 1;
			}
			echo "<br style='clear: left;' />";
    	} else {
    		echo "<br><br><center><div class='no_availability'><div>No Availability</div></div></center>";
    	}
		
		mysqli_free_result($book_res1);
    } else {
		echo "<p class='error'>Loading availability book... Failed! [Connection Error]";
		if ($book_show_error) {
			echo "<br>[<i>" . mysqli_error() . "</i>]";
		}
		echo "<br>Contact Administrator!</p>";
		echo "<p><a href='appointment.php'>TRY AGAIN</a></p>";
    }

    mysqli_close($conex);

?>
