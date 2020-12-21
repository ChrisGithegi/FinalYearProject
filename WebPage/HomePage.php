<?php
include "config.php";
?>
<!doctype html>
<html>
  <head>

  <?php
  if(isset($_POST['submit'])){

    if(!empty($_POST['lang'])) {

      $lang = implode(",",$_POST['lang']);

      // Insert and Update record
      $checkEntries = mysqli_query($con,"SELECT * FROM languages");
      if(mysqli_num_rows($checkEntries) == 0){
        mysqli_query($con,"INSERT INTO languages(language) VALUES('".$lang."')");
      }else{
        mysqli_query($con,"UPDATE languages SET language='".$lang."' ");
      }

    }

  }
  ?>
  </head>
  <body>
  <form method="post" action="">
    <span>Select languages</span><br/>
    <?php

    $checked_arr = array();

    // Fetch checked values
    $fetchLang = mysqli_query($con,"SELECT * FROM languages");
    if(mysqli_num_rows($fetchLang) > 0){
      $result = mysqli_fetch_assoc($fetchLang);
      $checked_arr = explode(",",$result['language']);
    }

    // Create checkboxes
    $languages_arr = array("PHP","JavaScript","jQuery","AngularJS");
    foreach($languages_arr as $language){

      $checked = "";
      if(in_array($language,$checked_arr)){
        $checked = "checked";
      }
      echo '<input type="checkbox" name="lang[]" value="'.$language.'" '.$checked.' > '.$language.' <br/>';
    }
    ?>

    <input type="submit" value="Submit" name="submit">
  </form>

  </body>
</html>
