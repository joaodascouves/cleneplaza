<?php

  static $page_info = Array(
    'title' => 'Announcements',
    'priority' => 2,
    'permission' => Array('admin', 'mod', 'user'),
    'styles' => Array('news', 'post')
  );

  if( context_parse(__FILE__) )
    return $page_info;

  include 'includes/controls/news.control.php';

  if( !@strcmp('submit', $_GET['action']) && !@strcmp('admin', current_user_get()['level']) )
  {
    if( $_SERVER['REQUEST_METHOD'] === 'POST' )
      $result = news_insert(array_merge($_POST, $_FILES))['message'];

    else
      $result = get_view('news/insert');
  }
  else
    $result = get_view('news/wall');

  echo make_page(Array(
    'body.inner' => $result

  ));
