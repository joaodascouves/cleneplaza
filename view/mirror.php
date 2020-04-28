<?php

  static $page_info = Array(
    'title' => 'Mirroring',
    'priority' => 1,
    'permission' => Array('user', 'mod'),
    'styles' => Array('mirror', 'wall')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  include 'includes/controls/mirror_control.php';

  echo make_page(Array(
    'body.inner' => ( !@strcmp('submit', $_GET['action']) ?

      ( $_SERVER['REQUEST_METHOD'] === 'POST' ?
        mirror_check_and_insert($_POST['url'])['message'] :
        get_view('mirror.insert')
      ) :

      get_view('mirror.wall')
      )
  ));
