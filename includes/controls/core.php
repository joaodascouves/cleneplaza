<?php

  /*
    Returns user object stored in /includes/controls/login.php.

    @return Array
  */
  function current_user_get()
  {
    $user = $_SESSION['user'];
    return $user;
  }

  /*
    Alias for current_user_get()['level'].
    If level is empty, returns 'guest'.

    @return String
  */
  function current_user_privilege()
  {
    $level = ( !@empty(current_user_get()['level']) ? current_user_get()['level'] : 'guest' );
    return $level;
  }

  /*
    Returns a boolean indicating if scripts are either enabled
    our disabled.

    @return Boolean
  */
  function no_script()
  {
    return ( !@strcmp('true', $_GET['noscript']) ||
      @strcmp('false', $_GET['noscript']) && !@strcmp('true', $_COOKIE['noscript']) );
  }
