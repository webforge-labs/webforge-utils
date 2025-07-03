<?php declare(strict_types=1);

namespace Webforge\Common\System;

/**
 * @covers Webforge\Common\System\Dir
 */
class DirTest extends \PHPUnit\Framework\TestCase
{
    protected Dir $dir;
    protected string $absolutePath;
    protected string $relativePath;

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

    public function testThatTheFactoryReturnsADir(): void
    {
        self::assertInstanceOf(\Webforge\Common\System\Dir::class, Dir::factory(__DIR__ . DIRECTORY_SEPARATOR));
    }

    public function testThatTheTSFactoryReturnsADir_andWorksWithoutTrailingSlash(): void
    {
        self::assertInstanceOf(\Webforge\Common\System\Dir::class, Dir::factoryTS(__DIR__));
    }

    public function testFactoryTSCanHaveAnEmptyPath(): void
    {
        self::assertInstanceOf(\Webforge\Common\System\Dir::class, Dir::factoryTS());
    }

    /**
     * @dataProvider providePathsWithoutTrailingSlash
     */
    public function testFactoryDoesNotLikeDirectoriesWithoutSlash(string $erroneous): void
    {
        $this->expectException(\Webforge\Common\System\Exception::class);

        new Dir($erroneous);
    }

    public static function providePathsWithoutTrailingSlash(): array
    {
        return [
          ['/var/local/missing/trail'],
          ['D:\www\missing\trail']
        ];
    }

    public function testConstructWithDirAsParamWillCloneDirectory(): void
    {
        $dir = new Dir($this->dir);

        self::assertEquals((string) $this->dir, (string) $dir);
        self::assertNotSame($this->dir, $dir);
    }

