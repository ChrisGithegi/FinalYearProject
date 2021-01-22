<?php
	include_once 'dbconnect.php'
?>

<!DOCTYPE html>
<html>
<head>
	<title>ANPR DATA VISUALISATION</title>
</head>
<body>
  <?php
		if (isset($_POST['indiv_vals'])) {
			$columns = $_POST['indiv_vals'];
			echo "A Visualisation will be produced below <br/>";
			$new_sql = "";
			$x = 0;
			$stack_col = array();
			foreach ($columns as $key => $value) {
				$sql="SELECT column_name from information_schema.columns WHERE table_name = 'traffic' and table_schema = 'ANPR' and column_name = '$value'";
				$result = mysqli_query($conn, $sql);
				$resultCheck = mysqli_num_rows($result);

				if (($resultCheck > 0 && $x == 0)) {
					if (($value == "Date") || ($value == "Site") || ($value == "Make") || ($value == "Type") || ($value == "Body_Type") || ($value == "Body_Type_Detail")){
						$new_sql .= "($value in (";
						$place = $value;
						array_push($stack_col, "$value");
					}
				}
				elseif (($resultCheck > 0 && $x > 0)) {
					if (($value == "Date") || ($value == "Site") || ($value == "Make") || ($value == "Type") || ($value == "Body_Type") || ($value == "Body_Type_Detail")){
						$new_sql = rtrim($new_sql, ", ");
						$new_sql .= ")) and ($value in (";
						$place = $value;
						array_push($stack_col, "$value");
					}
				}
				elseif (($place == "Date") || ($place == "Site") || ($place == "Make") || ($place == "Type") || ($place == "Body_Type")|| ($value == "Body_Type_Detail")) {
					$new_sql .= "'$value',";
				}
				else {
					echo "wrong";
				}
				$x++;
			}
			$new_sql = rtrim($new_sql, ", ");
			$new_sql .= "))";
			$sqlcount = "SELECT";
			foreach ($stack_col as $key => $value) {
				$sqlcount.= " $value,";
			}
			$sqlcount .= " count(*) as 'Total Count', sum(Mass) as 'Vehicle Mass', sum(co2) as 'Vehicle Emissions' FROM traffic where ";
			$sql = "SELECT * FROM traffic where $new_sql;";
			$sqlcount .= $new_sql;

			$sqlcount .= " GROUP BY";
			foreach ($stack_col as $key => $value) {
				$sqlcount .= " $value,";
			}

			$sqlcount = rtrim($sqlcount, ", ");
			$sqlcount .= ";";

			$result = mysqli_query($conn, $sqlcount);
			$resultCheck = mysqli_num_rows($result);

			$chart_val = "";
			if ($resultCheck > 0) {
				while($row = mysqli_fetch_assoc($result)) {
					$chart_val .= "['";
					foreach ($row as $field => $new_val) {
						if ($field == "Total Count") {
							$chart_val .= "',$new_val";
						}
						elseif ($field == "Vehicle Mass") {
							$chart_val .= ",$new_val";
						}
						elseif ($field == "Vehicle Emissions") {
							$chart_val .= ",$new_val";
						}
						else{
							$chart_val .= "$new_val ";
						}

					}
					$chart_val = rtrim($chart_val, ", ");
					$chart_val .= "],";

				}
			}
			$chart_val = rtrim($chart_val, ", ");
			$chart_head = "['";
			foreach ($stack_col as $key => $value) {
				$chart_head .= "$value & ";
			}
			$chart_head = rtrim($chart_head, "&  ");
			$chart_head .= "', 'Total Vehicle Count', 'Total Vehicle Mass', 'Total Vehicle Emissions'],";
			$script_normal = "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
					<script type='text/javascript'>
						google.charts.load('current', {'packages':['bar']});
						google.charts.setOnLoadCallback(drawChart);

						function drawChart() {
							var data = google.visualization.arrayToDataTable([
								$chart_head $chart_val
							]);
							var options = {
								chart: {
									title: 'Traffic Breakdown by Selection',
									subtitle: 'Breakdown of Total Mass, Total Vehicle Count & Total Vehicle Emissions',
								},
								series: {
									0: { axis: 'Total Vehicle Count' }, // Bind series 0 to an axis named 'Total Count'.
									1: { axis: 'Total Vehicle Mass' }, // Bind series 1 to an axis named 'Average Vehicle Mass'.
									2: { axis: 'Total Vehicle Emissions' } // Bind series 1 to an axis named 'Average Vehicle Emissions'.
								},
								axes: {
									y: {
										'Total Vehicle Count': {label: 'Total Vehicle Count'}, // Bottom x-axis.
										'Total Vehicle Mass': {side: 'top', label: 'Total Vehicle Mass (kg)'}, // Top x-axis.
										'Total Vehicle Emissions': {label: 'Total Vehicle Emissions (g/km)'} // Bottom x-axis.
									}
								}
							};
							var chart = new google.charts.Bar(document.getElementById('barchart_material'));
							chart.draw(data, google.charts.Bar.convertOptions(options));
						}
					</script>";

			echo "$script_normal";
			echo "<div id='barchart_material' style='width: 1500px; height: 700px;'></div>";
			echo "<div> <form action='Stats.php' method='post'> <button style='margin: 1em 1em 1em 2em'>
								Simulate Ultra Low Emission Zone (ULEZ)
							</button></form>";
			echo "</div>";
			echo "<br/><br/>";

			$hourlysql = "SELECT extract(hour from Time) as Hour, count(*) as 'Total Count', sum(Mass) as 'Vehicle Mass', sum(co2) as 'Vehicle Emissions' FROM traffic where $new_sql GROUP BY extract(hour from Time) order by extract(hour from Time) ASC;";
			$result = mysqli_query($conn, $hourlysql);
			$resultCheck = mysqli_num_rows($result);

			$chart_val = "";
			if ($resultCheck > 0) {
				while($row = mysqli_fetch_assoc($result)) {
					$chart_val .= "['";
					foreach ($row as $field => $new_val) {
						if ($field == "Total Count") {
							$chart_val .= "',$new_val";
						}
						elseif ($field == "Vehicle Mass") {
							$chart_val .= ",$new_val";
						}
						elseif ($field == "Vehicle Emissions") {
							$chart_val .= ",$new_val";
						}
						else{
							$chart_val .= "$new_val:00 to $new_val:59";
						}

					}
					$chart_val = rtrim($chart_val, ", ");
					$chart_val .= "],";

				}
			}
			$chart_val = rtrim($chart_val, ", ");
			$chart_head = "['Hour of Day', 'Total Vehicle Count', 'Total Vehicle Mass', 'Total Vehicle Emissions'],";

			$script_hourly = "<br/><script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
					<script type='text/javascript'>
						google.charts.load('current', {'packages':['bar']});
						google.charts.setOnLoadCallback(drawHourChart);

						function drawHourChart() {
							var data = google.visualization.arrayToDataTable([
								$chart_head $chart_val
							]);
							var options = {
								chart: {
									title: 'Traffic Breakdown by Selection per Hour',
									subtitle: 'Breakdown of Total Mass, Total Vehicle Count & Total Vehicle Emissions on an Hourly basis',
								},
								bars: 'horizontal', // Required for Material Bar Charts.
								series: {
									0: { axis: 'Total Vehicle Count' }, // Bind series 0 to an axis named 'Total Count'.
									1: { axis: 'Total Vehicle Mass' }, // Bind series 1 to an axis named 'Average Vehicle Mass'.
									2: { axis: 'Total Vehicle Emissions' } // Bind series 1 to an axis named 'Average Vehicle Emissions'.
								},
								axes: {
									x: {
										'Total Vehicle Count': {label: 'Total Vehicle Count'}, // Bottom x-axis.
										'Total Vehicle Mass': {side: 'top', label: 'Total Vehicle Mass (kg)'}, // Top x-axis.
										'Total Vehicle Emissions': {label: 'Total Vehicle Emissions (g/km)'} // Bottom x-axis.
									},
									y: {
										'Body_type': {label: 'Hour of Day'}
									}
								}
							};
							var chart = new google.charts.Bar(document.getElementById('barchart_hourly'));
							chart.draw(data, google.charts.Bar.convertOptions(options));
						}
					</script>";

			echo "$script_hourly";
			echo "<div id='barchart_hourly' style='width: 1500px; height: 900px;'></div>";
			echo "<br/><br/>";
			$drop = "DROP TABLE IF EXISTS results;";
			mysqli_query($conn,$drop);
			mysqli_query($conn, "CREATE TABLE IF NOT EXISTS results $sql");

		}
		else{
			echo "You must select an option.";
		}
	?>
	<p>Click Below to go back</p>
	<form action="index.php" method="post">
		<p><input type="submit" value="Go Back"></p>
	</form>
</body>
</html>
