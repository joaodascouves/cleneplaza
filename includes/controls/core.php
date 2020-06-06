<?php

  include 'user.control.php';
  include 'moderate.control.php';
  include 'coins.control.php';

  /**
  *  Returns user object stored in /includes/controls/login.php.
  *
  *  @return Array
  */
  function current_user_get()
  {
    if( !isset($_SESSION) )
      session_start();

    if( !isset($_SESSION['user_id']) )
      return;

    $user = user_get_by_id($_SESSION['user_id']);
    return $user;
  }

  /**
  *  Alias for current_user_get()['level'].
  *  If level is empty, returns 'guest'.
  *
  *  @return String
  */
  function current_user_privilege()
  {
    $level = ( !@empty(current_user_get()['level']) ? current_user_get()['level'] : 'guest' );
    return $level;
  }

  /**
  *  Returns a boolean indicating if scripts are either enabled
  *  our disabled.
  *
  *  @return Boolean
  */
  function no_script()
  {
    return ( !@strcmp('true', $_GET['noscript']) ||
      @strcmp('false', $_GET['noscript']) && !@strcmp('true', $_COOKIE['noscript']) );
  }


  /**
  *  Parse $file applying proper filters. If the file passes it all,
  *  it is uploaded on the specific context folder, than given a upload name and a checkfile_sum.
  *  Otherwise, an integer is returned. $specifier is a variable used merely for convenience
  *  with the post_image_insert function (includes/control/post.control.php).
  *
  *  @param Array $file
  *  @param String $context
  *  @param String $specifier
  *  @return Array
  */
  function parse_and_upload_image($file, $context, $specifier='')
 {
   global $config;
   global $conn;

   $error = 1;

   if( !exif_imagetype($file['tmp_name']) || !@in_array($file_ext = end(explode('/', $file['type'])), $config['allowed_exts']) )
     return Array(
       'status' => $error++,
       'message'=> 'Invalid file type.'
     );

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
     return Array(
       'status' => $error++,
       'message'=> 'Unknown upload error.'
     );

   if( mysqli_num_rows($query)>0 )
     return Array(
       'status' => $error++,
       'message'=> 'Image was recently posted.'
     );

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
        'status' => 0,
        'path' => $file_path,
        'name' => $file['name'],
        'sum' => $file_sum
      );
    }
    else
      return Array(
        'status' => 3,
        'message'=> 'File couldn\'t be uploaded.'
      );
  }

  /*
    Returns an associative array containing context entry information,
    or returns false.

    @parameter String $context
    @parameter Integer id
    @return Array
  */
  function context_entry_by_id($context, $id, $items = Array(), $with_opts=true)
  {
    global $conn;
    global $config;

    $rows_str = '';

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

    $query = mysqli_query($conn, sprintf("SELECT `c`.`ID` AS `ID`, `c`.`user_id` AS `user_id`, `u`.`name` AS `creator`
      %s FROM `cl_%s` AS `c` INNER JOIN `cl_users` AS `u` ON `u`.`ID`=`c`.`user_id` WHERE `c`.`status`!= 'rejected' AND `c`.`ID`=%s",

      ( !@empty($rows_str) ? ", {$rows_str}" : '' ),
      $context,
      intval($id)));

    if( $query && mysqli_num_rows($query)>0 )
    {
      $post = mysqli_fetch_assoc($query);
      $user = current_user_get();

      if( $with_opts )
      {
        $rights = get_rights_over($context, $post['user_id'] === $user['ID']);

        if( sizeof($rights)>0 )
        {
          foreach( $rights as $right )
            $post['opts'][strtoupper(substr($right, 0, 1))] = $right;
        }
      }

      return $post;
    }
  }

  /**
  *  Virtually deletes a context entry from database.
  *
  *  @param Array $parameters
  *  @param String $context
  *  @return Array
  */
  function context_delete_by_id($parameters, $context)
  {
    global $conn;

    if( ($entry_id = @validate_natural_num($parameters['ID'])) === 0 )
      return Array(
        'status' => 1,
        'message'=> 'Entry ID must be set and valid.'
      );

    $mod_id = @validate_natural_num($parameters['mod_id']);

    $query = mysqli_query($conn, sprintf("UPDATE `cl_%s` SET `status`='rejected', `mod_id`=%d WHERE `ID`=%d",

      $context,
      $mod_id,
      $entry_id)
    );

    if( @mysqli_affected_rows($conn) === 0 )
      return Array(
        'status' => 2,
        'message'=> 'Couldn\'t delete entry from database.'
      );

    $trimmed_context = substr($context, 0, -1);

    $query = mysqli_query($conn, sprintf("SELECT `amount` FROM `cl_coins` WHERE
      `opcode`='credit' AND `subject_context`='%s' AND `subject_id`=%d",
      
        $trimmed_context,
        $entry_id));

    $amount = $query ? mysqli_fetch_array($query)[0] : 0;

    if( $amount>0 )
    {
      if( ($result = debit_current_user($amount, $entry_id, $trimmed_context, 'entry deleted'))['status'] )
        return $result;
    }

    return Array(
      'status' => 0,
      'message'=> 'Entry deleted from database.'
    );
  }

  /**
  *  Fetchs specified range of rows for context $context.
  *
  *  @param Array $parameters
  *  @param String $context
  *  @param Array $items
  *  @param Array $where
  *  @return Array
  */
  function collection_fetch($parameters, $context, $items, $where = Array(), $with_opts=true)
  {
    global $config;
    global $conn;

    $balance = current_user_balance()['amount'];

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
        $where_str .= ' AND ';
    }

    $query = mysqli_query($conn, sprintf("SELECT `c`.`ID` as `ID`, `c`.`user_id` AS `user_id`, `u`.`name` AS `creator`%s
      FROM `cl_%s` AS `c` INNER JOIN `cl_users` AS `u` ON `u`.`ID`=`c`.`user_id` WHERE `c`.`status`!='rejected' AND %s%s ORDER BY `c`.`ID` DESC LIMIT %d",

      ( !@empty($rows_str) ? ", {$rows_str}" : '' ),
      $context,

      ( $direction === '>' ?
        sprintf("`c`.`ID`%s%d AND `c`.`ID`>$offset",  ( $offset<49 ? '>' : '<'), ( $offset<49 ? $offset : $offset+49 ) ) :
        sprintf("`c`.`ID`<%d", $offset)
      ),

      ( !@empty($where_str) ? " AND {$where_str}" : '' ),
      $limit)
    );

    if( !$query || @mysqli_num_rows($query) === 0 )
    {
      return Array(
        'status' => 0,
        'message'=>'',
        'wall' => Array(),
        'balance' => $balance
      );
    }

    $wall = Array();
    $user = current_user_get();

    while( ($row = mysqli_fetch_assoc($query)) )
    {
      if( $with_opts )
      {
        $rights = get_rights_over($context, $row['user_id'] === $user['ID']);

        if( sizeof($rights)>0 )
        {
          foreach( $rights as $right )
            $row['opts'][strtoupper(substr($right, 0, 1))] = $right;
        }
      }

      array_push($wall, $row);
    }

    return Array(
      'status' => 0,
      'message'=>'',
      'wall' => $wall,
      'balance' => $balance
    );
  }

  /**
  *  Checks if current user has already created a entry within x seconds.
  *
  *  @param String context
  *  @return Array
  */
  function anti_flood_step($context)
  {
    global $config;
    global $conn;

    $query = mysqli_query($conn, sprintf("SELECT UNIX_TIMESTAMP(`created_at`) AS `created_at` FROM `cl_%s`
      WHERE `user_id`=%d ORDER BY `created_at` DESC LIMIT 1",

      $context,
      current_user_get()['ID']
    ));

    if( !$query || mysqli_num_rows($query) === 0 )
      return false;

    $tolerance = time() - $config['anti_flood_tolerance'];
    $row = mysqli_fetch_assoc($query);

    if( $row['created_at']>$tolerance )
    {
      return Array(
        'status' => 100,
        'message'=> 'Please wait '. intval($row['created_at'] - $tolerance) .' seconds before posting.'
      );
    }
  }
