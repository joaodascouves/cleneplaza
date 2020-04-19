<?php

  static $page_info = Array(
    'title' => 'Register',
    'priority' => '50',
    'permission' => Array('guest')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  function proccess_register()
  {
    global $config;
    global $conn;
    $error = '';

    if( @empty($_POST['username']) ||
        @empty($_POST['email']) ||
        @empty($_POST['password']) ||
        @empty($_POST['confirmation']) )
    {
      $error = 'Please fill in all the fields.';
    }

    if( @strcmp($_POST['password'], $_POST['confirmation']) )
      $error = 'Passwords don\'t match.';

    $query = mysqli_query($conn, "SELECT ID FROM cl_users WHERE user_email='{$_POST['email']}'");
    if( mysqli_num_rows($query)>0 )
      $error = 'You are already registered. Please log in.';

    $query = mysqli_query($conn, "SELECT ID FROM cl_users WHERE user_name='{$_POST['username']}'");
    if( mysqli_num_rows($query)>0 )
      $error = 'Username already taken.';

    if( !empty($error) )
    {
      return get_view('register.form', Array(
        'register.form.error' => $error
      ));
    }
    else
    {
      $query = mysqli_query($conn, "INSERT INTO cl_users (user_email, user_name, user_password)
      VALUES ('{$_POST['email']}', '{$_POST['username']}', MD5(CONCAT('{$_POST['password']}', '{$config['salt']}')))");

      if( mysqli_affected_rows($conn)>0 )
      {
        return header('Location: /clene2/');
      }
    }



  }

  echo make_page(Array(
    'body.inner' => ($_SERVER['REQUEST_METHOD'] === 'POST' ? proccess_register() : get_view('register.form') )

  ));
