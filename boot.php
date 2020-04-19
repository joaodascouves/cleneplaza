<?php

  include 'includes/functions.php';
  include 'includes/database.php';
  include 'includes/renderer.php';

  if( @empty($context = $_GET['context']) )
    header("Location: {$config['siteroot']}/?context=home");

  if( file_exists("./view/{$context}.php") )
  {
    session_start();
    if( !isset($_SESSION['user_level']))
      $_SESSION['user_level'] = 'guest';

    include "./view/{$context}.php";
  }

  else
    @include "./view/404.php";
