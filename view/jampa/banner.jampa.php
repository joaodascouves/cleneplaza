<? global $config; ?>
<a href="?op=home">
  <div id="banner">
        <h1><? echo $config['title']; ?></h1>
        <? echo $config['subtitle']; ?>

  </div>
</a>
<div class="page-info">
  <div class="page-title">
    <a href="?context=<?

      $context = $_GET['context'];

      if( isset($_SESSION['user_id']) && in_array($context, ['post', 'home']) )
      {
        $GLOBALS['page']['title'] = 'Wall';
        $context = 'home';
      }

      echo $context;
    ?>">
      <? echo $GLOBALS['page']['title']; ?>
    </a>
  </div>

  <?
    if( isset($GLOBALS['page']['subtitle']) )
    {
      echo "<div class=\"page-title\">
        {$GLOBALS['page']['subtitle']}
      </div>";
    }
  ?>
</div>
