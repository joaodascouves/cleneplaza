<div id="profile-container">
  <dl class="list-form">
    <dt style="display: inline">
      <h3 style="display: inline">
        {{ name }}
      </h3>
      <?
        $current_user = current_user_get();

        if( !isset($_GET['profile_id']) ||
          @validate_natural_num($_GET['profile_id']) == $current_user['ID'] )
        {
          echo sprintf("<a href=\"?context=profile%s&action=edit\">(edit)</a>",
            ( isset($_GET['profile_id']) ? sprintf("&profile_id=%d", $_GET['profile_id']) : '' ));
        }

        if( isset($_GET['profile_id']) && $current_user['ID'] != $_GET['profile_id'] )
        {
          $rights = get_rights_over('users', true);

          if( sizeof($rights)>0 )
          {
            foreach( $rights as $name => $user_action )
              echo "<a href=\"?context=moderate&object=users&entry_id={$_GET['profile_id']}&action=$user_action\">($user_action)</a>";
          }
        }
      ?>
    </dt><br/><br/>
  <dd>
    <div class="profile-file">
      <img class="profile-file-file" src="{{ file_path }}"/><br/>
      <small style="width:100%"><a href="{{ file_path }}">{{ file_name }}</a></small>
    </div>
  </dd>

  <dt>
    About me
  </dt>
  <dd>
    <p class="about">
      {{ about }}
    </p>
  </dd>

  <dt>
    Stats
  </dt>
  <dd>
  <p class="about">
    Posts made: {{ posts_count }}<br/>
    Submited mirrors: 0
  </p>
  </dd>
</dl>
</div>
