<?php

  static $page_info = Array(
    'title' => 'Profile',
    'priority' => 0,
    'permission' => Array('user','mod')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  echo make_page(Array(
    'body.inner' => get_view('profile.form', Array(
      'profile.form.name' => $_SESSION['user_name'],
      'profile.form.created_at' => $_SESSION['created_at'],
      'profile.form.about' => $_SESSION['user_about']
      ))

  ));
