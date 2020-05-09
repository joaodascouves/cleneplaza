<?php

  function token_create($str)
  {
    global $config;

    return md5(trim($str) .
      $_SERVER['HTTP_USER_AGENT'] . strval(floor(intval(date('i', time()))/10)) . $config['salt']);
  }

  function secure_str($string, $extended_replace=true)
  {
    global $conn;

    $string = stripslashes(urldecode($string));
    $string = htmlentities($string, ENT_QUOTES);
    $string = strip_tags($string);
    $string = mysqli_real_escape_string($conn, $string);

    if( $extended_replace )
      $string = str_replace(
        Array('(', ')', '=', '..', '[', ']', ';'),
        Array('&lpar;', '&rpar;', '&equals;', '&period;&period;', '&lsqb;', '&rsqb;', '&semi;'),
        $string
      );

    return $string;
  }

  foreach( Array(&$_GET, &$_POST/*, &$_SERVER*/) as &$input )
  {
    foreach( $input as $key => $value )
    {
      if( @is_array($input[$key]) )
        $input[$key] = null;

      else
      {
        $input[$key] = secure_str($value);

        if( preg_match('/^(-|\.|)([0-9]+)/', $value) || preg_match('/(ID|_id)$/', $key) )
          $input[$key] = intval($value);
      }
    }
  }

  if( sizeof($_FILES)>0 )
  {
    foreach( $_FILES as &$file )
      $file['name'] = secure_str($file['name'], false);
  }
