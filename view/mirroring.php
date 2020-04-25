<?php

  static $page_info = Array(
    'title' => 'Mirroring',
    'priority' => 1,
    'permission' => Array('user', 'mod')
  );

  if( parse_context(__FILE__) )
    return $page_info;

  echo make_page(Array(
    'body.inner' => 'Teste'
  ));
