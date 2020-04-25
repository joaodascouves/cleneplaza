<?php

  include 'includes/controls/core.php';

  static $__api_actions = Array();

  $__api_actions = Array(
    'post_collection_get' =>  Array( Array('user', 'mod'),      Array('POST') ),
    'post_image_insert' =>    Array( Array('user', 'mod'),      Array('POST') )

  );

  function api_action_exists($action)
  {
    global $__api_actions;
    return array_key_exists($action, $__api_actions);
  }
