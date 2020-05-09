<?php

  function validate_natural_num($number)
  {
    return ( @intval($number) >= 0 ?
      $number : intval(0) );
  }
