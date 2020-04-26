<?php

  //$config = new Array();

  // database connection
  $config['hostname'] = 'localhost';
  $config['username'] = 'clene';
  $config['password'] = 'nolepass';
  $config['database'] = 'clene2';

  //misc
  $config['siteroot'] = dirname('../');
  $config['upload_path'] = 'userfiles/';
  $config['unique_expiry'] = 60*60*24; // time in seconds that unique locker spends to expire

  //security
  $config['salt'] = 'NOLETO';
  $config['public_wall'] = false;
  $config['allowed_exts'] = Array('jpg', 'jpeg', 'png', 'gif');

  include 'config_ex.php';
