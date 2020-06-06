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

    if( @empty(($username = trim($parameters['username']))) ||
        @empty(($password = trim($parameters['password']))) )
    {
      return Array(
        'status' => 1,
        'message'=> 'Please fill in all the fields.'
      );
    }

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_users` WHERE `email`='%s' AND `password`=MD5(CONCAT('%s', '%s'))",
      $username,
      $password,
      $config['salt']));

    if( @mysqli_num_rows($query) === 0 )
      return Array(
        'status' => 2,
        'message'=> 'Incorrect e-mail or password.'
      );

    $_SESSION['user_id'] = mysqli_fetch_array($query)[0];
    header('Location: ?context=home');

    return Array(
      'status' => 0,
      'message'=> ''
    );
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

    if( @empty(($username = trim($parameters['username']))) ||
        @empty(($email = trim($parameters['email']))) ||
        @empty(($password = $parameters['password'])) ||
        @empty(($confirmation = $parameters['confirmation'])) )
    {
      return Array(
        'status' => 1,
        'message'=> 'Please fill in all the fields.'
      );
    }

    if( @strcmp($password, $confirmation) )
      return Array(
        'status' => 2,
        'message'=> 'Passwords doesn\'t match.'
      );

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_users` WHERE `email`='%s'", $email));
    if( mysqli_num_rows($query)>0 )
      return Array(
        'status' => 3,
        'message'=>'E-mail address already registered.'
      );

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_users` WHERE `name`='%s'", $username));
    if( mysqli_num_rows($query)>0 )
      return Array(
        'status' => 4,
        'message'=> 'Username already taken.'
      );

    $query = mysqli_query($conn, sprintf("INSERT INTO `cl_users` (`email`, `name`, `password`, `status`)
    VALUES ('%s', '%s', MD5(CONCAT('%s', '%s')), 'active')", $email, $username, $password, $config['salt']));

    if( @mysqli_affected_rows($conn)>0 )
    {
      setcookie('welcome', 'true', time()+60, '/');

      $key = md5($email . $config['salt']);
      $token = md5($key . $config['salt']);

      $mail_body = base64_encode("Access the link below to verify your account:\n\n
http://localhost/clene2/?context=verify&key={$key}&token={$token}");

      // system("sendEmail -f financeiro@netwaytelecom.com.br -t '{$email}' -m '{$mail_body}' -u 'teste'
      // -s smtp.netwaytelecom.com.br:587 -xu financeiro@netwaytelecom.com.br -xp 'nwr3v3str31s' &>/dev/null");


      return header('Location: ?context=home');
    }
  }

  /*
    Switches user status to activated, if correct token is provided.

    @parameter String $key
    @parameter String $token
    @return Array
  */
  function account_verify($key, $token)
  {
    if( @empty($key) || @empty($token) )
      return Array(
        'status' => 1,
        'message'=> 'Both key and token must be filled.'
      );

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_users`
      WHERE MD5(CONCAT(`key`, '%s'))='%s' AND `status`='inactive'", $config['salt'], $key));

    if( !$query || mysqli_num_rows($query)>0 )
      return Array(
        'status' => 2,
        'message'=> 'There\'s nothing to be activated.'
      );

    if( @strcmp($token, md5($key . $config['salt'])) )
      return Array(
        'status' => 3,
        'message'=> 'Invalid token.'
      );

    $id = mysqli_fetch_array($query)[0];
    $query = mysqli_query($conn, sprintf("UPDATE `cl_users` SET `status`='active'
      WHERE `ID`=%d", $id));

    if( !$query || @mysqli_affected_rows($query) === 0 )
      return Array(
        'status' => 4,
        'message'=> 'User couldn\'t be activated.'
      );

    return Array(
      'status' => 0,
      'message'=> 'User activated successfully.'
    );
  }
