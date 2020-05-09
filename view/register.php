<?php

  static $page_info = Array(
    'title' => 'Register',
    'priority' => 0,
    'permission' => Array('guest'),
    'align' => 'right'
  );

  if( context_parse(__FILE__) )
    return $page_info;

  include 'includes/controls/account_control.php';

  if( @strcmp($_COOKIE['token'], md5($_COOKIE['answer'] . $config['salt'])) )
  {
    setcookie('next', '?context=register', time()+600, '/');
    header('Location: ?context=guard');
    exit;
  }

  echo make_page(Array(
    'body.inner' => ($_SERVER['REQUEST_METHOD'] === 'POST' ?

      get_view('register.form', Array('error' => account_register($_POST)['message'])) :
      get_view('register.form', Array('error' => 'E-mail must be valid and non disposable.'))

      )

  ));
