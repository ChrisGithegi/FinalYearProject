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
      echo "<form action='charts.php' method='post'>";
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
		?>
  <p>Click Below to go back</p>
  <form action="index.php" method="post">
    <p><input type="submit" value="Go Back"></p>
  </form>
</body>
</html>
