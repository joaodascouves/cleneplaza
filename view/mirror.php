<?php

  static $page_info = Array(
    'title' => 'Mirroring',
    'priority' => 0,
    'permission' => Array('admin', 'mod', 'user'),
    'styles' => Array('mirror', 'wall', 'post')
  );

  if( context_parse(__FILE__) )
    return $page_info;

  include 'includes/controls/mirror.control.php';

  $action = ( isset($_GET['action']) ? $_GET['action'] : 'wall' );

  if( !strcmp('wall', $action) )
  {
    echo make_page(Array(
      'body.inner' => get_view('mirror/wall')
    ));
  }

  else if( !strcmp('rules', $action) )
  {
    echo make_page(Array(
      'body.inner' => get_view('mirror/rules')
    ));
  }

  else if( !strcmp('submit', $action) )
  {
    echo make_page(Array(
      'body.inner' => ( $_SERVER['REQUEST_METHOD'] !== 'POST' ?
        get_view('mirror/insert') :
        mirror_check_and_insert($_POST)['message']
      )
    ));
  }

  else if( !strcmp('show', $action) )
  {
    echo make_page(Array(
      'body.inner' => get_view('mirror/display',
        mirror_get_by_id(validate_natural_num($_GET['entry_id'])))
    ));
  }
