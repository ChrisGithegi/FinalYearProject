<?php
	include_once 'dbconnect.php'
?>

<!DOCTYPE html>
<html>
<head>
	<title>ANPR DATA VISUALISATION</title>
</head>
<body>
	<p3>Please select the following headings you want to analyse</p3><br>
	<form action="action_handler.php" method="post">
		<p>Please select from the options below</p>
		<input type="checkbox" value="Date", name="options[]"> Date<br/>
		<input type="checkbox" value="Site", name="options[]"> Site<br/>
		<input type="checkbox" value="Type", name="options[]"> Type of Vehicle<br/>
	  <input type="checkbox" value="Make", name="options[]"> Make of Vehicle<br/>
		<input type="checkbox" value="Body_Type", name="options[]"> Body Type<br/>
		<p><input type="submit" value="Submit"></p>
	</form>
</body>
</html>
