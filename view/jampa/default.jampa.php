<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1" />
  <link rel="stylesheet" type="text/css" href="static/css/{{ config.theme }}.css?{{ misc.random }}" />
  <?

    if( isset($head_styles) )
    foreach( $head_styles as $style )
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"static/css/$style.css?". time(). "\" />";

    if( !no_script() )
    {
      global $config;

      echo "<script type=\"text/javascript\" src=\"static/js/main.js?". time() ."\"></script>";
      foreach( $config['user_scripts'] as $script )
        echo "<script type=\"text/javascript\" src=\"{$script}?". time() ."\"></script>";
    }
  ?>
  <title>{{ config.title }} - {{ head.title }}</title>
</head>

<body>
    <?
      echo ( no_script() ?
      "<script type=\"text/javascript\">window.location.href += '&noscript=false';</script>" :
      "<noscript><meta http-equiv=\"refresh\" content=\"0;{$_SERVER['REQUEST_URI']}&noscript=true\" /></noscript>" );
    ?>
    <?
      if( !no_script() )
      {
        $context = ( !@strcmp('home', $_GET['context']) ? 'post' : $_GET['context'] );
        echo "<script>context='{$context}';</script>";
      }
    ?>
  <div id="center-modal">
    <a class="modal-close" href="#/" onclick="parentNode.style.display='none'">Close (×)</a>
  </div>
  <div id="page-container">
    <div id="top-container">
      {{ body.menu }}
      {{ body.banner }}
      {{ config.important }}
    </div>
    <div id="inner-container">
      {{ body.inner }}
    </div>
    <?
      if( @in_array($_GET['context'], Array('home', 'mirror')) && !isset($_GET['entry_id']) )
        echo get_view('bottom');
    ?>
  </div>
</body>
</html>
