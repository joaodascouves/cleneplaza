<?php

  /*
    Returns number of comments and comments with images of entry ID
    in specified context.

    @parameter String $context
    @parameter Integer $id
    @return Array
  */
  function comment_get_count_by_id($context, $id)
  {
    global $conn;

    $query = mysqli_query($conn, sprintf("SELECT `ID`, `file_path` FROM `cl_comments`
      WHERE `context`='%s' AND `status`!='rejected' AND `entry_id`=%d", $context, intval($id)));

    $result = Array(0, 0);
    if( $query || mysqli_num_rows($query)>0 )
    {
      while( ($row = mysqli_fetch_assoc($query)) )
      {
        $result[0]++;

        if( !@empty($row['file_path']) )
          $result[1]++;
      }
    }

    return $result;
  }

  /*
    Returns a collection of comments.

    @parameter Array $parameters
    @parameter Integer $post_id
    @return Array
  */
  function comment_collection_fetch($parameters)
  {
    if( @empty($parameters['entry_id']) )
      return Array(
        'status' => 51,
        'message'=> 'Entry ID not set.'
      );

    if( @empty($parameters['context']) )
      return Array(
        'status' => 52,
        'message'=> 'Context not set.'
      );

    $collection = collection_fetch($parameters, 'comments', Array(
      'file_name',
      'file_path',
      Array('REPLACE(`c`.`body`, \'\n\', \'<br/>\')', 'label'),
      Array('`c`.`created_at`', 'stats')
    ), Array(
      sprintf("`c`.`entry_id`= %d", intval($parameters['entry_id'])),
      sprintf("`c`.`context`= '%s'", $parameters['context'])
    ));

    return $collection;
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

    if( @strlen(trim(($message = $parameters['message'])))<5 && @empty($parameters['image_file']['name']) )
      return Array(
        'status' => $error++,
        'message'=> 'Message body too short (min. 5 characters).'
      );

    if( @is_array(($flood_msg = anti_flood_step('comments'))) )
      return $flood_msg;

    $query = mysqli_query($conn, sprintf("SELECT `ID` FROM `cl_%ss` WHERE `ID`=%d", $context, ($entry_id = intval($parameters['entry_id']))));
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
