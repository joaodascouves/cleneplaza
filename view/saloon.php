<?php

  static $page_info = Array(
    'title' => 'Saloon',
    'priority' => -1,
    'permission' => Array('admin', 'mod', 'user')
  );

  if( context_parse(__FILE__) )
    return $page_info;

  include 'includes/controls/saloon.control.php';

  if( !@empty(($alias = $_GET['alias'])) && !@empty(($action = $_GET['action'])) )
  {
    if( !@strcmp('rules', $action) )
      $result = saloon_rules($alias)['message'];
  }

  echo make_page(Array(
    'body.inner' => $result
  ));
