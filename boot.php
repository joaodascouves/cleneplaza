<?php

  include 'includes/functions.php';
  include 'includes/database.php';
  include 'includes/renderer.php';

  if( @empty($context = $_GET['context']) )
    header("Location: {$config['siteroot']}/?context=home");

  if( file_exists("./view/{$context}.php") )
  {
    session_start();
    setcookie('noscript', ( no_script() ? 'true' : 'false' ), time()+3600, '/');


    if( !isset($_SESSION['level']))
      $_SESSION['level'] = 'guest';

    include "./view/{$context}.php";
  }

  else
    @include "./view/404.php";
