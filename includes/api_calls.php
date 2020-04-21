<?php

  /*
    Returns an object containing the range of images specified.

    @param Array parameters
    @return String
  */
  function api_wall_collection_get($parameters)
  {
    global $config;
    global $conn;

    $query = mysqli_query($conn, sprintf("SELECT `p`.`ID` AS `ID`, `u`.`user_name` AS `creator`,
      CONCAT('%s', `p`.`status`, '/', `p`.`uploadname`) AS `uploadname`, `p`.`filename` AS `filename` FROM `cl_posts` AS `p`
      INNER JOIN `cl_users` AS `u` ON `u`.`ID`=`p`.`user_id` WHERE `p`.`ID`>%d ORDER BY `p`.`ID` DESC LIMIT %d",
      $config['upload_path'], $parameters['offset'], $parameters['limit']));

    if( mysqli_num_rows($query) == 0 )
      return json_encode(Array(
        'status' => 0,
        'message' => 'There are currently no clenes in the database.',
        'images' => ''
      ));

    $wall = Array();
    while( ($row = mysqli_fetch_assoc($query)) )
      $wall[] = $row;

    return json_encode(Array(
      'status' => 0,
      'message' => '',
      'images' => $wall
    ));
  }

  /*
    Insert an image into the database.

    @param Array parameters
    @param Array files
    @return String
  */
  function api_wall_image_insert($parameters)
  {
    global $config;
    global $conn;

    if( @empty($image_file = $parameters['image_file']) ||
        !@in_array($image_ext = end(explode('/', $image_file['type'])), $config['allowed_exts']) )
    {
      return json_encode(Array(
        'status' => 1,
        'message' => 'Invalid file.'
      ));
    }

    $file_sum = md5_file($image_file['tmp_name']);
    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_posts` WHERE `filename`='%s' OR `sum`='%s'",
      $image_file['name'], $file_sum));

    if( mysqli_num_rows($query)>0 )
      return json_encode(Array(
        'status' => 1,
        'message' => 'Image was already posted.'
      ));

    $upload_name = time() .".{$image_ext}";

    if( move_uploaded_file($image_file['tmp_name'], "{$config['upload_path']}pending/$upload_name") )
    {
      $query = mysqli_query($conn, sprintf("INSERT INTO `cl_posts` (`user_id`, `uploadname`, `filename`, `sum`)
        VALUES ('%d', '%s', '%s', '%s')", $_SESSION['ID'], $upload_name, $image_file['name'], $file_sum));

      if( mysqli_affected_rows($conn)>0 )
        return json_encode(Array(
          'status' => 0,
          'message' => 'Image uploaded.'
        ));

      else
      {
        return json_encode(Array(
          'status' => 1,
          'message' => 'Upload error.'
        ));
      }
    }
  }
