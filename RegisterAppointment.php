<?php

include "dbconfig.php";

$sfname = $_POST["sfname"];
$slname = $_POST["slname"];
$sID = $_POST["sID"];
$sEM = $_POST["sEM"];
$reason = $_POST["reason"];
$counselor = $_POST["counselor"];
$location = $_POST["location"];
$date = date("Y/m/d");

$query1 = "INSERT into seds2017db.Students VALUES (\"$sID\",\"$sfname\",\"$slname\", null, null, null, \"$sEM\", null, null, null, \"teststudent\", \"jorgetriestohard\" )"

$result1 = mysqli_query($conn, $query1);

if($result)
{

}
else
{
	echo "<BR><br>Error" . mysqli_error($conn);
}


$query = "INSERT into seds2017db.Students_Appointments VALUES (id, \"$sID\", \"$counselor\" , \"$location\" , \"$reason\", \"$date\" , 1 , 0 , 0 , null , null )";

				$result = mysqli_query($conn, $query);


				if($result)
				{
					echo "<BR>Your appointment has been set. Please check your email for a confirmation code<BR>";
				}
				else
				{
					echo "<BR><br>Error" . mysqli_error($conn);
				}

?>
