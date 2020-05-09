<?php

  static $page_info = Array(
    'title' => 'Home',
    'priority' => 0,
    'permission' => Array('admin', 'user', 'mod', 'guest'),
    'styles' => Array('wall')
  );

  if( context_parse(__FILE__) )
    return $page_info;

  echo make_page(Array(
    'body.inner' => (
      $config['public_wall'] || current_user_privilege() !== 'guest' ?

      get_view('post.wall', Array(
        'offset' => ( @is_numeric($_GET['offset']) ? $_GET['offset'] : 0 )

      )) :


    ( !@strcmp('true', $_COOKIE['welcome']) ?

      "Account created sucessfully.<br/>Now wait for the activation link (it can take a while)." :
      get_view('presentation')
    ))

  ));
