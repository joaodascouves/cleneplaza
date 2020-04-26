<?php

  /*
    Returns an associative array containing information of user with unique-ID
    equal to $id. If ID is inexistent, returns false.

    @parameter Integer $id
    @return Array
  */
  function user_get_by_id($id)
  {
    global $conn;

    $query = mysqli_query($conn, sprintf("SELECT * FROM `cl_users` WHERE `ID`=%d LIMIT 1", $id));
    if( mysqli_num_rows($query)>0 )
      return mysqli_fetch_assoc($query);
  }

  /*
    Returns the total posts made by user #id.

    @parameter Integer $id
    @return Array
  */
  function user_posts_count_by_id($id)
  {
    global $conn;

    $query = mysqli_query($conn, sprintf("SELECT COUNT(`ID`) AS `posts_count` FROM `cl_posts` WHERE `user_id`=%d", $id));
    if( mysqli_num_rows($query)>0 )
      return mysqli_fetch_assoc($query)['posts_count'];
  }

  function user_update(&$parameters)
  {
    global $config;
    global $conn;

    if( !($id = @validate_natural_num($parameters['ID'])) || $id<0 )
      $error = "Invalid user ID.";

    else if( current_user_get()['ID'] != $id && @strcmp('admin', current_user_get()['level']) !== 0 )
      $error = "You have no rights over specified user ID.";

    else if( @strlen($parameters['name']>60) )
      $error = "Name field can not have more than 32 characters.";

    else if( @strlen($parameters['about'])>512 )
      $error = "About field can not have more than 512 characters.";

    else
    {
      $user = user_get_by_id($id);

      if( !@empty($_FILES['propic']['name']) )
        $upload_result = parse_and_upload_image($_FILES['propic'], 'users');

      if( @empty($_FILES['propic']['name']) || @is_array($upload_result) )
      {
        $query = mysqli_query($conn, sprintf("UPDATE `cl_users` SET `name`='%s', `about`='%s'%s WHERE `ID`=%d",
          $parameters['name'],
          $parameters['about'],
          ( @is_array($upload_result) ?
            sprintf(", file_path='%s', file_name='%s', file_sum='%s'",
              $upload_result[0],
              $upload_result[1],
              $upload_result[2]
              ) : ''
          ),
          $id)
        );

        if( mysqli_affected_rows($conn)>0 )
        {
          $user = user_get_by_id($id);

          if( $id == current_user_get()['ID'] )
            $_SESSION['user'] = $user;

          $error = "User updated sucessfully.";
        }
        else
          $error = "User was not updated.";
      }
      else
      {
        switch( $upload_result )
        {
          case 1: $error = 'Invalid file.'; break;
          case 2: $error = 'Profile picture already taken.'; break;
          case 3: $error = 'File coudn\'t be uploaded.'; break;
          default: $error = 'Unknown error.'; break;
        }
      }
    }


    return get_view('profile.form', $user + Array('error' => $error));
  }
