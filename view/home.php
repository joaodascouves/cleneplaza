<?php

  static $page_info = Array(
    'title' => 'Home',
    'permission' => Array('guest', 'user', 'mod', 'alerted', 'admin')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  echo make_page(Array(
    'head.title' => $page_info['title'],
    'body.banner' => "<h1>{$config['title']}</h1>{$config['subtitle']}",
    'body.inner' => get_view('wall')

  ), $with_menu=true);
