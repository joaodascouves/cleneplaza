<?php

  static $page_info = Array(
    'title' => 'Ranking',
    'priority' => 0,
    'permission' => Array('admin', 'mod', 'user')
  );

  if( context_parse(__FILE__) )
    return $page_info;

  echo make_page(Array(
    'body.inner' => 'Here you can see the players ranking.'
  ));
