<?php

function output_compress($buffer)
{
  $search = Array(
    '/\>[^\S ]+/s',
    '/[^\S ]+\</s',
    '/(\s)+/s',
    '/<!--(.|\s)*?-->/'
  );

  $replace = Array(
    '>',
    '<',
    '\\1',
    ''
  );

  $buffer = preg_replace($search, $replace, $buffer);
  return $buffer;
}
