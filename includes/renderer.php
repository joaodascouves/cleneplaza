<?php

include 'controls/core.php';

/*
  Processes HTML replacing double brackets with array data, or
  evaluement, then return it.

  @parameter String $page
  @paramter Array $replacements
  @return String
*/
function inject_content($page, $replacements = Array())
{
  if( is_array($replacements) )
  foreach( $replacements as $name => $object )
  {
    if( is_array($object) )
    {
      foreach( $object as $key => $value )
      {
        $delimiter = ( is_string($value) ? "'" : '' );

        eval(sprintf("$%s['%s'] = %s%s%s;",
          str_replace('.', '_', $name),
          secure_str($key),
          $delimiter,
          secure_str($value),
          $delimiter
        ));
      }

      unset($replacements[$name]);
    }
  }

  if( preg_match_all('/\{{2}\?*[ ]*(.*?)[ ]*\}{2}/s', $page, $matches) )
  {
    foreach( $matches[1] as $index => $var )
    {
      if( strpos($matches[0][$index], '{{?') === 0 )
      {
        ob_start();
        eval($var);
        $value = ob_get_contents();
        ob_end_clean();
      }
      else
      {
        if( isset($replacements[$var]) )
          $value = $replacements[$var];
        else
          $value = '';
      }

      if ( isset($value) )
        $page = str_replace($matches[0][$index], $value, $page);
    }
  }

  return trim($page);
}

/*
  Parses current page context based in URI, and exits if current user permissions
  doesn't attend to it. If page was invoked internally (e.g. include), return it
  alias name. Otherwise, returns false.

  @parameter String $path
  @return Boolean
*/
function parse_context($path)
{
  global $page_info;

  if( !@in_array(current_user_privilege(), $page_info['permission']) )
    exit;

  preg_match('/\/([^\/]+)(?=\.php)/', $path, $match);
  if( @strcmp($_GET['context'], $match[1]) )
    return $match[1];

  return false;
}

/*
  Parses and returns a HTML file located in root/view/html/,
  applying evaluation by default.

  @parameter String $name
  @parameter Array $parameters
  @return String
*/
function get_view($name, $parameters = Array())
{
  global $config;
  return inject_content(@file_get_contents("{$config['siteroot']}/view/html/{$name}.html"), $parameters);
}

/*
  Builds an Array object containing title, context and priority
  of pages which current user has access to. If any of this pages throw error,
  the page will fail to execute.

  @return Array
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

    if( ($context = parse_context($page)) )
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

/*
  This function is an alias for make_page('default' ...).
  Returns the parsed view with some additional options.

  @parameter Array $replacements
  @parameter Boolean $with_menu
  @parameter Boolean $with_banner
  @parameter Boolean $with_bottom
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
