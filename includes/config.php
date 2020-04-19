<?php

  //$config = new Array();

  // database connection
  $config['hostname'] = 'localhost';
  $config['username'] = 'clene';
  $config['password'] = 'nolepass';
  $config['database'] = 'clene2';

  //misc
  $config['siteroot'] = dirname('../');

  //security
  $config['salt'] = 'NOLETO';
  $config['public_wall'] = false;

  include_once 'config_ex.php';
