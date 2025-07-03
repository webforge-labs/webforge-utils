<?php declare(strict_types=1);

namespace Webforge\Common\System;

/**
 * @covers Webforge\Common\System\Dir
 */
class DirFilesTest extends \PHPUnit\Framework\TestCase
{
    protected $dir;
    protected $absolutePath;
    protected $relativePath;

    protected function setUp(): void
    {
        $this->dir = new Dir(__DIR__ . DIRECTORY_SEPARATOR);

        if (DIRECTORY_SEPARATOR === '\\') {
            $absolutePath = 'D:\\path\\for\\absolute\\';
        } else {
            $this->absolutePath = '/path/for/absolute/';
        }

        $this->relativePath = 'path' . DIRECTORY_SEPARATOR . 'for' . DIRECTORY_SEPARATOR . 'relative' . DIRECTORY_SEPARATOR;
    }

    public function testDirGetFile(): void
    {
        $dir = new Dir(__DIR__ . DIRECTORY_SEPARATOR);
        $file = __FILE__;
        $fname = basename($file);

        //self::assertEquals('D:\www\psc-cms\Umsetzung\base\src\psc\readme.txt',
        //(string) $dir->getFile('readme.txt'));

        self::assertEquals($file, (string) $dir->getFile($fname));
        self::assertEquals($file, (string) $dir->getFile(new File($fname)));
        self::assertEquals($file, (string) $dir->getFile(new File(new Dir('.' . DIRECTORY_SEPARATOR), $fname)));

        /*
          das ist unexpected! ich will aber keinen test auf sowas machen..
          self::assertEquals('D:\www\psc-cms\Umsetzung\base\readme.txt',
                              (string)  $dir->getFile('..\\..\\readme.txt'));
        */

        if (DIRECTORY_SEPARATOR === '\\') {
            self::assertEquals(
                __DIR__ . '\lib\docu\readme.txt',
                (string) $dir->getFile(new File('.\lib\docu\readme.txt'))
            );
            self::assertEquals(
                __DIR__ . '\lib\docu\readme.txt',
                (string) $dir->getFile(new File(new Dir('.\lib\docu\\'), 'readme.txt'))
            );
        } else {
            self::assertEquals(
                __DIR__ . '/lib/docu/readme.txt',
                (string) $dir->getFile(new File('./lib/docu/readme.txt'))
            );
            self::assertEquals(
                __DIR__ . '/lib/docu/readme.txt',
                (string) $dir->getFile(new File(new Dir('./lib/docu/'), 'readme.txt'))
            );
        }

        $absoluteDir = __DIR__ . DIRECTORY_SEPARATOR;
        $this->expectException(\InvalidArgumentException::class);
        $dir->getFile(new File(new Dir($absoluteDir), 'readme.txt'));
    }

    public function testDirgetFiles(): void
    {
        $dir = Dir::factoryTS(__DIR__);

        if ($dir->exists()) {
            $files = $dir->getFiles('php');
            self::assertNotEmpty($files);

            foreach ($files as $file) {
                if (isset($dirone)) {
                    self::assertFalse($dirone === $file->getDirectory(), 'Die Verzeichnisse der dateien von getFiles() müssen kopien des ursprünglichen objektes sein. keine Referenzen');
                    self::assertFalse($dirone === $dir, 'Die Verzeichnisse der dateien von getFiles() müssen kopien des ursprünglichen objektes sein. keine Referenzen');
                }

                $dirone = $file->getDirectory();
                self::assertFalse($file->getDirectory()->isRelative());

                $file->makeRelativeTo($dir);

                self::assertTrue($file->isRelative());
            }
        } else {
            $this->markTestSkipped('ui dev für test nicht da');
        }
    }

    public function testIsEmpty(): void
    {
        $nonEx = Dir::factoryTS(__DIR__)->sub('blablabla/non/existent/');
        self::assertTrue($nonEx->isEmpty());

        $temp = Dir::createTemporary();
        self::assertTrue($temp->isEmpty());

        $f = $temp->getFile('blubb.txt');
        $f->writeContents('wurst');
        self::assertFileExists((string) $f);
        self::assertFalse($temp->isEmpty());
    }
}
