  <?php

  include 'comment_control.php';

  /*
    Alias for context_entry_by_id (includes/controls/core.php).

    @parameters Integer id
    @return Array
  */
  function post_get_by_id($id)
  {
    return context_entry_by_id('posts', $id, Array(
      'file_path',
      'file_name',
      'created_at',
      Array('REPLACE(`c`.`body`, \'\n\', \'<br/>\')', 'body'),
      Array('IFNULL(TRIM(`c`.`title`), `c`.`file_name`)', 'title')
    ));
  }

  /*
    Returns an object containing the specified range of images.

    @param Array parameters
    @return String
  */
  function post_collection_fetch($parameters)
  {
    $collection = collection_fetch($parameters, 'posts', Array(
      'file_path',
      Array('IFNULL(TRIM(`c`.`title`), `c`.`file_name`)', 'label'),
      Array(
        "IFNULL((LENGTH(TRIM(`c`.`body`)) - LENGTH(REPLACE(TRIM(`c`.`body`), ' ', '')))+ROUND(LENGTH(TRIM(`c`.`body`))/LENGTH(TRIM(`c`.`body`))), 0)",
        'stats'
        )
    ));

    foreach( $collection['wall'] as &$item )
    {
      $comments = comment_get_count_by_id('post', $item['ID']);
      $item['stats'] = sprintf("(W:%d|R:%d|I:%d)", $item['stats'], $comments[0], $comments[1]);
    }

    return $collection;
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

    $error = 1;

    if( @empty($parameters['image_file']['name']) )
      return Array(
        'status' => $error++,
        'message'=> 'Image filename can not be empty.'
      );

    if( @strlen(trim(($message = $parameters['message'])))>65535 )
      return Array(
        'status' => $error++,
        'message'=> 'Message can not be larger than 65535 characters.'
      );

    if( @strlen((trim($title = $parameters['title'])))>60 )
      return Array(
        'status' => $error++,
        'message'=> 'Title can not be larger than 60 characters.'
      );

    if( @is_array(($flood_msg = anti_flood_step('posts'))) )
      return $flood_msg;

    $upload_result = parse_and_upload_image($parameters['image_file'], 'posts', 'pending');

    if( $upload_result['status'] !== 0 )
      return $upload_result;

    $query = mysqli_query($conn, sprintf("INSERT INTO `cl_posts` (`user_id`, `file_path`, `file_name`, `file_sum`, `title`, `body`)
      VALUES ('%d', '%s', '%s', '%s', %s, %s)",
      current_user_get()['ID'],
      $upload_result['path'],
      $upload_result['name'],
      $upload_result['sum'],
      ( !@empty($title) ? "'{$title}'" : 'NULL'),
      ( !@empty($message) ? "'{$message}'" : 'NULL')
    ));

    if( (!$query || !mysqli_affected_rows($conn)) )
      return Array(
        'status' => $error++,
        'message'=> 'Couldn\'t insert row in database.'
      );

    return Array(
      'status' => 0,
      'message'=> 'Image posted with ID '. mysqli_insert_id($conn)
    );
  }
