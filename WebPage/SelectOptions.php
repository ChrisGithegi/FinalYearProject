<?php
	include_once 'dbconnect.php'
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>ANPR DATA VISUALISATION</title>
	<link rel="stylesheet" href="./style.css">
</head>
<body>

  <?php
    if(isset($_POST['options'])){
      $columns = $_POST['options'];
      echo "<form class='form' action='Charts.php' method='post'>";
      foreach ($columns as $key => $value) {
        echo "<input type='hidden' value='$value', name='indiv_vals[]'>";
        echo "<p> $value </p>";
        $sql="SELECT $value from traffic group by $value order by $value ASC;";
        $result = mysqli_query($conn, $sql);
        $resultCheck = mysqli_num_rows($result);

        if ($resultCheck > 0) {
          while($row = mysqli_fetch_assoc($result)) {
            foreach ($row as $field => $new_val) {
							echo "<div class='inputGroup'>
							<input id='$new_val' type='checkbox' value='$new_val', name='indiv_vals[]'/>
							<label for='$new_val'>$new_val</label>
						 </div>";
            }
          }
        }
      }
      echo "<p><button class='buttons' type='submit'>Proceed to Visualisation</button></p>";
      echo "</form>";
    }
		?>
  <p>Click Below to go back</p>
  <form action="HomePage.php" method="post">
    <p><button class="buttons" type="submit">Go Back</button></p>
  </form>
</body>
</html>
