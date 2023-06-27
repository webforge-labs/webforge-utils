<?php

namespace Webforge\Common;

use Webforge\Common\System\Dir;
use Webforge\Common\System\File;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    // implement this correctly when we have a solution for the Code\Test\Base Class
    public function getFile($name): File
    {
        return $this->getTestDirectory()->getFile($name);
    }

    public function getTestDirectory($sub = '/'): Dir
    {
        return $GLOBALS['env']['root']->sub('tests/files/')->sub($sub);
    }
}
