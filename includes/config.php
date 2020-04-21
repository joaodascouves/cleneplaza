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

  //security
  $config['salt'] = 'NOLETO';
  $config['public_wall'] = false;
  $config['allowed_exts'] = Array('jpg', 'jpeg', 'png', 'gif');

  include_once 'config_ex.php';
