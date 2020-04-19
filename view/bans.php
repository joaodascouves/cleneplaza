<?php

  static $page_info = Array(
    'title' => 'Last bans',
    'priority' => 2,
    'permission' => Array('user', 'mod')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  echo make_page(Array(
    'body.inner' => 'Here you see the last banned fellows'
  ));
