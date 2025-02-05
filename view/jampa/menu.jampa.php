<div id="menu-container">
  <nav>
    <ul>
        <?
          global $config;

          $menu = make_menu();
          foreach( $menu as $my_page )
          {
            $align = ( !@strcmp('right', $my_page['align']) ? ' class="right-tab"' : '' );

            echo "<li{$align}>
              <a href=\"?context={$my_page['context']}\">${my_page['title']}</a>
            </li>";

          }
        ?>
        <?

          if( @validate_natural_num($_SESSION['user_id'])>0 )
          {
            $balance = current_user_balance()['amount'];

            echo "<li class=\"right-tab\">
              <a href=\"?context=store\"><div id=\"balance\" style=\"display: inline\">${balance}</div> ¢¢</a>
            </li>";
          }

        ?>
    </ul>
  </nav>
</div>
