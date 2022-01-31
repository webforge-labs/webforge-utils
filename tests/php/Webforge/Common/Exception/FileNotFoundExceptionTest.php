<?php

namespace Webforge\Common\Exception;

use Webforge\Common\System\File;

class FileNotFoundExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testCanBeConstructedFromMissingFile()
    {
        $this->assertInstanceOf('Webforge\\Common\Exception', $exception = FileNotFoundException::fromFile($file = new File('this/does/not/exist')));

        $this->assertSame($file, $exception->getNotFoundFile());
    }
}
