<?php

include 'controls/core.php';

/*
  Processes HTML replacing double brackets with array data, or
  evaluement, then return it.

  @param String $_page
  @paramter Array $_replacements
  @return String
*/
function inject_content($_page, $_replacements = Array())
{
  if( is_array($_replacements) )
  foreach( $_replacements as $_name => $_object )
  {
    if( is_array($_object) )
    {
      eval(sprintf("$%s = unserialize(base64_decode('%s'));",

        str_replace('.', '_', $_name),
        base64_encode(serialize($_object))
      ));

      unset($_replacements[$_name]);
    }
  }

  if( preg_match_all('/(\{{2}|<\?)[ ]*(.*?)[ ]*(\}{2}|\?>)/s', $_page, $_matches) )
  {
    foreach( $_matches[2] as $_index => $_var )
    {
      if( strpos($_matches[0][$_index], '<?') === 0 )
      {
        ob_start();
        eval($_var);
        $_value = ob_get_contents();
        ob_end_clean();
      }
      else
      {
        if( isset($_replacements[$_var]) )
          $_value = $_replacements[$_var];
        else
          $_value = '';
      }

      if ( isset($_value) )
        $_page = str_replace($_matches[0][$_index], $_value, $_page);
    }
  }

  return trim($_page);
}

/**
*  Parses current page context based in URI, and exits if current user permissions
*  doesn't attend to it. If page was invoked internally (e.g. include), return it
*  alias name. Otherwise, returns false.
*
*  @param String $path
*  @return Boolean
*/
function context_parse($path)
{
  global $page_info;

  if( !@in_array(current_user_privilege(), $page_info['permission']) )
    exit;

  if( !@in_array('guest', $page_info['permission']) )
  {
    $result = user_sanitize();
    if( $result['status'] < 0 )
    {
      if( $result['redirect'] )
      {
        if( $_SESSION )
          session_destroy();

        header("Location: {$result['redirect']}");
      }

      return true;
    }
  }

  preg_match('/\/([^\/]+)(?=\.php)/', $path, $match);
  if( @strcmp($_GET['context'], $match[1]) )
    return $match[1];

  return false;
}

/**
*  Parses and returns a HTML file located in root/view/jampa/,
*  applying evaluation by default.
*
*  @param String $name
*  @param Array $parameters
*  @return String
*/
function get_view($name, $parameters = Array())
{
  global $config;

  if( file_exists(($path = "{$config['siteroot']}/view/jampa/{$name}.jampa.php")) )
    return inject_content(@file_get_contents($path), $parameters);
}

/**
*  Builds an Array object containing title, context and priority
*  of pages which current user has access to. If any of this pages throw error,
*  the page will fail to execute.
*
*  @return Array
*/
function make_menu()
{
  global $config;
  global $page_info;

  $my_page_info = $page_info;
  $menu = Array();

  foreach( glob("{$config['siteroot']}/view/*.php") as $page )
  {
    $has_permission = true;

    if( ($context = context_parse($page)) )
      $page_info = include_once($page);

    else
    {
      $page_info = $my_page_info;
      $context = $_GET['context'];
    }

    if( @in_array(current_user_privilege(), $page_info['permission']) && $page_info['priority'] >= 0 )
    {
      $priority = $page_info['priority'];
      while( array_key_exists($priority, $menu) )
        $priority++;

      $menu[$priority] = Array(
        'context' => $context,
        'title' => $page_info['title'],
        'permission' => $page_info['permission'],
        'styles' => ( isset($page_info['styles']) ? $page_info['styles'] : Array() ),
        'align' => ( isset($page_info['align']) ? $page_info['align'] : 'left' )
      );
    }
  }

  ksort($menu);
  return $menu;
}

/**
*  This function is an alias for make_page('default' ...).
*  Returns the parsed view with some additional options.
*
*  @param Array $replacements
*  @param Boolean $with_menu
*  @param Boolean $with_banner
*  @param Boolean $with_bottom
*  @return String
*/
function make_page($replacements, $with_menu=true, $with_banner=true, $with_bottom=true)
{
  global $config;
  global $page_info;

  $options = Array(
    'misc.random' => time(),

    'config.theme' => $config['theme'],
    'config.title' => $config['title'],
    'config.subtitle' => $config['subtitle'],
    'config.important'=> ( !empty($config['important']) ? get_view('important', Array(
      'config.important' => $config['important']
      )) : '' ),

    'head.title' => $page_info['title'],
    'head.styles' => ( !@empty($page_info['styles']) ? $page_info['styles'] : Array()),
    'body.menu' => ( $with_menu ? get_view('menu') : '' ),
    'body.banner' => ( $with_banner ? get_view('banner') : '' ),
    'body.bottom' => ( $with_bottom ? get_view('bottom') : '' )

  );

  return get_view('default', $replacements + $options);
}

function output_compress($buffer)
{
  $search = Array(
    '/\>[^\S ]+/s',
    '/[^\S ]+\</s',
    '/(\s)+/s',
    '/<!--(.|\s)*?-->/'
  );

  $replace = Array(
    '>',
    '<',
    '\\1',
    ''
  );

  $buffer = preg_replace($search, $replace, $buffer);
  return $buffer;
}

ob_start('output_compress');
