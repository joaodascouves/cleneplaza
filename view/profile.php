<?php

  static $page_info = Array(
    'title' => 'Profile',
    'priority' => 1,
    'permission' => Array('admin', 'mod', 'user'),
    'styles' => Array('profile'),
    'align' => 'right'
  );

  if( context_parse(__FILE__) )
    return $page_info;

  include_once 'includes/controls/user.control.php';

  $edit = !@strcmp('edit', $_GET['action']);

  $user = ( isset($_GET['profile_id']) ?
    user_get_by_id(validate_natural_num($_GET['profile_id'])) :
    current_user_get() );

  if( !$edit && $user )
  {
    $user = array_merge($user,
      Array('posts_count' => user_posts_count_by_id($user['ID']))
    );
  }


  echo make_page(Array(
    'body.inner' => ( $edit ?

      ( $_SERVER['REQUEST_METHOD'] === 'POST' ?
        user_update($_POST) :
        get_view('profile/form', $user)
      ) :

      get_view('profile/display', $user)
      )

  ));
