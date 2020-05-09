<?php

  // database connection
  $config['hostname'] = 'localhost';
  $config['username'] = 'clene';
  $config['password'] = 'nolepass';
  $config['database'] = 'clene2';

  // misc
  $config['siteroot'] = dirname('../');
  $config['upload_path'] = 'userfiles/';

  // post settings
  $config['anti_flood_tolerance'] = 0; // 0.3 seconds.
  $config['mirror_unique_expiry'] = 60*60*24*365; // 1 year.
  $config['image_unique_expiry'] = 60*60*24;  // 1 day.

  // mirroring settings
  $config['http_timeout'] = 30;
  $config['mirror_validity_code'] = 'title';

  // security
  $config['salt'] = 'NOLETO';
  $config['public_wall'] = false;
  $config['allowed_exts'] = Array('jpg', 'jpeg', 'png', 'gif');

  include 'config_ex.php';
