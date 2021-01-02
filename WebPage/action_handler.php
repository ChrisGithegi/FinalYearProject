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
    if(isset($_POST['options'])){
      $columns = $_POST['options'];
      echo "Headings:<br/>";
      echo "<form action='action_handler.php' method='post'>";
      foreach ($columns as $key => $value) {
        echo "<input type='hidden' value='$value', name='indiv_vals[]'>";
        echo "<p> $value </p>";
        $sql="SELECT $value from traffic group by $value order by $value ASC;";
        $result = mysqli_query($conn, $sql);
        $resultCheck = mysqli_num_rows($result);

        if ($resultCheck > 0) {
          while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            foreach ($row as $field => $new_val) {
              echo "<input type='checkbox' value='$new_val', name='indiv_vals[]'> $new_val<br/>";
            }
            echo "</tr>";
            echo "<br>";
          }
        }
      }
      echo "<p><input type='submit' value='Proceed'></p>";
      echo "</form>";
    }
    elseif (isset($_POST['indiv_vals'])) {
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
          if (($value == "Date") || ($value == "Site") || ($value == "Make") || ($value == "Type") || ($value == "Body_Type")){
            $new_sql .= "($value in (";
            $place = $value;
            array_push($stack_col, "$value");
          }
        }
        elseif (($resultCheck > 0 && $x > 0)) {
          if (($value == "Date") || ($value == "Site") || ($value == "Make") || ($value == "Type") || ($value == "Body_Type")){
            $new_sql = rtrim($new_sql, ", ");
            $new_sql .= ")) and ($value in (";
            $place = $value;
            array_push($stack_col, "$value");
          }
        }
        elseif (($place == "Date") || ($place == "Site") || ($place == "Make") || ($place == "Type") || ($place == "Body_Type")) {
          $new_sql .= "'$value',";
        }
        else {
          echo "wrong";
        }
        $x++;
      }
      $new_sql = rtrim($new_sql, ", ");
      $new_sql .= "))";
      $sql = "SELECT";
      foreach ($stack_col as $key => $value) {
        $sql .= " $value,";
      }
      $sql .= " count(*) as 'Total Count', sum(Mass) as 'Vehicle Mass', sum(co2) as 'Vehicle Emissions' FROM traffic where ";
      $sql .= $new_sql;

      $sql .= " GROUP BY";
      foreach ($stack_col as $key => $value) {
        $sql .= " $value,";
      }

      $sql = rtrim($sql, ", ");
      $sql .= ";";

      $result = mysqli_query($conn, $sql);
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
      echo "<br/><br/>";

      $sql = "SELECT extract(hour from Time) as Hour, count(*) as 'Total Count', sum(Mass) as 'Vehicle Mass', sum(co2) as 'Vehicle Emissions' FROM traffic where $new_sql GROUP BY extract(hour from Time) order by extract(hour from Time) ASC;";
      $result = mysqli_query($conn, $sql);
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
