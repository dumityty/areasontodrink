<?php

$dir = glob(dirname(__FILE__) . '/*');

foreach ($dir as $key => $file) {
  if ($file != dirname(__FILE__) . '/middleware.php') {
    require_once($file);
  }
}
