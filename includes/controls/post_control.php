  <?php

  /*
    Returns an associative array containing post information,
    or returns false.

    @parameters Integer id
    @return Array
  */
  function post_get_by_id($id)
  {
    global $conn;
    global $config;

    $query = mysqli_query($conn, sprintf("SELECT `p`.`ID` AS `ID`, `p`.`user_id` AS `user_id`, `u`.`name` AS `creator`,
      CONCAT('%s', `p`.`status`, '/', `p`.`file_path`) AS `file_path`, `p`.`file_realname` AS `file_realname`, `p`.`created_at`,
      `p`.`body` AS `message` FROM `cl_posts` AS `p` INNER JOIN `cl_users` AS `u` ON `u`.`ID`=`p`.`user_id` WHERE `p`.`ID`=%s",
      $config['upload_path'], $id));

    if( mysqli_num_rows($query)>0 )
    {
      $post = mysqli_fetch_assoc($query);
      return $post;
    }
  }

  /*
    Returns an object containing the specified range of images.

    @param Array parameters
    @return String
  */
  function post_collection_get($parameters)
  {
    global $config;
    global $conn;

    $limit = @validate_natural_num($parameters['limit']);
    $limit = ( $limit > 0 && $limit <= 48 ? $limit : 47 );

    $offset = @validate_natural_num($parameters['offset']);
    $direction = ( !@strcmp('ASC', $parameters['direction']) ? '>' : '<' );

    $query = mysqli_query($conn, sprintf("SELECT `p`.`ID` AS `ID`, `p`.`user_id` AS `user_id`, `u`.`name` AS `creator`,
      CONCAT('%s', `p`.`status`, '/', `p`.`file_path`) AS `file_path`, `p`.`file_realname` AS `file_realname`,
      (LENGTH(TRIM(`p`.`body`)) - LENGTH(REPLACE(TRIM(`p`.`body`), ' ', ''))) + ROUND(LENGTH(TRIM(`p`.`body`))/LENGTH(TRIM(`p`.`body`)))
      AS `words_count` FROM `cl_posts` AS `p` INNER JOIN `cl_users` AS `u` ON `u`.`ID`=`p`.`user_id` %s ORDER BY `p`.`ID` DESC LIMIT %d",

      $config['upload_path'],

      ( $direction === '>' ?
        sprintf("WHERE `p`.`ID`%s%d AND `p`.`ID`>$offset",  ( $offset<49 ? '>' : '<'), ( $offset<49 ? $offset : $offset+49 ) ) :
        sprintf("WHERE `p`.`ID`<%d", $offset)
      ),

      $limit
      ));

    if( mysqli_num_rows($query) == 0 )
      return Array(
        'status' => 0,
        'message' => '',
        'images' => Array()
      );

    $wall = Array();
    while( ($row = mysqli_fetch_assoc($query)) )
    {
      if( !$row['words_count'] )
        $row['words_count'] = 0;

      $wall[] = $row;
    }

    return Array(
      'status' => 0,
      'message' => '',
      'images' => $wall
    );
  }

  /*
    Inserts an image into the database.

    @param Array parameters
    @param Array files
    @return String
  */
  function post_image_insert($parameters)
  {
    global $config;
    global $conn;

    if( @empty($image_file = $parameters['image_file']) || !exif_imagetype($image_file['tmp_name']) ||
        !@in_array($image_ext = end(explode('/', $image_file['type'])), $config['allowed_exts']) )
    {
      return Array(
        'status' => 1,
        'message' => 'Invalid file.'
      );
    }

    $message = ( !@empty($parameters['message']) ? $parameters['message'] : '');
    $file_sum = md5_file($image_file['tmp_name']);

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_posts` WHERE `file_realname`='%s' OR `file_sum`='%s'",
      secure_str($image_file['name']), $file_sum));

    if( mysqli_num_rows($query)>0 )
      return Array(
        'status' => 1,
        'message' => 'Image was posted recently.'
      );

    $upload_name = sprintf("%s-%s.%s", substr($file_sum, 0, 6), time(), $image_ext);

    if( move_uploaded_file($image_file['tmp_name'], "{$config['upload_path']}pending/$upload_name") )
    {
      $query = mysqli_query($conn, sprintf("INSERT INTO `cl_posts` (`user_id`, `file_path`, `file_realname`, `file_sum`, `body`)
        VALUES ('%d', '%s', '%s', '%s', '%s')", current_user_get()['ID'], $upload_name, $image_file['name'], $file_sum, $message));

      if( mysqli_affected_rows($conn)>0 )
        return Array(
          'status' => 0,
          'message' => 'Image uploaded.'
        );

      else
      {
        return Array(
          'status' => 1,
          'message' => 'Upload error.'
        );
      }
    }
  }
