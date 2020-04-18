<?php

  static $page_info = Array(
    'title' => 'Register',
    'permission' => Array('guest')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  echo make_page(Array(
    'head.title' => $page_info['title'],
    'body.banner' => "<h1>{$config['title']}</h1>{$config['subtitle']}",
    'body.inner' => get_view('register')

  ), $with_menu=true);
