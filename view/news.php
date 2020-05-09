<?php

  static $page_info = Array(
    'title' => 'Announcements',
    'priority' => 2,
    'permission' => Array('admin', 'mod', 'user'),
    'styles' => Array('news', 'post')
  );

  if( context_parse(__FILE__) )
    return $page_info;

  include 'includes/controls/news_control.php';

  echo make_page(Array(
    'body.inner' => ( !@strcmp('submit', $_GET['action']) && !@strcmp('admin', current_user_get()['level']) ?

      ( $_SERVER['REQUEST_METHOD'] === 'POST' ? news_insert(array_merge($_POST, $_FILES))['message'] : get_view('news.insert') ) :
      get_view('news.wall')
      )

  ));
