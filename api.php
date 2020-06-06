<?php

  include 'includes/config.php';
  include 'includes/functions.php';
  include 'includes/database.php';

  include 'includes/api_actions.php';

  function raise_error($errcode, $message)
  {
    return Array(
      'status' => $errcode,
      'message'=> $message
    );
  }

  $encoding_function = 'json_encode';
  if( !@empty($_GET['encoding']) )
  {
    switch( $_GET['encoding'] )
    {
      case 'json':
      break;

      case 'serialize':
      $encoding_function = 'serialize';
      break;

      case 'xml' && function_exists('xmlrpc_encode'):
      $encoding_function = 'xmlrpc_encode';
      break;

      default:
      //http_response_code(500);
      echo $encoding_function(
        raise_error(503, 'Encoding type not available.')
      );
      break;
    }
  }

  $result = user_sanitize();
  if( $result['status']<0 )
  {
    echo $encoding_function(raise_error($result['status'], $result['message']));
    exit;
  }

  if( !@empty($context = $_GET['context']) && !@empty($action = $_GET['action']) )
  {
    $control_file = "includes/controls/{$context}.control.php";
    $action_name = "{$context}_{$action}";

    if( file_exists($control_file) )
    {
      include_once $control_file;

      if( api_action_exists($action_name) && function_exists($action_name) )
      {
        if( in_array($_SERVER['REQUEST_METHOD'], $__api_actions[$action_name][1]) )
        {
          if( !isset($_SESSION) )
            session_start();

          echo $encoding_function($action_name(array_merge($_POST, $_FILES)));
        }
        else
        {
          //http_response_code(500);
          echo $encoding_function(
            raise_error(502, 'Incorrect method for specified action.')
          );
        }
      }
      else
      {
        //http_response_code(500);
        echo $encoding_function(
          raise_error(501, 'Action does not exist.')
        );
      }
    }
    else
    {
      //http_response_code(500);
      echo $encoding_function(
        raise_error(500, 'Context does not exist.')
      );
    }
  }
