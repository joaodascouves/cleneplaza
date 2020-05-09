<?php

  static $page_info = Array(
    'title' => 'Logout',
    'priority' => 0,
    'permission' => Array('admin', 'mod', 'user'),
    'align' => 'right'
  );

  if( context_parse(__FILE__) )
    return $page_info;

  session_destroy();
  header("Location: {$config['siteroot']}/?op=home");
