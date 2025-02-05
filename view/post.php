<?php

  static $page_info = Array(
    'title' => 'Post',
    'priority' => -1,
    'permission' => Array('admin', 'mod', 'user'),
    'styles' => Array('post')
  );

  if( context_parse(__FILE__) )
    return $page_info;

  include 'includes/controls/post.control.php';

  $action = ( isset($_GET['action']) ? $_GET['action'] : 'submit' );

  if( !strcmp('submit', $action) )
  {
    echo make_page(Array(
      'body.inner' => ( $_SERVER['REQUEST_METHOD'] !== 'POST' ?
        get_view('post/insert') :
        post_image_insert(array_merge($_POST, $_FILES))['message']
      )
    ));
  }

  else if( !strcmp('show', $action) && ($entry_id = @validate_natural_num($_GET['entry_id']))>0 )
  {
    echo make_page(Array(
      'body.inner' => get_view('post/display', post_get_by_id($entry_id))
    ));
  }
