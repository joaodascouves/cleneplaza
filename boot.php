<?php

  include_once 'includes/renderer.php';

  if( @empty($context = $_GET['context']) )
    header("Location: {$config['siteroot']}/?context=home");

  if( file_exists("./view/{$context}.php") )
  {
    session_start();
    $_SESSION['user_level'] = 'guest';

    include "./view/{$context}.php";
  }

  else
    @include "./view/404.php";
