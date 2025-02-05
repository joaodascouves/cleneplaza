<?
  if( @strcmp('home', $_GET['context']) )
    echo get_view('wall.options');

  else
  {
    include 'includes/controls/saloon.control.php';

    $saloon_list = saloons_get();

    $current_saloon = ( !@empty($_GET['saloon']) ?
      $_GET['saloon'] :
      $saloon_list[0]['alias']
    );

    echo get_view('wall.options', Array('saloon_list' => $saloon_list, 'current_saloon' => Array('alias' => $current_saloon)) );
    echo sprintf("<script type=\"text/javascript\">var saloon='%s'</script>", $current_saloon);
  }
?>
<section>
<? 

  if( !no_script() )
  {
    $upload_box = <<<EOF
    <div id="wall-container">
      <div class="wall-image-container" id="upload_box" style="display: none">
        <div class="wall-image-label wall-image-label-top-left">
          Quick post image
        </div>
        <label for="image_file">
          <img class="wall-image-file" id="wall_image_preview" src="foto.jpg" />
        </label>
        <form action="api.php?context=post&action=image_insert" method="POST" enctype="multipart/form-data">
          <input name="image_file" id="image_file" type="file" accept="image/*" onchange="wall_image_preview_refresh(event)" /><br/>
          <input type="hidden" name="saloon" value="CURRENT_SALOON" />
          <input class="form-button wall-image-send" id="send_file" type="submit" value="Send" disabled />
        </form>
      </div>
    </div>
EOF;

    echo str_replace('CURRENT_SALOON', $current_saloon, $upload_box);
  }
?>
<?
    if( no_script() )
    {
      include 'controls/post.control.php';

      $offset = 0;
      $direction = ( !@strcmp('DESC', $_GET['direction']) ? 'DESC' : 'ASC' );

      if( isset($_GET['offset']) )
        $offset = @validate_natural_num($_GET['offset']);

      $wall_collection = post_collection_fetch(Array(
        'offset' => $offset,
        'direction' => $direction,
        'limit' => 48,
        'saloon' => $current_saloon
      ))['wall'];

      if( $wall_collection )
      foreach( $wall_collection as $post )
      {
        $opts_str = '';

        if( @is_array($post['opts']) )
        {
          $opts_str = '(';
          $index = 0;

          foreach( $post['opts'] as $name => $action )
          {
            $opts_str .= "<a href=\"?context=moderate&object=posts&entry_id={$post['ID']}&action={$action}\">{$name}</a>";

            if( ++$index < sizeof($post['opts']) )
              $opts_str .= '|';
          }

          $opts_str .= ')';
        }

        echo "<div class=\"wall-image-container\">
          <img class=\"wall-image-file\" src=\"{$post['file_path']}\">
          <div class=\"wall-image-label wall-image-label-top-left\">
            <a href=\"?context=post&entry_id={$post['ID']}&action=show\">#{$post['ID']}</a>
            <!-- <div class=\"wall-image-label wall-image-label-creator\"> -->
            <a href=\"?context=profile&profile_id={$post['user_id']}\">~{$post['creator']}</a>
            <!-- </div> -->
          </div>
          <div class=\"wall-image-label wall-image-label-top-right\">{$post['stats']}{$opts_str}</div>
          <div class=\"wall-image-label wall-image-label-file_name\">{$post['label']}</div>
        </div>";
      }
    }
?>
</section>

<div id="wall-pagination-container">
   <?
    if( no_script() )
    {
      $current_page = ( isset($_GET['page']) ? @validate_natural_num($_GET['page']) : 0 );

      if( sizeof($wall_collection) == 48 )
      {
        echo sprintf("<a id=\"next_button\" href=\"?context=home&offset=%d&direction=DESC&page=%d\">Next</a>",
          $wall_collection[47]['ID'],
          $current_page + 1
          );
      }

      if( $current_page > 0 && $wall_collection )
      {
        echo sprintf("<a id=\"prev_button\" href=\"?context=home%s\">Previous</a>",
          ( $current_page > 1 ?
            sprintf("&offset=%d&direction=ASC&page=%d",
              $wall_collection[0]['ID'],
              $current_page - 1
              ) : ''
          )
          );
      }
    }
?>
</div>
