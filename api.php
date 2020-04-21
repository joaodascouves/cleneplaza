<?php

  include 'includes/config.php';
  include 'includes/database.php';

  include 'includes/api_calls.php';

  if( !@empty($action = "api_{$_GET['action']}") && @function_exists($action) )
  {
    session_start();
    echo call_user_func($action, array_merge($_POST, $_FILES));
  }
