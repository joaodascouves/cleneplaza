<?php

  include_once 'includes/controls/core.php';

  static $__api_actions = Array(
    'post_collection_fetch' =>      Array( Array('admin', 'mod', 'user'), Array('POST') ),
    'post_image_insert' =>          Array( Array('admin', 'mod', 'user'), Array('POST') ),

    'mirror_collection_fetch' =>    Array( Array('admin', 'mod', 'user'), Array('POST') ),
    'mirror_check_and_insert' =>    Array( Array('admin', 'mod', 'user'), Array('POST') ),

    'comment_collection_fetch' =>   Array( Array('admin', 'mod', 'user'), Array('POST') ),
    'comment_message_insert'   =>   Array( Array('admin', 'mod', 'user'), Array('POST') ),

    'moderate_action'          =>   Array( Array('admin', 'mod', 'user'), Array('POST') )

  );

  function api_action_exists($action)
  {
    global $__api_actions;
    return array_key_exists($action, $__api_actions);
  }
