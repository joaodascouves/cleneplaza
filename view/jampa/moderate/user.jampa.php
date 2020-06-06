<?
  $object = ( isset($parameters['object']) ? $parameters['object'] : '' );
  $action = ( isset($parameters['action']) ? $parameters['action'] : '' );
  $entry_id = ( @validate_natural_num($parameters['entry_id']) !== 0 ? $parameters['entry_id'] : '' );

  $creator = ( isset($entry['name']) ? $entry['name'] : '' );
  $user_id = ( @validate_natural_num($entry['ID']) !== 0 ? $entry['ID'] : '' );

  $email = ( isset($entry['email']) ? $entry['email'] : '' );

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
    Username: <b><? echo $creator; ?></b>
    <a href="?context=profile&profile_id=<? echo $user_id; ?>">(profile)</a><br/>
    E-mail: <? echo $email; ?>
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
