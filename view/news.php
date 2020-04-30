<?php

  static $page_info = Array(
    'title' => 'Announcements',
    'priority' => 2,
    'permission' => Array('admin', 'mod', 'user')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  echo make_page(Array(
    'body.inner' => 'Notícias'

  ));
