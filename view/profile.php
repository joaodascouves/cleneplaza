<?php

  static $page_info = Array(
    'title' => 'Profile',
    'priority' => 0,
    'permission' => Array('user','mod')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  include 'includes/controls/user_control.php';

  $user = ( isset($_GET['profileId']) ? user_get_by_id(validate_natural_num($_GET['profileId'])) : current_user_get() );

  echo make_page(Array(
    'body.inner' => get_view('profile.form', $user
      + Array('posts_count' => user_posts_count_by_id($user['ID'])))

  ));
