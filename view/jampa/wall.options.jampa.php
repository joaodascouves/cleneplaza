<div id="wall-options-container">
  <nav>
    <ul>
    <?
    if( !no_script() )
    {
      $wall_opts = <<<EOF
<li>
  <input type="checkbox" id="sync_trigger" />
  <label for="sync_trigger">
    Sync
  </label>
</li>
<li>
  <input type="checkbox" id="post_trigger" disabled />
  <label for="post_trigger">
    Post
  </label>
</li>
EOF;

      echo $wall_opts;
    }
    ?>
    <li>
      <a href="<? 

        $saloon_query = ( isset($current_saloon) ? "&saloon={$current_saloon['alias']}" : '' );

        echo sprintf("?context=%s%s&action=submit",
          (!@strcmp('home', $_GET['context'])?'post':$_GET['context']), $saloon_query);

      ?>">Make single</a>
    </li>

    <? 
      if( isset($saloon_list) )
        echo get_view('saloon.form', Array('saloon_list' => $saloon_list, 'current_saloon' => Array('alias' => $current_saloon['alias']) ));
    ?>

    <li class="right-tab">
      <a href="#bottom">Bottom</a>
    </li>
    <?

      if( isset($current_saloon) )
      {
        echo "<li class=\"right-tab\">
          <a href=\"?context=saloon&alias={$current_saloon['alias']}&action=rules\"><b>Rules!</b></a>
          </li>";
      }

      if( !@strcmp('mirror', $_GET['context']) )
      {
        echo "<li class=\"right-tab\">
          <a href=\"?context=mirror&action=rules\"><b>Rules!</b></a>
          </li>";
      }
    ?>

    </ul>
  </nav>
</div>
