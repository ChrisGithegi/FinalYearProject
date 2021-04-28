<?php
	include_once 'dbconnect.php'
?>

<!DOCTYPE html>
<html>
<head>
	<title>ANPR DATA VISUALISATION</title>
	<link rel="stylesheet" href="./style.css">
</head>
<body>
	<?php
		$dropC = "DROP TABLE IF EXISTS CAZC;";
		$dropD = "DROP TABLE IF EXISTS CAZD;";
		mysqli_query($conn, $dropC);
		mysqli_query($conn, $dropD);
		$SQL = "Select Distinct UniqueID, Date, Body_Type, Mass from results where (tier not in ('Euro 4', 'Euro 5', 'Euro 6') and Fuel_Type in ('Petrol', 'Gas') and Site not in ('1','2')";
		mysqli_query($conn, "CREATE TABLE IF NOT EXISTS CAZC $SQL and Body_Type not in ('Cars'));");
		mysqli_query($conn, "INSERT INTO CAZC $SQL and (Body_Type in ('Cars') and Type in ('Private Vehicle')));");
		mysqli_query($conn, "CREATE TABLE IF NOT EXISTS CAZD $SQL);");
		$SQL = "Select Distinct UniqueID, Date, Body_Type, Mass from results where (tier not in ('Euro 6') and Fuel_Type in ('Diesel') and Site not in ('1','2')";
		mysqli_query($conn, "INSERT INTO CAZC $SQL and Body_Type not in ('Cars'));");
		mysqli_query($conn, "INSERT INTO CAZC $SQL and (Body_Type in ('Cars') and Type in ('Private Vehicle')));");
		mysqli_query($conn, "INSERT INTO CAZD $SQL);");
		$SQL = "alter table CAZD add charge int Default 9;";
		mysqli_query($conn, $SQL);
		$SQL = "update CAZD set charge = 50 where (Body_Type in ('Goods', 'Others') and Mass > 3500);";
		mysqli_query($conn, $SQL);
		$SQL = "update CAZD set charge = 50 where (Body_Type in ('Buses & Coaches'));";
		mysqli_query($conn, $SQL);
		$SQL = "alter table CAZC add charge int Default 9;";
		mysqli_query($conn, $SQL);
		$SQL = "update CAZC set charge = 50 where (Body_Type in ('Goods', 'Others') and Mass > 3500);";
		mysqli_query($conn, $SQL);
		$SQL = "update CAZC set charge = 50 where (Body_Type in ('Buses & Coaches'));";
		mysqli_query($conn, $SQL);
		$sqlCAZC = "select Date, sum(charge) from CAZC group by date;";
		$sqlCAZD = "select Date, sum(charge) from CAZD group by date;";
		$resultCAZC = mysqli_query($conn, $sqlCAZC);
		$resultCAZD = mysqli_query($conn, $sqlCAZD);
		$chartCAZC = "";
		while($row = mysqli_fetch_assoc($resultCAZC)) {
			$chartCAZC .= "['";
			foreach ($row as $field => $new_val) {
				if ($field == "sum(charge)") {
					$chartCAZC .= "',$new_val";
				}
				else {
					$chartCAZC .= "$new_val";
				}
			}
			$chartCAZC .= "],";
		}
		$chartCAZC = rtrim($chartCAZC, ", ");
		#echo "$chartCAZC";
		$chartCAZChead = "['Date', 'Total Revenue (£)'],";

		$chartCAZD = "";
		while($row = mysqli_fetch_assoc($resultCAZD)) {
			$chartCAZD .= "['";
			foreach ($row as $field => $new_val) {
				if ($field == "sum(charge)") {
					$chartCAZD .= "',$new_val";
				}
				else {
					$chartCAZD .= "$new_val";
				}
			}
			$chartCAZD .= "],";
		}
		$chartCAZD = rtrim($chartCAZD, ", ");
		#echo "$chartCAZD";
		$chartCAZDhead = "['Date', 'Total Revenue (£)'],";
		$script_normal = "
		<div align='center'><h2>Simulating Clean Air Zones</h2></div>
		<div align='center'><p2>Clean Air Zones already exist in London and Bath & are currently being implemented in Birmingham<br/><br/></p2></div>
		<div align='center'><p2>The values in the charts below represent charges occuring 24 hours a day, 7 days a week with each unique vehicle being charged once<br/><br/><br/></p2></div>
		<div align='center'><p2>Class C: The following types of vehicle are charged: Buses, coaches, taxis, private hire vehicles, heavy goods vehicles, vans, minibuses<br/><br/></p2></div>
		<div align='center'><p2>Class D: The following types of vehicle are charged: Buses, coaches, taxis, private hire vehicles, heavy goods vehicles, vans, minibuses, cars, the local authority has the option to include motorcycles<br/><br/></p2></div>
		<script src='https://www.gstatic.com/charts/loader.js'></script>
		<table class='columns' align='center'>
				<tr>
					<td><div id='visualization' style='border: 1px solid #ccc'></div></td>
				</tr>
				</table>
		<div align='center'><input class='buttons' type='button' id='b1' onclick='doOnClick()'></></div>


    <script type='text/javascript'>
		google.charts.load('current', {
		packages: ['corechart', 'controls']
		}).then(function () {
		// Chart Data
		var rowData1 = [$chartCAZChead $chartCAZC];
		var rowData2 = [$chartCAZDhead $chartCAZD];

		var data = [];
		data[0] = google.visualization.arrayToDataTable(rowData1);
		data[1] = google.visualization.arrayToDataTable(rowData2);

		var current = 0;
		// Create and draw the visualization.
		var chart = new google.visualization.BarChart(document.getElementById('visualization'));
		var button = document.getElementById('b1');
		function drawChart() {
			// Disabling the button while the chart is drawing.
			button.disabled = true;
			google.visualization.events.addListener(chart, 'ready', function() {
				button.disabled = false;
				button.value = 'Switch to Clean Air Zone ' + (current ? 'Class C' : 'Class D');
			});
			var options = {
				width:1200,
				height:500,
				vAxis: {title: 'Date'},
      	hAxis: {title: 'Total Revenue  (£)',minValue: 0},
				animation:{
        duration: 1000,
        easing: 'out'},
			};
			options['title'] = (current ? 'CAZD' : 'CAZC') + ' Revenue';

			chart.draw(data[current], options);
		}
		drawChart();

		doOnClick = function() {
			current = 1 - current;
			drawChart();
		}
		});
    </script>";
				echo "$script_normal";
				echo "";
				echo "";
				echo "<br/><br/>";




				$sqlCAZC = "select Date, count(charge) from CAZC group by date;";
				$sqlCAZD = "select Date, count(charge) from CAZD group by date;";
				$resultCAZC = mysqli_query($conn, $sqlCAZC);
				$resultCAZD = mysqli_query($conn, $sqlCAZD);
				$chartCAZC = "";
				while($row = mysqli_fetch_assoc($resultCAZC)) {
					$chartCAZC .= "['";
					foreach ($row as $field => $new_val) {
						if ($field == "count(charge)") {
							$chartCAZC .= "',$new_val";
						}
						else {
							$chartCAZC .= "$new_val";
						}
					}
					$chartCAZC .= "],";
				}
				$chartCAZC = rtrim($chartCAZC, ", ");
				#echo "$chartCAZC";
				$chartCAZChead = "['Date', 'Total Number of Charges'],";

				$chartCAZD = "";
				while($row = mysqli_fetch_assoc($resultCAZD)) {
					$chartCAZD .= "['";
					foreach ($row as $field => $new_val) {
						if ($field == "count(charge)") {
							$chartCAZD .= "',$new_val";
						}
						else {
							$chartCAZD .= "$new_val";
						}
					}
					$chartCAZD .= "],";
				}
				$chartCAZD = rtrim($chartCAZD, ", ");
				#echo "$chartCAZD";
				$chartCAZDhead = "['Date', 'Total Number of Charges'],";
				$script_normal = "<script src='https://www.gstatic.com/charts/loader.js'></script>
				<table class='columns' align='center'>
						<tr>
							<td><div id='charge_count' style='border: 1px solid #ccc'></div></td>
						</tr>
						</table>
				<div align='center'><input class='buttons 'type='button' id='b2' onclick='doOnClick2()'></></div>

		    <script type='text/javascript'>
				google.charts.load('current', {
				packages: ['corechart', 'controls']
				}).then(function () {
				// Chart Data
				var rowData1 = [$chartCAZChead $chartCAZC];
				var rowData2 = [$chartCAZDhead $chartCAZD];

				var data = [];
				data[0] = google.visualization.arrayToDataTable(rowData1);
				data[1] = google.visualization.arrayToDataTable(rowData2);

				var current = 0;
				// Create and draw the visualization.
				var chart = new google.visualization.BarChart(document.getElementById('charge_count'));
				var button = document.getElementById('b2');
				function drawChart() {
					// Disabling the button while the chart is drawing.
					button.disabled = true;
					google.visualization.events.addListener(chart, 'ready', function() {
						button.disabled = false;
						button.value = 'Switch to Clean Air Zone ' + (current ? 'Class C' : 'Class D');
					});
					var options = {
						width:1200,
						height:500,
						vAxis: {title: 'Date'},
		      	hAxis: {title: 'Total number of Charges',minValue: 0},
						animation:{
		        duration: 1000,
		        easing: 'out'},
					};
					options['title'] = (current ? 'CAZD' : 'CAZC') + ' Total number of Charges';

					chart.draw(data[current], options);
				}
				drawChart();

				doOnClick2 = function() {
					current = 1 - current;
					drawChart();
				}
				});
		    </script>";
						echo "$script_normal";
						echo "";
						echo "";
						echo "<br/><br/>";





	?>



  <p>Click Below to go back</p>
  <form action="HomePage.php" method="post">
    <p><input type="submit" class='buttons'value="Go Back"></p>
  </form>
</body>
</html>
