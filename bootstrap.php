<?php

require_once __DIR__.'/vendor/autoload.php';

if (version_compare('5.6.0', PHP_VERSION) >= 0) {
  ini_set('mbstring.internal_encoding', 'UTF-8');
}

return $GLOBALS['env']['root'] = new \Webforge\Common\System\Dir(__DIR__.DIRECTORY_SEPARATOR);