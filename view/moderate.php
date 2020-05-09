<?php

  static $page_info = Array(
    'title' => 'Moderate',
    'priority' => -1,
    'permission' => Array('admin', 'mod', 'user')
  );

  if( context_parse(__FILE__) )
    return $page_info;

  include_once 'includes/controls/moderate_control.php';

  if( $_SERVER['REQUEST_METHOD'] === 'POST' && !@strcmp('true', $_GET['confirm']) )
  {
    $result = moderate_action(array_merge($_POST, $_GET))['message'];
  }
  else
  {
    $sanitize = moderate_panel(array_merge($_POST, $_GET));

    if( $sanitize['status'] !== 0 )
      $result = $sanitize;

    else
    {
      $result = get_view(( !$sanitize['user_flag'] ? 'moderate.user' : 'moderate.object'),
        Array(
          'parameters' => $sanitize['parameters'],
          'entry' => $sanitize['entry']
        ));
    }
  }

  echo make_page(Array(
    'body.inner' => $result
  ));
