<?php

  static $page_info = Array(
    'title' => 'Home',
    'priority' => 0,
    'permission' => Array('guest', 'user', 'mod', 'alerted', 'admin')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  echo make_page(Array(
    'body.inner' => (
      $config['public_wall'] || $_SESSION['user_level'] === 'user' ?
      get_view('wall') :
      'Please log-in to access this page.'. "<br>Level: {$_SESSION['user_level']}"
    ),
    // 'config.important' => "Level: ". $_SESSION['user_level']

  ));
