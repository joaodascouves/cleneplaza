<?php

  static $__mod_roles = Array(

    'admin'   =>  Array(

      'news'    => 'delete',
      'posts'   => 'delete,aprove',
      'mirrors' => 'delete,aprove',
      'comments'=> 'delete',
      'users'   => 'ban,hide'

    ),

    'mod'   =>  Array(

      'posts'   => 'delete,aprove',
      'mirrors' => 'delete,aprove',
      'comments'=> 'delete',
      'users'   => 'ban,hide'

    ),

    'user'    => Array(

      'posts'     => 'delete',
      'comments'  => 'delete'

    )
  );

  /**
  *  Returns an array containing user rights over object.
  *
  *  @param String $context
  *  @return Array
  */
  function get_rights_over($context, $owned)
  {
    global $__mod_roles;

    if( @empty(($level = current_user_get()['level'])) )
      return Array();

    if( @empty(($rules = $__mod_roles[$level][$context])) )
      return Array();

    if( @in_array($level, Array('user')) && !$owned )
      return Array();

    return explode(',', $rules);
  }

  /**
   * @param Array $parameters
   * @return Array
   */
  function moderate_panel($parameters)
  {
    if( @empty(($object = $parameters['object'])) )
      return Array(
        'status' => 1,
        'message'=> 'Object type must be set.'
      );

    if( @empty(($action = $parameters['action'])) )
      return Array(
        'status' => 2,
        'message'=> 'Action must be set.'
      );

    if( ($entry_id = @validate_natural_num($parameters['entry_id'])) === 0 )
      return Array(
        'status' => 3,
        'message'=> 'Entry ID must be set and valid.'
      );

    $user_flag = strcmp('users', $object);

    $entry = ( !$user_flag ?

      user_get_by_id($entry_id) :
      context_entry_by_id($object, $entry_id, false)
    );

    if( !$entry )
      return Array(
        'status' => 4,
        'message'=> 'Entry not found on database.'
      );

    $user = current_user_get();
    if( !@in_array($action, get_rights_over($object, ( !$user_flag ? true : $user['ID'] === $entry['user_id']))) )
      return Array(
        'status' => 5,
        'message'=> 'Access to specified action has been denied.'
      );

    unset($entry['opts']);

    return Array(
      'status' => 0,
      'user_flag' => $user_flag,
      'parameters'=> $parameters,
      'entry' => $entry,
      'current_user' => $user
    );
  }

  function moderate_action($parameters)
  {
    $result = moderate_panel(Array(
      'object' => ($object = $parameters['object']),
      'action' => ($action = $parameters['action']),
      'entry_id' => ($entry_id = $parameters['entry_id'])
    ));

    if( $result['status'] !== 0 )
      return $result;

    switch( $action )
    {
      case 'delete':
      $result = context_delete_by_id(Array('ID' => $entry_id, 'mod_id' => $result['current_user']['ID']), $object);
      break;

      default:
      $result = Array(
        'status' => 101,
        'message'=> 'Unexistent action.'
      );
      break;
    }

    if( isset($result) )
      return $result;
  }
