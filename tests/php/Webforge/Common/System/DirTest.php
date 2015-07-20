<?php

namespace Webforge\Common\System;

use SplFileInfo;

/**
 * @covers Webforge\Common\System\Dir
 */
class DirTest extends \PHPUnit_Framework_TestCase {
  
  protected $dir;
  protected $absolutePath, $relativePath;
  
  public function setUp() {
    $this->dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);

    if (DIRECTORY_SEPARATOR === '\\') {
      $absolutePath = 'D:\\path\\for\\absolute\\';
    } else {
      $this->absolutePath = '/path/for/absolute/';
    }

    $this->relativePath = 'path'.DIRECTORY_SEPARATOR.'for'.DIRECTORY_SEPARATOR.'relative'.DIRECTORY_SEPARATOR;
  }
  
  public function testThatTheFactoryReturnsADir() {
    $this->assertInstanceOf('Webforge\Common\System\Dir', Dir::factory(__DIR__.DIRECTORY_SEPARATOR));
  }

  public function testThatTheTSFactoryReturnsADir_andWorksWithoutTrailingSlash() {
    $this->assertInstanceOf('Webforge\Common\System\Dir', Dir::factoryTS(__DIR__));
  }

  public function testFactoryTSCanHaveAnEmptyPath() {
    $this->assertInstanceOf('Webforge\Common\System\Dir', Dir::factoryTS());
  }

  /**
   * @dataProvider providePathsWithoutTrailingSlash
   */
  public function testFactoryDoesNotLikeDirectoriesWithoutSlash($erroneous) {
    $this->setExpectedException('Webforge\Common\System\Exception');

    new Dir($erroneous);
  }

  public static function providePathsWithoutTrailingSlash() {
    return Array(
      array('/var/local/missing/trail'),
      array('D:\www\missing\trail')
    );
  }

  public function testConstructWithDirAsParamWillCloneDirectory() {
    $dir = new Dir($this->dir);

    $this->assertEquals((string) $this->dir, (string) $dir);
    $this->assertNotSame($this->dir, $dir);
  }
  
  /**
   * @dataProvider provideDifferentPaths
   */
  public function testgetOSPathReturnsPathForGivenOS($path, $expectedPath, $os, $flags = 0) {
    $dir = new Dir($path);

    $this->assertEquals(
      $expectedPath,
      $dir->getOSPath($os, $flags)
    );
  }

  public static function provideDifferentPaths() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test('/var/local/www/', '/var/local/www/', Dir::UNIX);
    
    $test('D:\www\webforge\\', 'D:\www\webforge\\', Dir::WINDOWS);
    $test('D:\www\webforge\\', '/D:/www/webforge/', Dir::UNIX);
    $test('C:\\', 'C:\\', Dir::WINDOWS);
    $test('C:\\', '/C:/', Dir::UNIX);

    $test('/D:/www/webforge/', 'D:\www\webforge\\', Dir::WINDOWS);
    $test('/D:/www/webforge/', '/D:/www/webforge/', Dir::UNIX);

    $test('.\its\relative\\','.\its\relative\\', Dir::WINDOWS);
    $test('.\its\relative\\','./its/relative/', Dir::UNIX); 

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
  public function testAbsoluteOrRelative($path, $isAbsolute) {
    $dir = new Dir($path);
    if ($isAbsolute) {
      $this->assertTrue($dir->isAbsolute(), $path.' ->isAbsolute');
      $this->assertFalse($dir->isRelative(), $path.' ->isNotRelative');
    } else {
      $this->assertFalse($dir->isAbsolute(), $path.' ->isNotAbsolute');
      $this->assertTrue($dir->isRelative(), $path.' ->isRelative');
    }
  }
  

  /**
   * @dataProvider provideAbsoluteOrRelative
   */
  public function testIsAbsolutePath($path, $isAbsolute) {
    $this->assertEquals($isAbsolute, Dir::isAbsolutePath($path), '::isAbsolutePath('.$path.')');
  }

  public static function provideAbsoluteOrRelative() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $absolute = TRUE;
    $relative = FALSE;
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

  public function testUnixRegressionForAbsolutePath_factoryTSDoesLtrimInsteadofRtrim() {
    if (DIRECTORY_SEPARATOR === '/') {
      $dir = Dir::factoryTS('/var/local/www/tiptoi.pegasus.ps-webforge.net/base/src/');
      $this->assertEquals('/var/local/www/tiptoi.pegasus.ps-webforge.net/base/src/', (string) $dir);
    } else {
      $dir = Dir::factoryTS('/D:/var/local/www/tiptoi.pegasus.ps-webforge.net/base/src/');
      $this->assertEquals('D:\var\local\www\tiptoi.pegasus.ps-webforge.net\base\src\\', (string) $dir);
    }
  }

  public function testDotMakesAnRelativeEmptyPathedDir() {
    $dir = Dir::factoryTS('.');

    $this->assertEquals(
      '.'.DIRECTORY_SEPARATOR,
      (string) $dir
    );

    $this->assertTrue($dir->isRelative());
  }

  public function testDotMakesAnDirThatExpandsToCWDOnResolve() {
    $dir = Dir::factoryTS('.');

    $dir->resolvePath();

    $this->assertEquals(
      getcwd().DIRECTORY_SEPARATOR,
      (string) $dir
    );
  }

  /**
   * @dataProvider provideResolvePathNormalizesPathAndReplacesWithCWD
   */
  public function testResolvePathNormalizesPathAndReplacesWithCWD() {

  }
  
  public static function provideResolvePathNormalizesPathAndReplacesWithCWD() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };

    $ds = DIRECTORY_SEPARATOR;
    $cwd = getcwd();
    $lowerCwd = dirname($cwd); // composer trick

    $test('something/relative', implode($ds, array($cwd,'something', 'relative')));
    $test('./something/relative', implode($ds, array($cwd,'something', 'relative')));
    $test('./removed/../something/relative', implode($ds, array($cwd,'something', 'relative')));
    $test('../something/relative/lower', implode($ds, array($lowerCwd, 'something', 'relative', 'lower')));
    $test('something/empty/../../../', implode($ds, array($lowerCwd)));
    $test('something/empty/../../', implode($ds, array($cwd)));
  
    return $tests;
  }

  public function testisSubDirectoryOf() {
    $sub = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $parent = new Dir(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);
    
    $this->assertTrue($sub->isSubdirectoryOf($parent));
  }

  public function testDirectoryIsNotSubdirectoryOfSelf() {
    $dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $self = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    
    $this->assertFalse($dir->isSubdirectoryOf($self));
  }
  
  public function testGetMACTime_Acceptance() {
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', $this->dir->getModifiedTime());
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', $this->dir->getCreateTime());
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', $this->dir->getAccessTime());
  }

  public function testCygwinPathsAreTreatedCorrectly() {
    $path = '/cygdrive/D/www/psc-cms-js/git/';

    $this->assertTrue(Dir::isCygwinPath($path));

    $this->assertEquals(
      $path,
      (string) new Dir($path)
    );
  }

  /**
   * @dataProvider provideFixToUnixPath
   */
  public function testFixToUnixPath($actualPath, $expectedPath) {
    $this->assertEquals($expectedPath, Dir::fixToUnixPath($actualPath));
  }
  
  public static function provideFixToUnixPath() {
    $tests = array();
  
    $test = function() use (&$tests) {
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
  public function testCreateFromURL($url, $expectedPath, $root = NULL) {
    $root = $root ?: new Dir('D:\www\\');

    $this->assertEquals(
      $expectedPath,
      (string) Dir::createFromURL($url, $root)->resolvePath()->getOSPath(Dir::WINDOWS)
    );
  }
  
  public static function provideCreateFromURL() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };

    $root = 'D:\www\\';
  
    $test('something/relative', $root.'something\relative\\');
    $test('something/relative/which/./resolves', $root.'something\relative\\which\\resolves\\');
    $test('something/relative/which/../resolved', $root.'something\relative\resolved\\');

    $test('/', $root);
    $test('./', $root);

    return $tests;
  }

  public function testCreateFromURLUsesCWDAsDefault() {
    $this->assertEquals(
      getcwd().DIRECTORY_SEPARATOR.'in'.DIRECTORY_SEPARATOR.'cwd'.DIRECTORY_SEPARATOR,
      (string) Dir::createFromURL('in/cwd/')
    );
  }

  public function testWtsPath() {
    $this->assertEquals(__DIR__, $this->dir->wtsPath());
  }

  public function testCreateDirRespectsEnvVariableWhenUmaskIsSet() {
    $old = getenv('WEBFORGE_UMASK_SET');
    if ($old != 1) {
      putenv('WEBFORGE_UMASK_SET=1');
    }

    $this->assertEquals(
      sprintf('%04o', 0744),
      sprintf('%04o', Dir::$defaultMod),
      'defaultMod for historical reasons should be used for this test (otherwise its useless)'
    );

    try {
      $newDir = new Dir(__DIR__.DIRECTORY_SEPARATOR.'create-test'.DIRECTORY_SEPARATOR);

      if ($newDir->exists()) {
        $newDir->delete();
      }

      $newDir->create();

      $perms = fileperms($newDir) & 0777;
      $umask = umask();

      $this->assertEquals(
        sprintf('%04o', 0777 & ~$umask),
        sprintf('%04o', $perms),
        'Permission for dir should be umasked. (it should have used 0777 for creating)'
      );

    } catch (\Exception $e) {
      putenv('WEBFORGE_UMASK_SET='.$old);
      throw $e;
    }

    putenv('WEBFORGE_UMASK_SET='.$old);
  }
}
 