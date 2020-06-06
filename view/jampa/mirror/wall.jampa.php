<? echo get_view('wall.options'); ?>
<section>
  <div id="wall-container">
    <div class="wall-image-container" id="upload_box" style="display: none">
      <div class="wall-image-label wall-image-label-top-left">
        Quick submit URL
      </div>
      <form action="api.php?context=mirror&action=check_and_insert" method="POST" enctype="multipart/form-data">
        <input type="text" name="url" id="url-input" class="form" /><br/>
        <input class="form-button wall-image-send" type="submit" value="Send" />
      </form>
    </div>
  </div>
  <?
    if( no_script() )
    {
      //include 'controls/mirror.control.php';

      $offset = 0;
      $direction = ( !@strcmp('DESC', $_GET['direction']) ? 'DESC' : 'ASC' );

      if( isset($_GET['offset']) )
        $offset = @validate_natural_num($_GET['offset']);

      $wall_collection = mirror_collection_fetch(Array(
        'offset' => $offset,
        'direction' => $direction,
        'limit' => 48
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

        // if( @is_array($post['flags']) )
        //   $post['flags'] = implode(',', $post['flags']);

        echo "<div class=\"wall-image-container\">
          <img class=\"wall-image-file\" src=\"{$post['file_path']}\">
          <div class=\"wall-image-label wall-image-label-top-left\">
            <a href=\"?context=mirror&entry_id={$post['ID']}&action=show\">#{$post['ID']}</a>
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
