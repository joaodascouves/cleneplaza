<?php

  static $page_info = Array(
    'title' => 'Verification',
    'priority' => -1,
    'permission'=> Array('guest')
  );

  if( context_parse(__FILE__) )
    return $page_info;

  include 'includes/controls/account.control.php';

  echo make_page(Array(
    'body.inner' => @account_verify($_GET['email'], $_GET['token'])['message']
  ));
