<div class="post-container post-container-main mirror-content">
  <div style="display: inline-block; margin-right: 20px">
    URL: {{ url }}
  </div>
  <div style="display: inline-block">
    IP: {{ ip }}
  </div>
  <div class="top-right">
<? 

  if( isset($opts) )
  {
    echo '(';
    $index = 0;

    foreach( $opts as $name => $action )
    {
      echo "<a href=\"?context=moderate&object=mirrors&entry_id={$_GET['entry_id']}&action={$action}\">{$action}</a>";

      if( ++$index < sizeof($opts) )
        echo '|';
    }

    echo ')';
  }

?>
  </div>
  <br/>

  <small>
    Uploaded by <a href="?context=profile&profile_id={{ user_id }}">{{ creator }}</a> at {{ created_at }}
  </small>

  <div class="mirror-iframe">
    <iframe src="{{ fullpage_path }}"></iframe>
  </div>

  <p class="post-text">
    {{ message }}
  </p>
</div>
<? echo get_view('comment/wall'); ?>
<? echo get_view('comment/insert'); ?>
