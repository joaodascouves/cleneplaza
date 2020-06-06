<li>
  <form method="GET" action="<? echo $_SERVER['REQUEST_URI']; ?>" id="saloon_change">
    <input type="hidden" name="context" value="<? echo $_GET['context']; ?>" />
    <label for="saloon">Saloon</label>
    <select id="saloon" name="saloon" form="saloon_change">
      <?
        if( $saloon_list )
        foreach( $saloon_list as $saloon )
          echo sprintf("<option value=\"%s\"%s>%s</option>",
            $saloon['alias'],
            ( $saloon['alias'] === $current_saloon['alias'] ? ' selected' : '' ),
            $saloon['name']);

      ?>
    </select>
    <input type="submit" class="simpletext" value="Go!" />
  </form>
</li>
