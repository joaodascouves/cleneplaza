<?php

  foreach( Array($_GET, $_POST, $_SERVER) as $input)
  {
    foreach( $input as $key => $value )
    {
      $input[$key] = stripslashes($value);
      $input[$key] = htmlentities($value);
      $input[$key] = strip_tags($value);
      $input[$key] = mysqli_real_escape_string($value);
    }
  }
