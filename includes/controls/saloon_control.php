<?php

  /*
    Returns an Array containg saloons info.

    @return Array
  */
  function saloons_get()
  {
    global $conn;

    $query = mysqli_query($conn, sprintf("SELECT `ID`, `alias`, `name` FROM `cl_saloons`
      WHERE `visible`=1"));

    if( !$query || @mysqli_num_rows($query) === 0 )
      return Array();

    $saloons = Array();
    while( ($row = mysqli_fetch_assoc($query)) )
      array_push($saloons, $row);

    return $saloons;
  }

  function saloon_rules($alias)
  {
    global $conn;

    $query = mysqli_query($conn, sprintf("SELECT `rules` FROM `cl_saloons`
      WHERE `alias`='%s'", $alias));

    if( !$query || @mysqli_num_rows($query) === 0 )
      return Array(
        'status' => 1,
        'message'=> 'Rules unavailable.'
      );

    return Array(
      'status' => 0,
      'message'=> mysqli_fetch_array($query)[0]
    );
  }
