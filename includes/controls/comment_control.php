<?php

  /*
    Returns a collection of comments.

    @parameter Array $parameters
    @parameter Integer $post_id
    @return Array
  */
  function comment_collection_fetch($parameters, $post_id)
  {
    return collection_fetch($parameters, 'comments', Array(
      'file_path',
      'body',
      'created_at'
    ), Array("`entry_id`={$post_id}"));
  }

  /*
    Inserts a comment in specified context.

    @parameter Array $parameters
    @return Array
  */
  function comment_message_insert($parameters)
  {
    global $conn;

    $error = 1;
    if( @empty(($context = $parameters['context'])) )
      return Array(
        'status' => $error++,
        'message'=> 'Context not present.'
      );

    if( !@in_array($parameters['context'], Array('post', 'mirror')) )
      return Array(
        'status' => $error++,
        'message'=> 'Invalid context.'
      );

    if( @strlen(($message = $parameters['message']))<5 && @empty($parameters['image_file']['name']) )
      return Array(
        'status' => $error++,
        'message'=> 'Message body too short (min. 5 characters).'
      );

    if( @is_array(($flood_msg = anti_flood_step('comments'))) )
      return $flood_msg;

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_%ss` WHERE `ID`=%d", $context, ($entry_id = $parameters['entry_id'])));
    if( !$query || mysqli_num_rows($query)<1 )
      return Array(
        'status' => $error++,
        'message'=> 'Entry is not available in database.'
      );

    if( !@empty($parameters['image_file']['name']) )
    {
      $upload_result = parse_and_upload_image($parameters['image_file'], 'comments');
      if( $upload_result['status'] !== 0 )
        return $upload_result;
    }

    $query = mysqli_query($conn, sprintf("INSERT INTO `cl_comments` (`user_id`, `entry_id`, `context`, `body`, `file_path`,
      `file_name`, `file_sum`) VALUES ('%d', '%d', '%s', '%s', '%s', '%s', '%s')",

      current_user_get()['ID'],
      $entry_id,
      $context,
      $message,
      ( isset($upload_result) ? $upload_result['path'] : ''),
      ( isset($upload_result) ? $upload_result['name'] : ''),
      ( isset($upload_result) ? $upload_result['sum'] : ''))
    );

    if( !$query || mysqli_affected_rows($conn)<1 )
      return Array(
        'status' => $error++,
        'message'=> 'Coudn\'t post comment.'
      );

    return Array(
      'status' => 0,
      'message'=>''
    );
  }
