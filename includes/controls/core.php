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


  /*
    Parse $file applying proper filters. If the file passes it all,
    it is uploaded on the specific context folder, than given a upload name and a checkfile_sum.
    Otherwise, an error is raised. $specifier is a variable used merely for convenience
    with the post_image_insert function (includes/control/post_control.php).

    @parameter $file Array
    @parameter $context String
    @parameter $specifier String
    @return Array
  */
  function parse_and_upload_image($file, $context, $specifier='')
  {
    global $config;
    global $conn;

    if( !exif_imagetype($file) || !@in_array($file_ext = end(explode('/', $file['type'])), $config['allowed_exts']) )
      return 1; // Invalid file.

    $file_sum = md5_file($file['tmp_name']);

    $query = mysqli_query(sprintf("SELECT `ID` FROM `cl_%s` WHERE (file_realname='%s' OR file_sum='%s')
      AND UNIX_TIMESTAMP(updated_at)>%d",

      $file['name'],
      $file_sum,
      $context,
      time() - $config['unique_expiry']
    ));

    if( mysqli_num_rows($query)>0 )
      return 2; // File recently posted in that context.

    $file_path = sprintf("%s%s/%s",
      $config['upload_path'],
      $context,
      ( !@empty($specifier) ? "{$specifier}/" : '' ),
      time(),
      $file_ext
    );

    if( move_uploaded_file($file['tmp_name'], $file_path) )
    {
      //
    }
  }
