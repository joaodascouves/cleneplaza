<?php

  function secure_str($string)
  {
    global $conn;

    $string = stripslashes($string);
    $string = htmlentities($string);
    $string = strip_tags($string);
    $string = mysqli_real_escape_string($conn, $string);

    return $string;
  }

  // Properly escapes each user input variables to prevent SQLi and XXS attacks.
  foreach( Array(&$_GET, &$_POST, &$_SERVER) as &$input )
  {
    foreach( $input as $key => $value )
      $input[$key] = secure_str($value);
  }
