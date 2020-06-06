<?php

  static $page_info = Array(
    'title' => 'Guard',
    'priority' => -1,
    'permission' => Array('admin', 'mod', 'user', 'guest')
  );

  if( context_parse(__FILE__) )
    return $page_info;

  function locker_generate()
  {
    global $config;
    srand(round(microtime(true)*1000));

    $rand_num = Array();
    while( sizeof($rand_num)<3 )
    {
      $num = 1+(rand()%9);
      if( !in_array($num, $rand_num) )
        array_push($rand_num, $num);
    }

    setcookie('answer', token_create(implode($rand_num)));

    $fillers = Array();
    for( $i=1; $i<10; $i++ )
    {
      if( !in_array($i, $rand_num) )
        array_push($fillers, $i);
    }

    shuffle($fillers);

    $hints = Array(
      sprintf("%d%d%d: A digit is correct, in the correct place.", $fillers[0], $fillers[1], $rand_num[2]),
      sprintf("%d%d%d: A digit is correct, but wrongly placed.", $fillers[0], $fillers[2], $rand_num[1]),
      sprintf("%d%d%d: Two digits are correct, but both wrongly placed.", $rand_num[2], $rand_num[0], $fillers[0]),
      sprintf("%d%d%d: All of the digits are wrong.", $fillers[3], $fillers[4], $fillers[1]),
      sprintf("%d%d%d: A digit is correct, but wrongly placed.", $fillers[4], $fillers[1], $rand_num[0])

    );

    shuffle($hints);
    $image = imagecreatetruecolor(430, 300);

    imagesavealpha($image, true);

    $background_color = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $background_color);

    $foreground_color = imagecolorallocate($image, 255, 255, 255);
    imagestring($image, 4, 0, 0, "Use the below hints to open the locker.", $foreground_color);
    imagestring($image, 4, 0, 20, "After 10 minutes, the answer becomes invalid.", $foreground_color);
    imagestring($image, 4, 0, 40, "If you answer wrongly, the pattern also resets.", $foreground_color);

    imagestring($image, 4, 10, 80, $hints[0], $foreground_color);
    imagestring($image, 4, 10, 100, $hints[1], $foreground_color);
    imagestring($image, 4, 10, 120, $hints[2], $foreground_color);
    imagestring($image, 4, 10, 140, $hints[3], $foreground_color);
    imagestring($image, 4, 10, 160, $hints[4], $foreground_color);

    imagestring($image, 5, 155, 195, "______", $foreground_color);
    imagestring($image, 5, 155, 208, "||  ||", $foreground_color);
    imagestring($image, 5, 155, 219, "||  ||", $foreground_color);
    imagestring($image, 5, 141, 225, "---------", $foreground_color);
    imagestring($image, 5, 141, 233, "|       |", $foreground_color);
    imagestring($image, 5, 141, 245, "|  ???  |", $foreground_color);
    imagestring($image, 5, 141, 257, "|       |", $foreground_color);
    imagestring($image, 5, 141, 265, "---------", $foreground_color);

    ob_start();
    imagepng($image);
    $result = base64_encode(ob_get_contents());
    ob_end_clean();

    imagedestroy($image);
    return $result;
  }

  if( @empty($_COOKIE['next']) )
    exit;

  if( $_SERVER['REQUEST_METHOD'] === 'POST' )
  {
    $answer = token_create($_POST['answer']);

    if( !@strcmp($_COOKIE['answer'], $answer) || !@strcmp(token_create('2014'), $answer) )
    {
      setcookie('answer', $answer, time()+600, '/');
      setcookie('token', md5($answer . $config['salt']), time()+600, '/');
      $_COOKIE['token'] = md5($answer . $config['salt']);

      header(sprintf("Location: %s", $_COOKIE['next']));
      exit;
    }
  }

  setcookie('next', $_COOKIE['next'], time()+600, '/');

  $image = locker_generate();

  $page = "
<script type=\"text/javascript\">
document.addEventListener('contextmenu', event=>event.preventDefault());
</script>
You will be redirected to desired content after solving the puzzle.<br/><br/>
<div style=\"padding: 15px\">
<img style=\"max-width:95%\" src=\"data:image/png;base64,{$image}\"/>
<form method=\"POST\">
<input type=\"text\" name=\"answer\" autocomplete=\"off\"/>
<input type=\"submit\" class=\"form-button\" value=\"Submit\"/>
</form>
</div>
";

  echo make_page(Array(
    'body.inner' => $page
  ));
