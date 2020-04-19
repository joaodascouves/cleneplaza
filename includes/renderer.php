<?php

function inject_content($page, $replacements = Array())
{
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
        if( !@empty($replacements[$var]) )
          $value = $replacements[$var];
        else
          $value = "<!-- {$var} -->";
      }

      if ( isset($value) )
        $page = str_replace($matches[0][$index], $value, $page);
    }
  }

  return $page;
}

function parse_context($path)
{
  global $page_info;

  if( !@in_array($_SESSION['user_level'], $page_info['permission']) )
    exit;

  preg_match('/\/([^\/]+)(?=\.php)/', $path, $match);
  if( @strcmp($_GET['context'], $match[1]) )
    return $match[1];
}

function get_view($name, $parameters = Array())
{
  global $config;
  return inject_content(@file_get_contents("{$config['siteroot']}/view/html/{$name}.html"), $parameters);
}

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

    if( @in_array($_SESSION['user_level'], $page_info['permission']) && $page_info['priority'] >= 0 )
    {
      $priority = $page_info['priority'];
      while( array_key_exists($priority, $menu) )
        $priority++;

      $menu[$priority] = Array(
        'context' => $context,
        'title' => $page_info['title'],
        'permission' => $page_info['permission']
      );
    }
  }

  ksort($menu);
  return $menu;
}

function make_page($replacements, $with_menu=true, $with_banner=true)
{
  global $config;
  global $page_info;

  $options = Array(
    'config.theme' => $config['theme'],
    'config.title' => $config['title'],
    'config.subtitle' => $config['subtitle'],
    'config.important'=> $config['important'],
    'misc.random' => time(),

    'head.title' => $page_info['title'],
    'body.banner' => ( $with_banner ? get_view('banner') : '' ),
    'body.topFixed' => ( $with_menu ? get_view('menu') : '' )

  );

  return get_view('default', $replacements + $options);
}
