<?php

  static $page_info = Array(
    'title' => 'Logout',
    'priority' => 50,
    'permission' => Array('user')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  session_destroy();
  header("Location: {$config['siteroot']}/?op=home");
