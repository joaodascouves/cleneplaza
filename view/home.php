<?php

  static $page_info = Array(
    'title' => 'Home',
    'priority' => 0,
    'permission' => Array('admin', 'user', 'mod', 'guest'),
    'styles' => Array('wall')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  echo make_page(Array(
    'body.inner' => (
      $config['public_wall'] || current_user_privilege() !== 'guest' ?

      get_view('post.wall', Array(
        'offset' => ( @is_numeric($_GET['offset']) ? $_GET['offset'] : 0 ),

      )) :


      'Please log-in to access this page.'. "<br>Level: ". current_user_privilege()
    )

  ));
