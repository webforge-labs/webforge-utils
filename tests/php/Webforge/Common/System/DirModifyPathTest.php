<?php

namespace Webforge\Common\System;

/**
 * @covers Webforge\Common\System\Dir
 */
class DirModifyPathTest extends \PHPUnit\Framework\TestCase
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

    public function testMakeRelativeTo(): void
    {
        $base = Dir::factoryTS(__DIR__);

        $graph = $base->sub('lib/Psc/Graph/');
        $lib = $base->sub('lib/');

        $rel = clone $graph;
        self::assertEquals(
            '.' . DIRECTORY_SEPARATOR . 'Psc' . DIRECTORY_SEPARATOR . 'Graph' . DIRECTORY_SEPARATOR,
            (string) $rel->makeRelativeTo($lib),
            sprintf("making '%s' relative to '%s' failed", $graph, $lib)
        );

        $eq = clone $graph;

        self::assertEquals('.' . DIRECTORY_SEPARATOR, (string) $eq->makeRelativeTo($graph));
    }

    public function testMakeRelativeToException(): void
    {
        $sub = new Dir(__DIR__ . DIRECTORY_SEPARATOR);
        $parent = new Dir(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);

        $norel = clone $parent;
        $this->expectException(\Webforge\Common\System\Exception::class);
        $norel->makeRelativeTo($sub);
    }

    public function testMakeRelativeToMakesDirRelative(): void
    {
        $base = Dir::factoryTS(__DIR__);
        $graph = $base->sub('psc/class/Graph/');
        $psc = $base->sub('psc/');

        $graph->makeRelativeTo($psc);

        self::assertTrue($graph->isRelative());
    }

    public function testWrappedPathsReflectIsWrappedAndSetWrapperAndGetWrapper(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $abs = '/D:/path/does/not';
        } else {
            $abs = '/path/does/not';
        }
        $wrappedPath = 'phar://' . $abs . '/matter/my.phar.gz/i/am/wrapped/';

        $dir = new Dir($wrappedPath);
        self::assertEquals($wrappedPath, (string) $dir, 'path is not parsed correctly');

        self::assertTrue($dir->isWrapped());
        self::assertEquals('phar', $dir->getWrapper());

        $dir->setWrapper('rar');
        self::assertEquals('rar', $dir->getWrapper());
    }

    public function testWronglyWrappedFilePathIsCorrected(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $path = 'file://D:\wrong\path\\';
        } else {
            $path = 'file://D:/wrong/path/';
        }
        $dir = new Dir($path);

        self::assertEquals('file://D:/wrong/path/', (string) $dir);
    }

    public function testWrapWithSetsWrapperN(): void
    {
        $relativeDir = new Dir($this->relativePath);
        $relativeDir->wrapWith('file');

        self::assertEquals(
            'file://' . str_replace('\\', '/', $this->relativePath),
            (string) $relativeDir
        );
    }

    public function testDirExtractFromWrappedPath(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $abs = '/D:/path/does/not';
        } else {
            $abs = '/path/does/not';
        }
        $fileString = 'phar://' . $abs . '/matter/my.phar.gz/i/am/wrapped/class.php';

        $dir = Dir::extract($fileString);
        self::assertEquals('phar://' . $abs . '/matter/my.phar.gz/i/am/wrapped/', (string) $dir);
    }

    public function testWrapWith_ChangesThePathToUnixStyle(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $abs = 'D:\path\does\point\\'; // this is windows style for windows path
        } else {
            $abs = '/path/does/point/';
        }

        $absUnix = Dir::factory($abs)->getOSPath(Dir::UNIX, Dir::WINDOWS_DRIVE_WINDOWS_STYLE); // so we have to provide it here

        $abs .= 'to' . DIRECTORY_SEPARATOR . 'target' . DIRECTORY_SEPARATOR;

        $dir = new Dir($abs);
        $dir->wrapWith('vfs');

        self::assertEquals('vfs://' . $absUnix . 'to/target/', (string) $dir);
    }

    public function testGetDirWithoutTrailingSlash(): void
    {
        self::assertEquals(
            rtrim(__DIR__, DIRECTORY_SEPARATOR),
            Dir::factoryTS(__DIR__)->getPath(Dir::WITHOUT_TRAILINGSLASH)
        );
    }
}
