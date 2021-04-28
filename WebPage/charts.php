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
		if (isset($_POST['indiv_vals'])) {
			$columns = $_POST['indiv_vals'];
			$new_sql = "";
			$x = 0;
			$stack_col = array();
			foreach ($columns as $key => $value) {
				$sql="SELECT column_name from information_schema.columns WHERE table_name =
				'traffic' and table_schema = 'ANPR' and column_name = '$value'";
				$result = mysqli_query($conn, $sql);
				$resultCheck = mysqli_num_rows($result);

				if (($resultCheck > 0 && $x == 0)) {
					if (($value == "Date") || ($value == "Site") || ($value == "FuelType")
					|| ($value == "Type") || ($value == "Body_Type") || ($value == "Body_Type_Detail") || ($value == "Make")) {
						$new_sql .= "($value in (";
						$place = $value;
						array_push($stack_col, "$value");
					}
				}
				elseif (($resultCheck > 0 && $x > 0)) {
					if (($value == "Date") || ($value == "Site") || ($value == "FuelType")
					|| ($value == "Type") || ($value == "Body_Type") || ($value == "Body_Type_Detail") || ($value == "Make")){
						$new_sql = rtrim($new_sql, ", ");
						$new_sql .= ")) and ($value in (";
						$place = $value;
						array_push($stack_col, "$value");
					}
				}
				else {
					$new_sql .= "'$value',";
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

			$chart_count = "";
			$chart_mass = "";
			$chart_emissions = "";
			$chart_val = "";

			if ($resultCheck > 0) {
				while($row = mysqli_fetch_assoc($result)) {
					$chart_count .= "['";
					$chart_mass .= "['";
					$chart_emissions .= "['";
					$chart_val .= "['";
					foreach ($row as $field => $new_val) {
						if ($field == "Total Count") {
							$chart_count .= "',$new_val";
							$chart_val .= "',$new_val";
						}
						elseif ($field == "Vehicle Mass") {
							$chart_mass .= "',$new_val";
							$chart_val .= ",$new_val";

						}
						elseif ($field == "Vehicle Emissions") {
							$chart_emissions .= "',$new_val";
							$chart_val .= ",$new_val";

						}
						else{
							$chart_count .= "$new_val ";
							$chart_mass .= "$new_val ";
							$chart_emissions .= "$new_val ";
							$chart_val .= "$new_val ";
						}

					}
					$chart_count = rtrim($chart_count, ", ");
					$chart_count .= "],";
					$chart_mass = rtrim($chart_mass, ", ");
					$chart_mass .= "],";
					$chart_emissions = rtrim($chart_emissions, ", ");
					$chart_emissions .= "],";
					$chart_val = rtrim($chart_val, ", ");
					$chart_val .= "],";

				}
			}
			$chart_count = rtrim($chart_count, ", ");
			$chart_mass = rtrim($chart_mass, ", ");
			$chart_emissions = rtrim($chart_emissions, ", ");
			$chart_val = rtrim($chart_val, ", ");



			$chart_head ="";
			foreach ($stack_col as $key => $value) {
				$chart_head .= "$value & ";
			}
			$chart_head = rtrim($chart_head, "&  ");

			echo "<p2>Below are visualisations that support the data found in your request: </p2>";
			$script_normal = "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
					<script type='text/javascript'>
						google.charts.load('current', {'packages':['table','corechart']});
						google.charts.setOnLoadCallback(drawChart_count);
						google.charts.setOnLoadCallback(drawChart_mass);
						google.charts.setOnLoadCallback(drawChart_emissions);

						function drawChart_count() {
							var data = new google.visualization.DataTable();
			        data.addColumn('string', '$chart_head');
			        data.addColumn('number', 'Vehicle Count');
			        data.addRows([
			          $chart_count
			        ]);

							var barchart_options = {title:'Barchart: Total Count of Vehicles',
			                       width:700,
			                       height:400,
			                       legend: 'none',
													 	 hAxis: {minValue: 0}	};
			        var barchart = new google.visualization.BarChart(document.getElementById('barchart_count_div'));
			        barchart.draw(data, barchart_options);
						}

						function drawChart_mass() {
							var data = new google.visualization.DataTable();
			        data.addColumn('string', '$chart_head');
			        data.addColumn('number', 'Vehicle Count');
			        data.addRows([
			          $chart_mass
			        ]);

							var barchart_options = {title:'Barchart: Total Mass of Vehicles (kg)',
			                       width:700,
			                       height:400,
			                       legend: 'none',
													 	 hAxis: {minValue: 0}	};
			        var barchart = new google.visualization.BarChart(document.getElementById('barchart_mass_div'));
			        barchart.draw(data, barchart_options);
						}

						function drawChart_emissions() {
							var data = new google.visualization.DataTable();
			        data.addColumn('string', '$chart_head');
			        data.addColumn('number', 'Vehicle Count');
			        data.addRows([
			          $chart_emissions
			        ]);

							var barchart_options = {title:'Barchart: Total CO2 Emissions of Vehicles (g/km)',
			                       width:700,
			                       height:400,
			                       legend: 'none',
													 	 hAxis: {minValue: 0}	};
			        var barchart = new google.visualization.BarChart(document.getElementById('barchart_emissions_div'));
			        barchart.draw(data, barchart_options);
						}
					</script>";

			echo "$script_normal";



			echo "<br/><script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
		    <script type='text/javascript'>
		      google.charts.load('current', {'packages':['table','corechart']});
		      google.charts.setOnLoadCallback(drawTable);

		      function drawTable() {
		        var data = new google.visualization.DataTable();
		        data.addColumn('string', '$chart_head');
		        data.addColumn('number', 'Total Vehicle Count');
		        data.addColumn('number', 'Total Vehicle Mass (kg)');
						data.addColumn('number', 'Total Vehicle Emissions (g/km)');
		        data.addRows([
		          $chart_val
		        ]);

		        var table = new google.visualization.Table(document.getElementById('table_div'));

		        table.draw(data, {showRowNumber: false, width: '100%', height: '100%'});
		      }
		    </script>";
			echo "<br/><br/>";

			$hourlysql = "SELECT extract(hour from Time) as Hour, count(*) as 'Total Count', sum(Mass) as 'Vehicle Mass', sum(co2) as 'Vehicle Emissions' FROM traffic where $new_sql GROUP BY extract(hour from Time) order by extract(hour from Time) ASC;";
			$result = mysqli_query($conn, $hourlysql);
			$resultCheck = mysqli_num_rows($result);

			$chart_count = "";
			$chart_mass = "";
			$chart_emissions = "";
			$chart_val = "";

			if ($resultCheck > 0) {
				while($row = mysqli_fetch_assoc($result)) {
					$chart_count .= "['";
					$chart_mass .= "['";
					$chart_emissions .= "['";
					$chart_val .= "['";
					foreach ($row as $field => $new_val) {
						if ($field == "Total Count") {
							$chart_count .= ",$new_val";
							$chart_val .= "',$new_val";
						}
						elseif ($field == "Vehicle Mass") {
							$chart_mass .= ",$new_val";
							$chart_val .= ",$new_val";

						}
						elseif ($field == "Vehicle Emissions") {
							$chart_emissions .= ",$new_val";
							$chart_val .= ",$new_val";

						}
						else{
							$chart_count .= "$new_val:00 to $new_val:59'";
							$chart_mass .= "$new_val:00 to $new_val:59'";
							$chart_emissions .= "$new_val:00 to $new_val:59'";
							$chart_val .= "$new_val:00 to $new_val:59";
						}

					}
					$chart_count = rtrim($chart_count, ", ");
					$chart_count .= "],";
					$chart_mass = rtrim($chart_mass, ", ");
					$chart_mass .= "],";
					$chart_emissions = rtrim($chart_emissions, ", ");
					$chart_emissions .= "],";
					$chart_val = rtrim($chart_val, ", ");
					$chart_val .= "],";
				}
			}
			$chart_count = rtrim($chart_count, ", ");
			$chart_mass = rtrim($chart_mass, ", ");
			$chart_emissions = rtrim($chart_emissions, ", ");
			$chart_val = rtrim($chart_val, ", ");


			echo "<br/><script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
					<script type='text/javascript'>
						google.charts.load('current', {'packages':['table','corechart']});
						google.charts.setOnLoadCallback(drawChart_count);
						google.charts.setOnLoadCallback(drawChart_mass);
						google.charts.setOnLoadCallback(drawChart_emissions);

						function drawChart_count() {
							var data = new google.visualization.DataTable();
							data.addColumn('string', '$chart_head');
							data.addColumn('number', 'Vehicle Count');
							data.addRows([
								$chart_count
							]);

							var barchart_options = {title:'Barchart: Total Car Count',
														 width:700,
														 height:400,
														 legend: 'none',
													 	 hAxis: {minValue: 0}	};
							var barchart = new google.visualization.BarChart(document.getElementById('barchart_count_hourly'));
							barchart.draw(data, barchart_options);
						}

						function drawChart_mass() {
							var data = new google.visualization.DataTable();
							data.addColumn('string', '$chart_head');
							data.addColumn('number', 'Vehicle Count');
							data.addRows([
								$chart_mass
							]);

							var barchart_options = {title:'Barchart: Total Vehicle Mass (kg)',
														 width:700,
														 height:400,
														 legend: 'none',
													 	 hAxis: {minValue: 0}	};
							var barchart = new google.visualization.BarChart(document.getElementById('barchart_mass_hourly'));
							barchart.draw(data, barchart_options);
						}

						function drawChart_emissions() {
							var data = new google.visualization.DataTable();
							data.addColumn('string', '$chart_head');
							data.addColumn('number', 'Vehicle Count');
							data.addRows([
								$chart_emissions
							]);

							var barchart_options = {title:'Barchart: Total Vehicle CO2 Emissions (g/km)',
														 width:700,
														 height:400,
														 legend: 'none',
													 	 hAxis: {minValue: 0}	};
							var barchart = new google.visualization.BarChart(document.getElementById('barchart_emissions_hourly'));
							barchart.draw(data, barchart_options);
						}
					</script>";

			echo "<div align='center'><h3>Vehicle counts</h3></div>";
			echo "<table class='columns' align='center'>
				      <tr>
								<td><div id='barchart_count_div' style='border: 1px solid #ccc'></div></td>
				        <td><div id='barchart_count_hourly' style='border: 1px solid #ccc'></div></td>

				      </tr>
				    </table>";
			echo "<div align='center'><h3>Co2 Emission Statistics</h3></div>";
			echo "<table class='columns' align='center'>
							      <tr>
											<td><div id='barchart_emissions_div' style='border: 1px solid #ccc'></div></td>
											<td><div id='barchart_emissions_hourly' style='border: 1px solid #ccc'></div></td>

							      </tr>
							    </table>";

			echo "<div align='center'><h3>Vehicle Mass Statistics</h3></div>";
			echo "<table class='columns' align='center'>
					<tr>
						<td><div id='barchart_mass_div' style='border: 1px solid #ccc'></div></td>
						<td><div id='barchart_mass_hourly' style='border: 1px solid #ccc'></div></td>
					</tr>
					</table>";
			echo "<br/>";
			echo "<div align='center'> <form action='SimulateCAZ.php' method='post'> <button class='buttons'  type='submit'>Simulate Ultra Low Emission Zone</button></form>";
			echo "</div>";
			echo "<br/>";
			echo "<div align='center'><h3>Query Statistics Table</h3></div>";
			echo "<div id='table_div'></div>";


			echo "<br/><script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
		    <script type='text/javascript'>
		      google.charts.load('current', {'packages':['table','corechart']});
		      google.charts.setOnLoadCallback(drawTable);

		      function drawTable() {
		        var data = new google.visualization.DataTable();
		        data.addColumn('string', 'Hour of Day');
		        data.addColumn('number', 'Total Vehicle Count');
		        data.addColumn('number', 'Total Vehicle Mass (kg)');
						data.addColumn('number', 'Total Vehicle Emissions (g/km)');
		        data.addRows([
		          $chart_val
		        ]);

		        var table = new google.visualization.Table(document.getElementById('table2_div'));

		        table.draw(data, {showRowNumber: false, width: '100%', height: '100%'});
		      }
		    </script>";
			echo "<div align='center'><h3>Hourly Query Statistics Table </h3></div>";
			echo "<div id='table2_div'></div>";

			$drop = "DROP TABLE IF EXISTS results;";
			mysqli_query($conn,$drop);
			mysqli_query($conn, "CREATE TABLE IF NOT EXISTS results $sql");

			$sql = "select Body_Type_Detail, count(*) as count from results group by Body_Type_Detail order by count(*) desc limit 10";
			$resultBodyType = mysqli_query($conn, $sql);
			$chartBodyType = "";
			while($row = mysqli_fetch_assoc($resultBodyType)) {
				$chartBodyType .= "['";
				foreach ($row as $field => $new_val) {
					if ($field == "count") {
						$chartBodyType .= "',$new_val";
					}
					else {
						$chartBodyType .= "$new_val";
					}
				}
				$chartBodyType .= "],";
			}
			$chartBodyType = rtrim($chartBodyType, ", ");

			$sql = "select Fuel_Type, count(*) as count from results group by Fuel_Type order by count(*) desc limit 10";
			$resultFuelType = mysqli_query($conn, $sql);
			$chartFuelType = "";
			while($row = mysqli_fetch_assoc($resultFuelType)) {
				$chartFuelType .= "['";
				foreach ($row as $field => $new_val) {
					if ($field == "count") {
						$chartFuelType .= "',$new_val";
					}
					else {
						$chartFuelType .= "$new_val";
					}
				}
				$chartFuelType .= "],";
			}
			$chartFuelType = rtrim($chartFuelType, ", ");

			$sql = "select Tier, count(*) as count from results group by Tier order by count(*) desc limit 10";
			$resultTier = mysqli_query($conn, $sql);
			$chartTier = "";
			while($row = mysqli_fetch_assoc($resultTier)) {
				$chartTier .= "['";
				foreach ($row as $field => $new_val) {
					if ($field == "count") {
						$chartTier .= "',$new_val";
					}
					else {
						$chartTier .= "$new_val";
					}
				}
				$chartTier .= "],";
			}
			$chartTier = rtrim($chartTier, ", ");

			$sql = "select Site, count(*) as count from results group by Site order by count(*) desc limit 10";
			$resultSite = mysqli_query($conn, $sql);
			$chartSite = "";
			while($row = mysqli_fetch_assoc($resultSite)) {
				$chartSite .= "['";
				foreach ($row as $field => $new_val) {
					if ($field == "count") {
						$chartSite .= "',$new_val";
					}
					else {
						$chartSite .= "$new_val";
					}
				}
				$chartSite .= "],";
			}
			$chartSite = rtrim($chartSite, ", ");
			#echo "$chartSite";

			$sql = "select Type, count(*) as count from results group by Type order by count(*) desc limit 10";
			$resultType = mysqli_query($conn, $sql);
			$chartType = "";
			while($row = mysqli_fetch_assoc($resultType)) {
				$chartType .= "['";
				foreach ($row as $field => $new_val) {
					if ($field == "count") {
						$chartType .= "',$new_val";
					}
					else {
						$chartType .= "$new_val";
					}
				}
				$chartType .= "],";
			}
			$chartType = rtrim($chartType, ", ");
			#echo "$chartType";

			$sql = "select Make, count(*) as count from results group by Make order by count(*) desc limit 10";
			$resultMake = mysqli_query($conn, $sql);
			$chartMake = "";
			while($row = mysqli_fetch_assoc($resultMake)) {
				$chartMake .= "['";
				foreach ($row as $field => $new_val) {
					if ($field == "count") {
						$chartMake .= "',$new_val";
					}
					else {
						$chartMake .= "$new_val";
					}
				}
				$chartMake .= "],";
			}
			$chartMake = rtrim($chartMake, ", ");
			#echo "$chartFuelType";

			echo "<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
    <script type='text/javascript'>
      google.charts.load('current', {'packages':['table','corechart']});
      google.charts.setOnLoadCallback(drawBodyType);
			google.charts.setOnLoadCallback(drawFuelType);
			google.charts.setOnLoadCallback(drawTier);
			google.charts.setOnLoadCallback(drawSite);
			google.charts.setOnLoadCallback(drawType);
			google.charts.setOnLoadCallback(drawMake);

      function drawBodyType() {

        var data = google.visualization.arrayToDataTable([
          ['Body Type', 'Count'],
          $chartBodyType
        ]);

        var options = {
          title: 'Top 10 Body Types'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_Bodytype'));

        chart.draw(data, options);
      }

			function drawFuelType() {

        var data = google.visualization.arrayToDataTable([
          ['FuelType', 'Count'],
          $chartFuelType
        ]);

        var options = {
          title: 'Fuel Types of Vehicles in results'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_FuelType'));

        chart.draw(data, options);
      }

			function drawTier() {

        var data = google.visualization.arrayToDataTable([
          ['Tier', 'Count'],
          $chartTier
        ]);

        var options = {
          title: 'Euro Class of Vehicles in results'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_Tier'));

        chart.draw(data, options);
      }

			function drawSite() {

        var data = google.visualization.arrayToDataTable([
          ['Site', 'Count'],
          $chartSite
        ]);

        var options = {
          title: 'Number of Vehicles spotted at each Site'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_Site'));

        chart.draw(data, options);
      }

			function drawMake() {

        var data = google.visualization.arrayToDataTable([
          ['Make', 'Count'],
          $chartMake
        ]);

        var options = {
          title: 'Top 10 Vehicle Manufacturers'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_Make'));

        chart.draw(data, options);
      }


			function drawType() {

        var data = google.visualization.arrayToDataTable([
          ['Type', 'Count'],
          $chartType
        ]);

        var options = {
          title: 'Vehicle Type'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_Type'));

        chart.draw(data, options);
      }

    </script>";
		echo "<div align='center'><h3><br/>Other key data found from Query</h3></div>";
		echo "<table class='columns'>
      <tr>
        <td><div id='piechart_Make' style='width: 500px; height: 300px;border: 1px solid #ccc'></div></td>
        <td><div id='piechart_Site' style='width: 500px; height: 300px;border: 1px solid #ccc'></div></td>
				<td><div id='piechart_Tier' style='width: 500px; height: 300px;border: 1px solid #ccc'></div></td>
				</tr>
	    </table>
			<table class='columns2'>
	      <tr>
				<td><div id='piechart_Bodytype' style='width: 500px; height: 300px;border: 1px solid #ccc'></div></td>
				<td><div id='piechart_Type' style='width: 500px; height: 300px;border: 1px solid #ccc'></div></td>
				<td><div id='piechart_FuelType' style='width: 500px; height: 300px;border: 1px solid #ccc'></div></td>
      </tr>
    </table>";

		}
		else{
			echo "You must select an option.";
		}
	?>
	<p>Click Below to go back</p>
	<form action="HomePage.php" method="post">
		<button class='buttons'  type='submit'>Go Back to the HomePage</button>

	</form>
</body>
</html>
