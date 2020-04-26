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
      `p`.`file_path` AS `file_path`, `p`.`file_name` AS `file_name`, `p`.`created_at`,
      `p`.`body` AS `message` FROM `cl_posts` AS `p` INNER JOIN `cl_users` AS `u` ON `u`.`ID`=`p`.`user_id` WHERE `p`.`ID`=%s",
      $id));

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
      `p`.`file_path` AS `file_path`, `p`.`file_name` AS `file_name`,
      (LENGTH(TRIM(`p`.`body`)) - LENGTH(REPLACE(TRIM(`p`.`body`), ' ', ''))) + ROUND(LENGTH(TRIM(`p`.`body`))/LENGTH(TRIM(`p`.`body`)))
      AS `words_count` FROM `cl_posts` AS `p` INNER JOIN `cl_users` AS `u` ON `u`.`ID`=`p`.`user_id` %s ORDER BY `p`.`ID` DESC LIMIT %d",

      ( $direction === '>' ?
        sprintf("WHERE `p`.`ID`%s%d AND `p`.`ID`>$offset",  ( $offset<49 ? '>' : '<'), ( $offset<49 ? $offset : $offset+49 ) ) :
        sprintf("WHERE `p`.`ID`<%d", $offset)
      ),

      $limit
      ));

    if( !$query || mysqli_num_rows($query) == 0 )
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

    if( @empty($parameters['image_file']['name']) )
      return Array(
        'status' => 6,
        'message'=> 'Image filename can not be empty.'
      );

    $upload_result = parse_and_upload_image($parameters['image_file'], 'posts', 'pending');
    if( !is_array($upload_result) )
    {
      switch( $upload_result )
      {
        case 1: $error = 'Invalid file.'; break;
        case 2: $error = 'Image was posted recently.'; break;
        case 3: $error = 'File coudn\'t be uploaded.'; break;
        default: $error = 'Unknown error.'; break;
      }

      return Array(
        'status' => $upload_result,
        'message'=> $error
      );
    }

    $message = ( !@empty($parameters['message']) ? $parameters['message'] : '');

    $query = mysqli_query($conn, sprintf("INSERT INTO `cl_posts` (`user_id`, `file_path`, `file_name`, `file_sum`, `body`)
      VALUES ('%d', '%s', '%s', '%s', '%s')",
      current_user_get()['ID'],
      $upload_result[0],
      $upload_result[1],
      $upload_result[2],
      $message)
    );

    if( (!$query || !mysqli_affected_rows($conn)) )
      return Array(
        'status' => 5,
        'message'=> 'Couldn\'t insert row in database.'
      );

    return Array(
      'status' => 0,
      'message'=> 'Image posted with ID '. mysqli_insert_id($conn)
    );
  }
