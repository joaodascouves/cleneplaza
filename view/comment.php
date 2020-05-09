<?php

  static $page_info = Array(
    'title' => 'Post reply',
    'priority' => -1,
    'permission'=> Array('admin', 'mod', 'user')
  );

  if( context_parse(__FILE__ ) )
    return $page_info;

  include 'includes/controls/comment_control.php';

  $comment_result = comment_message_insert(array_merge($_POST, $_FILES));

  $message = ( $comment_result['status'] !== 0 ? $message : 'Comment posted sucessfully.' );

  echo make_page(Array(
    'body.inner' => $message
  ));
