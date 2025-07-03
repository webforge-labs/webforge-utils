<?php declare(strict_types=1);

namespace Webforge\Common\Exception;

use Webforge\Common\System\File;

class FileNotFoundExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testCanBeConstructedFromMissingFile(): void
    {
        self::assertInstanceOf(\Webforge\Common\Exception::class, $exception = FileNotFoundException::fromFile($file = new File('this/does/not/exist')));

        self::assertSame($file, $exception->getNotFoundFile());
    }
}
