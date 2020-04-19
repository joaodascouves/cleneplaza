<?php

  static $page_info = Array(
    'title' => 'Login',
    'priority' => 49,
    'permission' => Array('guest')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  function proccess_login()
  {
    global $config;
    global $conn;

    if( @empty($_POST['username']) || @empty($_POST['password']) )
      return 'Preencha todos os campos.';

    $query = mysqli_query($conn, "SELECT * FROM cl_users
      WHERE user_email='{$_POST['username']}'
      AND user_password=MD5(CONCAT('{$_POST['password']}', '{$config['salt']}'))");

    if( mysqli_num_rows($query)>0 )
    {
      $_SESSION = array_merge($_SESSION, mysqli_fetch_assoc($query));
      return header('Location: /clene2/');
    }
    else {
      return get_view('login.form', Array(
        'login.form.error' => 'Incorrect e-mail or password.'
      ));
    }
  }

  echo make_page(Array(
    'body.inner' => ($_SERVER['REQUEST_METHOD'] === 'POST' ? proccess_login() : get_view('login.form'))

  ));
