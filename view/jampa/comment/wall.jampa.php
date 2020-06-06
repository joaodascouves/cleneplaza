<?
  include_once 'includes/controls/comment.control.php';

  $context = ( !@empty($_GET['context']) ? $_GET['context'] : false );
  $entry_id = (!@empty($_GET['entry_id'])? $_GET['entry_id'] : false );
?>
<div id="comments-wall-container">
  <div id="comment-prelude">
    Replies: <div id="comment-count" style="display: inline"><?
      echo ( no_script() ? comment_get_count_by_id($context, $entry_id)[0] : '0');
    ?></div>
  </div>
<? 

  if( no_script() && $context && $entry_id )
  {
    $comment_collection = comment_collection_fetch(Array(
      'offset' => 0,
      'direction'=> 'ASC',
      'limit' => 0,
      'context' => $_GET['context'],
      'entry_id' => $_GET['entry_id']
    ))['wall'];

    if( $comment_collection )
    {
      $comment_collection = array_reverse($comment_collection);

      foreach( $comment_collection as $comment )
      {

        $opts_str = '';

        if( @is_array($comment['opts']) )
        {
          $opts_str = '(';
          $index = 0;

          foreach( $comment['opts'] as $name => $action )
          {
            $opts_str .= "<a href=\"?context=moderate&object=comments&entry_id={$comment['ID']}&action={$action}\">{$name}</a>";

            if( ++$index < sizeof($comment['opts']) )
              $opts_str .= '|';
          }

          $opts_str .= ')';
        }

        $post_file = ( !@empty($comment['file_path']) ?

          "<div class=\"post-file\">
            <img class=\"post-file-file\" src=\"{$comment['file_path']}\">
            <div class=\"post-label-file-name\">
              <a href=\"{$comment['file_path']}\">{$comment['file_name']}</a>
            </div>
          </div>" : '<br/><br/>');

        $label = ( !@empty($comment['label']) ?
          "<div class=\"post-label post-label-label\">
            {$comment['label']}
          </div>" : '');

        echo "<div class=\"comment-container post-container\">
          <div class=\"post-label post-label-top-left\">
            <a>#{$comment['ID']}</a>
            <a href=\"?context=profile&profile_id={$comment['user_id']}\"> ~{$comment['creator']}</a>
          </div>
          {$post_file}
          {$label}
          <div class=\"post-label post-label-top-right\">
            {$comment['stats']}{$opts_str}
          </div>
        </div>";
      }
    }
  }

?>
</div>
