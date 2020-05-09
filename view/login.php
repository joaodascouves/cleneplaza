<?php

  static $page_info = Array(
    'title' => 'Login',
    'priority' => 49,
    'permission' => Array('guest'),
    'align' => 'right'
  );

  if( context_parse(__FILE__) )
    return $page_info;

  include 'includes/controls/account_control.php';

  if( !@empty($_COOKIE['message']) )
  {
    if( !@strcmp($_COOKIE['message'], token_create('inactive')) )
      $message = 'You must verify your account through the link sent to your email address.';
  }

  echo make_page(Array(
    'body.inner' => ($_SERVER['REQUEST_METHOD'] === 'POST' ?

      get_view('login.form', Array( 'error'=> account_login($_POST)['message']) ) :
      get_view('login.form', Array( 'error'=>( isset($message) ? $message : '' )) )
      )

  ));