    /**
     * @dataProvider provideDifferentPaths
     */
    public function testgetOSPathReturnsPathForGivenOS($path, $expectedPath, $os, $flags = 0): void
    {
        $dir = new Dir($path);

        self::assertEquals(
            $expectedPath,
            $dir->getOSPath($os, $flags)
        );
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideDifferentPaths(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test('/var/local/www/', '/var/local/www/', Dir::UNIX);

        $test('D:\www\webforge\\', 'D:\www\webforge\\', Dir::WINDOWS);
        $test('D:\www\webforge\\', '/D:/www/webforge/', Dir::UNIX);
        $test('C:\\', 'C:\\', Dir::WINDOWS);
        $test('C:\\', '/C:/', Dir::UNIX);

        $test('/D:/www/webforge/', 'D:\www\webforge\\', Dir::WINDOWS);
        $test('/D:/www/webforge/', '/D:/www/webforge/', Dir::UNIX);

        $test('.\its\relative\\', '.\its\relative\\', Dir::WINDOWS);
        $test('.\its\relative\\', './its/relative/', Dir::UNIX);

        $test('./its/relative/', './its/relative/', Dir::UNIX);
        $test('./its/relative/', '.\its\relative\\', Dir::WINDOWS);

        $test('its/relative/', 'its/relative/', Dir::UNIX);
        $test('its/relative/', 'its\relative\\', Dir::WINDOWS);

        $test('its\relative\\', 'its\relative\\', Dir::WINDOWS);
        $test('its\relative\\', 'its/relative/', Dir::UNIX);

        $test('/cygdrive/c/', '/cygdrive/c/', Dir::UNIX);
        $test('/cygdrive/c/', '/cygdrive/c/', Dir::WINDOWS);

        $test('/cygdrive/c/with/longer/path/', '/cygdrive/c/with/longer/path/', Dir::UNIX);
        $test('/cygdrive/c/with/longer/path/', '/cygdrive/c/with/longer/path/', Dir::WINDOWS);

        $test('/cygdrive/c/with/bad\\path/', '/cygdrive/c/with/bad/path/', Dir::UNIX);
        $test('/cygdrive/c/with/bad\\path/', '/cygdrive/c/with/bad/path/', Dir::WINDOWS);

        $test('/cygdrive/c/with/okay\\ path/', '/cygdrive/c/with/okay\\ path/', Dir::UNIX);
        $test('/cygdrive/c/with/okay\\ path/', '/cygdrive/c/with/okay\\ path/', Dir::WINDOWS);

        $test('vfs:///project/src/', 'vfs:///project/src/', Dir::WINDOWS);
        $test('vfs:///project/src/', 'vfs:///project/src/', Dir::UNIX);

        $test('vfs://appstorage/', 'vfs://appstorage/', Dir::WINDOWS);
        $test('vfs://appstorage/', 'vfs://appstorage/', Dir::UNIX);

        $test('phar:///root/path/x.phar/src/', 'phar:///root/path/x.phar/src/', Dir::WINDOWS);
        $test('phar:///root/path/x.phar/src/', 'phar:///root/path/x.phar/src/', Dir::UNIX);

        $test('\\\\psc-host\shared\www\webforge\\', '\\\\psc-host\shared\www\webforge\\', Dir::WINDOWS);
        $test('\\\\psc-host\\', '\\\\psc-host\\', Dir::WINDOWS);

        // edge cases with exception?
        //$test('/var/local/www/', 'var\local\www\\', Dir::WINDOWS);
        //$test('\\\\psc-host\shared\www\webforge\\', '???', Dir::UNIX);
        //$test('/var/local/www/', '/var/local/www/', Dir::WINDOWS, Dir::WINDOWS_WITH_CYGWIN);

        // conversion to cygwin
        $test('D:\www\webforge\\', '/cygdrive/d/www/webforge/', Dir::WINDOWS, Dir::WINDOWS_WITH_CYGWIN);

        $test('its/relative/', 'its/relative/', Dir::UNIX, Dir::WINDOWS_WITH_CYGWIN);
        $test('its/relative/', 'its/relative/', Dir::WINDOWS, Dir::WINDOWS_WITH_CYGWIN);

        $test('/cygdrive/c/', '/cygdrive/c/', Dir::UNIX, Dir::WINDOWS_WITH_CYGWIN);
        $test('/cygdrive/c/', '/cygdrive/c/', Dir::WINDOWS, Dir::WINDOWS_WITH_CYGWIN);

        $test('/cygdrive/c/with/longer/path/', '/cygdrive/c/with/longer/path/', Dir::UNIX, Dir::WINDOWS_WITH_CYGWIN);
        $test('/cygdrive/c/with/longer/path/', '/cygdrive/c/with/longer/path/', Dir::WINDOWS, Dir::WINDOWS_WITH_CYGWIN);

        $test('/cygdrive/c/with/bad\\path/', '/cygdrive/c/with/bad/path/', Dir::UNIX, Dir::WINDOWS_WITH_CYGWIN);
        $test('/cygdrive/c/with/bad\\path/', '/cygdrive/c/with/bad/path/', Dir::WINDOWS, Dir::WINDOWS_WITH_CYGWIN);

        $test('/var/local/www/', '/var/local/www/', Dir::UNIX, Dir::WINDOWS_WITH_CYGWIN);

        return $tests;
    }

    /**
     * @dataProvider provideAbsoluteOrRelative
     */
    public function testAbsoluteOrRelative(string $path, $isAbsolute): void
    {
        $dir = new Dir($path);
        if ($isAbsolute) {
            self::assertTrue($dir->isAbsolute(), $path . ' ->isAbsolute');
            self::assertFalse($dir->isRelative(), $path . ' ->isNotRelative');
        } else {
            self::assertFalse($dir->isAbsolute(), $path . ' ->isNotAbsolute');
            self::assertTrue($dir->isRelative(), $path . ' ->isRelative');
        }
    }

    /**
     * @dataProvider provideAbsoluteOrRelative
     */
    public function testIsAbsolutePath(string $path, $isAbsolute): void
    {
        self::assertEquals($isAbsolute, Dir::isAbsolutePath($path), '::isAbsolutePath(' . $path . ')');
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideAbsoluteOrRelative(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $absolute = true;
        $relative = false;
        $test('vfs:///project/src/', $absolute);
        $test('phar:///root/path/x.phar/src/', $absolute);
        $test('/var/local/www/', $absolute);
        $test('D:\www\webforge\\', $absolute);
        $test('C:\\', $absolute);
        $test('\\\\host\path\to\location\\', $absolute);

        $test('.\its\relative\\', $relative);
        $test('./its/relative/', $relative);
        $test('../../its/relative/', $relative);
        $test('its/relative/', $relative);
        $test('its\relative\\', $relative);

        return $tests;
    }

    public function testUnixRegressionForAbsolutePath_factoryTSDoesLtrimInsteadofRtrim(): void
    {
        if (DIRECTORY_SEPARATOR === '/') {
            $dir = Dir::factoryTS('/var/local/www/tiptoi.pegasus.ps-webforge.net/base/src/');
            self::assertEquals('/var/local/www/tiptoi.pegasus.ps-webforge.net/base/src/', (string) $dir);
        } else {
            $dir = Dir::factoryTS('/D:/var/local/www/tiptoi.pegasus.ps-webforge.net/base/src/');
            self::assertEquals('D:\var\local\www\tiptoi.pegasus.ps-webforge.net\base\src\\', (string) $dir);
        }
    }

    public function testDotMakesAnRelativeEmptyPathedDir(): void
    {
        $dir = Dir::factoryTS('.');

        self::assertEquals(
            '.' . DIRECTORY_SEPARATOR,
            (string) $dir
        );

        self::assertTrue($dir->isRelative());
    }

    public function testDotMakesAnDirThatExpandsToCWDOnResolve(): void
    {
        $dir = Dir::factoryTS('.');

        $dir->resolvePath();

        self::assertEquals(
            getcwd() . DIRECTORY_SEPARATOR,
            (string) $dir
        );
    }

    public function testisSubDirectoryOf(): void
    {
        $sub = new Dir(__DIR__ . DIRECTORY_SEPARATOR);
        $parent = new Dir(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);

        self::assertTrue($sub->isSubdirectoryOf($parent));
    }

    public function testDirectoryIsNotSubdirectoryOfSelf(): void
    {
        $dir = new Dir(__DIR__ . DIRECTORY_SEPARATOR);
        $self = new Dir(__DIR__ . DIRECTORY_SEPARATOR);

        self::assertFalse($dir->isSubdirectoryOf($self));
    }

    public function testGetMACTime_Acceptance(): void
    {
        self::assertInstanceof(\Webforge\Common\DateTime\DateTime::class, $this->dir->getModifiedTime());
        self::assertInstanceof(\Webforge\Common\DateTime\DateTime::class, $this->dir->getCreateTime());
        self::assertInstanceof(\Webforge\Common\DateTime\DateTime::class, $this->dir->getAccessTime());
    }

    public function testCygwinPathsAreTreatedCorrectly(): void
    {
        $path = '/cygdrive/D/www/psc-cms-js/git/';

        self::assertTrue(Dir::isCygwinPath($path));

        self::assertEquals(
            $path,
            (string) new Dir($path)
        );
    }

    /**
     * @dataProvider provideFixToUnixPath
     */
    public function testFixToUnixPath($actualPath, $expectedPath): void
    {
        self::assertEquals($expectedPath, Dir::fixToUnixPath($actualPath));
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideFixToUnixPath(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test('/var/local\www/', '/var/local/www/');
        $test('\var/local\www/', '/var/local/www/');
        $test('/var/local/www\\', '/var/local/www/');

        $test('/var/with\\ space/', '/var/with\\ space/');
        $test('/var/with\\\\ backslash/', '/var/with\\\\ backslash/');

        return $tests;
    }

    /**
     * @dataProvider provideCreateFromURL
     */
    public function testCreateFromURL($url, $expectedPath, $root = null): void
    {
        $root = $root ?: new Dir('D:\www\\');

        self::assertEquals(
            $expectedPath,
            (string) Dir::createFromURL($url, $root)->resolvePath()->getOSPath(Dir::WINDOWS)
        );
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideCreateFromURL(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $root = 'D:\www\\';

        $test('something/relative', $root . 'something\relative\\');
        $test('something/relative/which/./resolves', $root . 'something\relative\\which\\resolves\\');
        $test('something/relative/which/../resolved', $root . 'something\relative\resolved\\');

        $test('/', $root);
        $test('./', $root);

        return $tests;
    }

    public function testCreateFromURLUsesCWDAsDefault(): void
    {
        self::assertEquals(
            getcwd() . DIRECTORY_SEPARATOR . 'in' . DIRECTORY_SEPARATOR . 'cwd' . DIRECTORY_SEPARATOR,
            (string) Dir::createFromURL('in/cwd/')
        );
    }

    public function testWtsPath(): void
    {
        self::assertEquals(__DIR__, $this->dir->wtsPath());
    }

    public function testCreateDirRespectsEnvVariableWhenUmaskIsSet(): void
    {
        $old = getenv('WEBFORGE_UMASK_SET');
        if ($old != 1) {
            putenv('WEBFORGE_UMASK_SET=1');
        }

        self::assertEquals(
            sprintf('%04o', 0744),
            sprintf('%04o', Dir::$defaultMod),
            'defaultMod for historical reasons should be used for this test (otherwise its useless)'
        );

        try {
            $newDir = new Dir(__DIR__ . DIRECTORY_SEPARATOR . 'create-test' . DIRECTORY_SEPARATOR);

            if ($newDir->exists()) {
                $newDir->delete();
            }

            $newDir->create();

            $perms = fileperms((string) $newDir) & 0777;
            $umask = umask();

            self::assertEquals(
                sprintf('%04o', 0777 & ~$umask),
                sprintf('%04o', $perms),
                'Permission for dir should be umasked. (it should have used 0777 for creating)'
            );
        } catch (\Exception $e) {
            putenv('WEBFORGE_UMASK_SET=' . $old);
            throw $e;
        }

        putenv('WEBFORGE_UMASK_SET=' . $old);
    }
}
