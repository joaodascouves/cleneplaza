<?php

  include_once 'core.php';

  /*
    Returns an associative array containing information of user with unique-ID
    equal to $id. If ID is inexistent, returns false.

    @parameter Integer $id
    @return Array
  */
  function user_get_by_id($id)
  {
    global $conn;

    $query = mysqli_query($conn, sprintf("SELECT * FROM `cl_users` WHERE `ID`=%d LIMIT 1", intval($id)));
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

    $query = mysqli_query($conn, sprintf("SELECT COUNT(`ID`) AS `posts_count` FROM `cl_posts` WHERE `user_id`=%d", intval($id)));
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

    $user = user_get_by_id($id);

    if( @strlen($parameters['name']>60) )
      $error = "Name field can not have more than 32 characters.";

    else if( @strlen($parameters['about'])>512 )
      $error = "About field can not have more than 512 characters.";

    else
    {
      if( !@empty($_FILES['propic']['name']) )
        $upload_result = parse_and_upload_image($_FILES['propic'], 'users');

      if( @empty($_FILES['propic']['name']) || $upload_result['status'] === 0 )
      {
        $query = mysqli_query($conn, sprintf("UPDATE `cl_users` SET `name`='%s', `about`='%s'%s WHERE `ID`=%d",
          $parameters['name'],
          $parameters['about'],
          ( @is_array($upload_result) ?
            sprintf(", file_path='%s', file_name='%s', file_sum='%s'",
              $upload_result['path'],
              $upload_result['name'],
              $upload_result['sum']
              ) : ''
          ),
          intval($id))
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
        $error = $upload_result['message'];
    }


    return get_view('profile.form', $user + Array('error' => $error));
  }


  /*
    Redirects user if banned or etc.

    @return null
  */
  function user_sanitize()
  {
    $user = current_user_get();

    if( !$user )
    {
      return Array(
        'status' => -1,
        'message'=> 'Access denied.',
        'redirect'=> '?context=login'
      );
    }

    if( $user['status'] === 'inactive' )
    {
      setcookie('message', token_create('inactive'), time()+600, '/');

      return Array(
        'status' => -2,
        'message'=> 'Inactive user.',
        'redirect'=> '?context=login'
      );
    }

    if( $user['status'] === 'banned' )
    {
      setcookie('message', token_create('banned'), time()+600, '/');
      setcookie('reason', $user['reason'], time()+600, '/');
      setcookie('penalty', $user['penalty'], time()+600, '/');

      return Array(
        'status' => -3,
        'message'=> $user['reason'],
        'penalty'=> $user['penalty'],
        'redirect'=> '?context=login'
      );
    }

    if( $user['status'] === 'alerted' )
    {
      return Array(
        'status' => 1,
        'message'=> $user['reason'],
        'penalty'=> $user['penalty']
      );
    }

    return Array(
      'status' => 0
    );
  }
