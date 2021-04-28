<?php
	include_once 'dbconnect.php'
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>ANPR DATA VISUALISATION</title>
<link rel="stylesheet" href="./style.css">

</head>
<body>
	<div align='center'><h2>Leeds Traffic Data is available to query</h2></div>
	<div align='center'><h4>This data covers the period of 18/06/2016 to 24/06/2016</h4></div>
	<form class="form" action="SelectOptions.php" method="post">
		<p>Please select from the options below</p>
		<div class="inputGroup">
			<input id="datefig" type="checkbox" value="Date", name="options[]"/>
			<label for="datefig">Date</label>
	 	</div>
	 	<div class="inputGroup">
	 		<input id="sitefig" type="checkbox" value="Site", name="options[]"/>
	 		<label for="sitefig">Site</label>
		</div>
		<div class="inputGroup">
  		<input id="typefig" type="checkbox" value="Type", name="options[]"/>
  		<label for="typefig">Type of Vehicle</label>
 		</div>
 		<div class="inputGroup">
 			<input id="makefig" type="checkbox" value="Make", name="options[]"/>
 			<label for="makefig">Make of Vehicle</label>
		</div>
		<div class="inputGroup">
			<input id="bodytypefig" type="checkbox" value="Body_Type", name="options[]"/>
			<label for="bodytypefig">Body Type</label>
		</div>
		<div class="inputGroup">
			<input id="bodytype2fig" type="checkbox" value="Body_Type_Detail", name="options[]"/>
			<label for="bodytype2fig">Detailed Body Type </label>
		</div>
		<button class="buttons" type="submit">Submit</button>

	</form>
</body>


</html>
