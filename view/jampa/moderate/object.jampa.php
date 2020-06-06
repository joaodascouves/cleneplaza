<?
  $object = ( isset($parameters['object']) ? $parameters['object'] : '' );
  $action = ( isset($parameters['action']) ? $parameters['action'] : '' );
  $entry_id = ( @validate_natural_num($parameters['entry_id']) !== 0 ? $parameters['entry_id'] : '' );

  $creator = ( isset($entry['creator']) ? $entry['creator'] : '' );
  $user_id = ( @validate_natural_num($entry['user_id']) !== 0 ? $entry['user_id'] : '' );

?>
<div class="">
  <p>
    You are about to <? echo $action; ?> ID
    <a href="?context=<? echo $object; ?>&entry_id=<? echo $entry_id; ?>&action=show">
      #<? echo $entry_id; ?>
    </a> from <? echo $object; ?>.<br/>
    This action will be logged and can not be unmade.
  </p>
  <p>
    Creator: <b><? echo $creator; ?></b>
    <a href="?context=profile&profile_id=<? echo $user_id; ?>">(profile)</a><?

      $rights = get_rights_over('users', true);

      if( sizeof($rights)>0 )
      {
        foreach( $rights as $name => $user_action )
          echo "<a href=\"?context=moderate&object=users&entry_id={$user_id}&action=$user_action\">($user_action)</a>";
      }

  ?>
  </p>
  <p>
  <? 

      $rights = get_rights_over($object, true);

      if( sizeof($rights)>1 )
        echo "Other actions:";

      foreach( $rights as $other_action )
      {
        if( @strcmp($action, $other_action) )
          echo "<li><a href=\"?context=moderate&object={$object}&entry_id={$entry_id}&action={$other_action}\">
          {$other_action}</a></li>";
      }
    ?>
  </p>
  <p>
    <form method="POST" action="<? echo $_SERVER['REQUEST_URI']; ?>&confirm=true">
      <input type="hidden" name="object" value="<? echo $object; ?>" />
      <input type="hidden" name="action" value="<? echo $action; ?>" />
      <input type="hidden" name="entry_id" value="<? echo $entry_id; ?>" />
      <input type="submit" class="form-button" value="Confirm" />
    </form>
  </p>
</div>
