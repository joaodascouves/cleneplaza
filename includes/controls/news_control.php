<?php

  /*
    Alias for collection_fetch (includes/controls/core.php).

    @parameter Array $parameters
    return Array
  */
  function news_collection_fetch($parameters)
  {
    return collection_fetch($parameters, 'news', Array(
      'title',
      'body',
      'file_name',
      'file_path',
      'created_at'
    ));
  }

  /*
    Insert an announcement into the database.

    @parameter Array $parameters
    @return Array
  */
  function news_insert($parameters)
  {
    global $config;
    global $conn;

    $error = 1;

    if( @strlen(($message = trim($parameters['message'])))<20 )
      return Array(
        'status' => $error++,
        'message'=> 'Body has to be larger than 20 characters.'
      );

    if( @strlen(($title = trim($parameters['title'])))<5 )
      return Array(
        'status' => $error++,
        'message'=> 'Title has to be larger than 5 characters.'
      );

    if( @strlen($message)>65535 )
      return Array(
        'status' => $error++,
        'message'=> 'Body can not be larger than 65535 characters.'
      );

    if( @strlen($title)>60 )
      return Array(
        'status' => $error++,
        'message'=> 'Title can not be largher than 60 characters.'
      );

    if( (!@empty($parameters['image_file']['name'])) )
    {
      $upload_result = parse_and_upload_image($parameters['image_file'], 'news');

      if( $upload_result['status'] !== 0 )
        return $upload_result;
    }

    $query = mysqli_query($conn, sprintf("INSERT INTO `cl_news` (`user_id`, `title`, `body`, `file_path`, `file_name`, `file_sum`)
      VALUES ('%d', '%s', '%s', %s, %s, %s)",

      current_user_get()['ID'],
      $title,
      $message,
      ( isset($upload_result) ? "'{$upload_result['path']}'" : 'NULL' ),
      ( isset($upload_result) ? "'{$upload_result['name']}'" : 'NULL' ),
      ( isset($upload_result) ? "'{$upload_result['sum']}'" : 'NULL' )
    ));

    if( (!$query | !mysqli_affected_rows($conn)) )
      return Array(
        'status' => $error++,
        'message'=> 'Announcement couldn\'t be inserted.'
      );

    return Array(
      'status' => 0,
      'message'=> 'Announcement inserted with ID '. mysqli_insert_id($conn) .'.'
    );
  }
