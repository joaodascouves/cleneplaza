<div class="post-container post-container-main">
  <div class="post-title">{{ title }}</div>
  <div class="top-right">
<?

  if( isset($opts) )
  {
    echo '(';
    $index = 0;

    foreach( $opts as $name => $action )
    {
      echo "<a href=\"?context=moderate&object=posts&entry_id={$_GET['entry_id']}&action={$action}\">{$action}</a>";

      if( ++$index < sizeof($opts) )
        echo '|';
    }

    echo ')';
  }

?>
  </div>
  <small>
    Uploaded by <a href="?context=profile&profile_id={{ user_id }}">{{ creator }}</a> at {{ created_at }}
  </small>

  <div class="post-file">
    <img class="post-main-file" src="{{ file_path }}"/><br/>
    <small style="width:100%"><a href="{{ file_path }}">{{ file_name }}</a></small>
  </div>

  <p class="post-text">
    {{ body }}
  </p>
</div>
<? echo get_view('comment/wall'); ?>
<? echo get_view('comment/insert'); ?>
