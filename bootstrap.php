<?php

require_once __DIR__.'/vendor/autoload.php';

ini_set('mbstring.internal_encoding', 'UTF-8');

return $GLOBALS['env']['root'] = new \Webforge\Common\System\Dir(__DIR__.DIRECTORY_SEPARATOR);