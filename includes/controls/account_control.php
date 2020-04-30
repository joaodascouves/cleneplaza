<?php

  /*
    Checks if credential pair exists in the database.
    If so, store user object. Otherwise, returns form view.

    @parameter Array $parameters
    @return null
  */
  function account_login($parameters)
  {
    global $config;
    global $conn;

    if( @empty($parameters['username']) || @empty($parameters['password']) )
      return get_view('login.form', Array(
        'error' => 'Please fill in all the fields.'
      ));

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_users`
      WHERE `email`='%s' AND `password`=MD5(CONCAT('%s', '%s'))",
      $parameters['username'], $parameters['password'], $config['salt']));

    if( @mysqli_num_rows($query)>0 )
    {
      $_SESSION['user_id'] = mysqli_fetch_array($query)[0];
      return header('Location: /clene2/');
    }
    else {
      return get_view('login.form', Array(
        'error' => 'Incorrect e-mail or password.'
      ));
    }
  }

  /*
    Register account in the database, with the proper sanitization.

    @parameter Array $parameters
    @return null
  */
  function account_register($parameters)
  {
    global $config;
    global $conn;
    $error = '';

    if( @empty($parameters['username']) ||
        @empty($parameters['email']) ||
        @empty($parameters['password']) ||
        @empty($parameters['confirmation']) )
    {
      $error = 'Please fill in all the fields.';
    }

    if( @strcmp($parameters['password'], $parameters['confirmation']) )
      $error = 'Passwords don\'t match.';

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_users` WHERE `email`='%s'", $parameters['email']));
    if( mysqli_num_rows($query)>0 )
      $error = 'You are already registered. Please log in.';

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_users` WHERE `name`='%s'", $parameters['username']));
    if( mysqli_num_rows($query)>0 )
      $error = 'Username already taken.';

    if( !empty($error) )
    {
      return get_view('register.form', Array(
        'error' => $error
      ));
    }
    else
    {
      $query = mysqli_query($conn, sprintf("INSERT INTO `cl_users` (`email`, `name`, `password`)
      VALUES ('%s', '%s', MD5(CONCAT('%s', '%s')))", $parameters['email'], $parameters['username'], $parameters['password'], $config['salt']));

      if( mysqli_affected_rows($conn)>0 )
      {
        return header('Location: /clene2/');
      }
    }
}
