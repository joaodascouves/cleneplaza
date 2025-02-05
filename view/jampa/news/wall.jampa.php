<?
  if( !@strcmp('admin', current_user_get()['level']) )
    echo "<a href=\"?context=news&action=submit\">Make single</a><br/><br/>";?>
<div id="news-container">
  <? 
    $collection = news_collection_fetch(Array(
      'offset' => 0,
      'limit' => 0,
      'direction' => 'ASC'
    ))['wall'];

    if( $collection )
    foreach( $collection as $item )
    {
      $opts_str = '';

      if( @is_array($item['opts']) )
      {
        $opts_str = '(';
        $index = 0;

        foreach( $item['opts'] as $name => $action )
        {
          $opts_str .= "<a href=\"?context=moderate&object=news&entry_id={$item['ID']}&action={$action}\">{$action}</a>";

          if( ++$index < sizeof($item['opts']) )
            $opts_str .= '|';
        }

        $opts_str .= ')';
      }

      $post_file = ( !@empty($item['file_path']) ?

        "<div class=\"post-file\">
          <img class=\"post-file-file\" src=\"{$item['file_path']}\">
          <div class=\"post-label-file-name\">
            <a href=\"{$item['file_path']}\">{$item['file_name']}</a>
          </div>
        </div>" : '<br/><br/>');

      echo "<div class=\"post-container\">
        <div class=\"top-right\">
          {$opts_str}
        </div>
        <div class=\"post-title\">{$item['title']}</div>
        <small>
          Posted by <a href=\"?context=profile&profile_id={$item['user_id']}\">{$item['creator']}</a> at {$item['created_at']}
        </small>

        {$post_file}

        <p class=\"post-text\">
          {$item['body']}
        </p>
</div>";
    }
?>
</div>
