<?php

$dir = glob(dirname(__FILE__) . '/*');

foreach ($dir as $key => $file) {
  if ($file != dirname(__FILE__) . '/routes.php') {
    require_once($file);
  }
}

