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
