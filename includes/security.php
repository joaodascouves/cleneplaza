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
    {
      if( @is_array($input[$key]) )
        $input[$key] = null;

      else
        $input[$key] = secure_str($value);
    }
  }

  if( sizeof($_FILES)>0 )
  {
    foreach( $_FILES as &$file )
      $file['name'] = secure_str($file['name']);
  }
