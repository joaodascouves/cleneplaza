<?php

  if( !($conn = mysqli_connect($config['hostname'], $config['username'], $config['password'])) )
  {
    echo mysqli_connect_error();
    exit;
  }

  if( !mysqli_select_db($conn, $config['database']) )
  {
    // Rebuilds the database. Dangerous!
    // Enable only if in test environment.
    system("mysql -h{$config['hostname']} -u{$config['username']} -p{$config['password']} < sql/install.sql 2>/dev/null");
    echo 'db reinstalled. please reload the page.';
    exit;
  }

  include 'security.php';
