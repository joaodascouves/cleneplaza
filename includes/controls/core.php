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
    Otherwise, an integer is returned. $specifier is a variable used merely for convenience
    with the post_image_insert function (includes/control/post_control.php).

    @parameter $file Array
    @parameter $context String
    @parameter $specifier String
    @return Array
  */
  function parse_and_upload_image($file, $context, $specifier='')
  {
    // $config['image_unique_expiry']
    global $config;
    global $conn;

    if( !exif_imagetype($file['tmp_name']) || !@in_array($file_ext = end(explode('/', $file['type'])), $config['allowed_exts']) )
      return 1; // Invalid file.

    $file_sum = md5_file($file['tmp_name']);
    $tolerance = time() - $config['image_unique_expiry'];

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_%s` WHERE (`file_name`='%s' OR `file_sum`='%s')
      AND (UNIX_TIMESTAMP(`updated_at`)>%d OR UNIX_TIMESTAMP(`created_at`)>%d)",

      $context,
      $file['name'],
      $file_sum,
      $tolerance,
      $tolerance
    ));

    if( !$query )
    {
      die(mysqli_error($conn));
      return 4;
    }

    if( mysqli_num_rows($query)>0 )
      return 2; // File recently posted in that context.

    $file_path = sprintf("%s%s/%s%s-%s.%s",
      $config['upload_path'],
      $context,
      ( !@empty($specifier) ? "{$specifier}/" : '' ),
      round(microtime(true)*1000),
      substr($file_sum, 0, 5),
      $file_ext
    );

    if( move_uploaded_file($file['tmp_name'], $file_path) )
    {
      return Array(
        $file_path,
        $file['name'],
        $file_sum
      );
    }
    else
      return 3; // File couldn't be uploaded.
  }

  /*
    Fetchs specified range of rows for context $context.

    @parameter
    @return Array
  */
  function collection_fetch($parameters, $context, $items, $where = '')
  {
    global $config;
    global $conn;

    $limit = @validate_natural_num($parameters['limit']);
    $limit = ( $limit > 0 && $limit <= 48 ? $limit : 47);

    $offset = @validate_natural_num($parameters['offset']);
    $direction = ( !@strcmp('ASC', $parameters['direction']) ? '>' : '<' );

    $rows_str = '';
    $where_str = '';

    if( !@empty($items) )
    foreach( $items as $index => $item )
    {
      if( @is_array($item) )
        $rows_str .= "{$item[0]} AS `{$item[1]}`";
      else
        $rows_str .= "`c`.`{$item}` AS `{$item}`";

      if( $index < sizeof($items)-1  )
        $rows_str .= ',';
    }

    if( !@empty($where) )
    foreach( $where as $index => $condition )
    {
      $where_str .= $condition;

      if( $index < sizeof($where)-1 )
        $where_str .= ',';
    }

    $query = mysqli_query($conn, sprintf("SELECT `c`.`ID` as `ID`, `c`.`user_id` AS `user_id`, `u`.`name` AS `creator`%s
      FROM `cl_%s` AS `c` INNER JOIN `cl_users` AS `u` ON `u`.`ID`=`c`.`user_id` WHERE %s%s ORDER BY `c`.`ID` DESC LIMIT %d",

      ( !@empty($rows_str) ? ", {$rows_str}" : '' ),
      $context,

      ( $direction === '>' ?
        sprintf("`c`.`ID`%s%d AND `c`.`ID`>$offset",  ( $offset<49 ? '>' : '<'), ( $offset<49 ? $offset : $offset+49 ) ) :
        sprintf("`c`.`ID`<%d", $offset)
      ),

      ( !@empty($where_str) ? " AND {$where_str}" : '' ),
      $limit)
    );

    if( !$query || mysqli_num_rows($query) === 0 )
    {
      return Array(
        'status' => 0,
        'message'=>'',
        'wall' => Array()
      );
    }

    $wall = Array();
    while( ($row = mysqli_fetch_assoc($query)) )
      array_push($wall, $row);

    return Array(
      'status' => 0,
      'message'=>'',
      'wall' => $wall
    );
  }
