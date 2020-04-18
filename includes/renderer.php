<?php

function inject_content($page, $replacements)
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

      $page = str_replace($matches[0][$index], $value, $page);
    }
  }

  return $page;
}

function parse_context($path)
{
  preg_match('/\/([^\/]+)(?=\.php)/', $path, $match);
  if( @strcmp($_GET['context'], $match[1]) )
    return $match[1];
}

function make_menu()
{
  global $config;
  global $page_info;

  $my_page_info = $page_info;
  $menu = "<div id=\"menu\"><table id=\"menuTable\"><tr class=\"menu\">";

  foreach( glob("{$config['siteroot']}/view/*.php") as $page )
  {
    $has_permission = true;

    if( ($context = parse_context($page)) )
    {
      $page_info = include_once($page);
      $title = $page_info['title'];

      if( !@in_array($_SESSION['user_level'], $page_info['permission']) )
        $has_permission = false;
    }
    else
    {
      $title = $my_page_info['title'];
      $context = $_GET['context'];
    }

    if( $has_permission )
      $menu .= "<td class=\"menu\" onclick=\"javascript:location.href = '{$config['siteroot']}/?context=${context}'\">{$title}</td>";
  }

  $menu .= "</tr></table></div>";

  return $menu;
}

function get_view($name)
{
  global $config;
  return @file_get_contents("{$config['siteroot']}/view/html/{$name}.html");
}

function make_page($replacements, $with_menu=false)
{
  global $config;
  $page = get_view('default');

  $options = Array(
    'config.theme' => $config['theme'],
    'config.title' => $config['title'],
    'config.subtitle' => $config['subtitle'],
    'config.important'=> $config['important'],
    'misc.random' => time(),

    'body.topFixed' => ( $with_menu ? make_menu() : "" )

  );

  return inject_content($page, $replacements + $options);
}
