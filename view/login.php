<?php

  static $page_info = Array(
    'title' => 'Login',
    'priority' => 49,
    'permission' => Array('guest'),
    'align' => 'right'
  );

  if( parse_context(__FILE__) )
    return $page_info;

  include 'includes/controls/account_control.php';

  echo make_page(Array(
    'body.inner' => ($_SERVER['REQUEST_METHOD'] === 'POST' ? account_login($_POST) : get_view('login.form'))

  ));
