<?php

namespace Webforge\Common\System;

use org\bovigo\vfs\vfsStream;
use Webforge\Common\TestCase;

class FileTest extends TestCase {
  
  protected static $absPathPrefix;
  
  protected $dir, $dirPath;

  public static function setUpBeforeClass() {
    self::$absPathPrefix = Util::isWindows() ? 'D:\\' : '/';
  }
  
  public function setUp() {
    $this->chainClass = __NAMESPACE__.'\\File';
    parent::setUp();

    $this->dirPath = self::absPath('path','to','some','dir');
    $this->dir = new Dir($this->dirPath);
    $this->notExistingFile = $this->dir->getFile('not-existing');
  }
  
  // erstellt einen Pfad mit trailing slash
  public static function path() {
    return implode(DIRECTORY_SEPARATOR, func_get_args()).DIRECTORY_SEPARATOR;
  }
  
  public static function absPath() {
    return self::$absPathPrefix.implode(DIRECTORY_SEPARATOR, func_get_args()).DIRECTORY_SEPARATOR;
  }
  
  public function testFactoryReturnsAFile() {
    $this->assertInstanceOf('Webforge\Common\System\File', File::factory($this->dirPath.'somefile.txt'));
    $this->assertInstanceOf('Webforge\Common\System\File', File::factory($this->dir, 'somefile.txt'));
    $this->assertInstanceOf('Webforge\Common\System\File', File::factory('somefile.txt', $this->dir));
  }
  
  public function testConstructor() {
    $fileString = self::absPath('www', 'test', 'base', 'ka', 'auch').'banane.php';
    
    $dir = new Dir(self::absPath('www', 'test', 'base', 'ka', 'auch'));
    $filename = 'banane.php';
    
    $file = new File($dir, $filename);
    $this->assertEquals($fileString, (string) $file);
    
    $file = new File($fileString);
    $this->assertEquals($fileString, (string) $file);
    
    $file = new File($filename, $dir);
    $this->assertEquals($fileString, (string) $file);
  }
  
  public function testWrappedConstructor() {
    $fileString = 'phar://'.($pf = Util::isWindows() ? 'D:/' : '/').'does/not/matter/my.phar.gz/i/am/wrapped/class.php';
    
    $file = new File($fileString);
    $this->assertEquals('php',$file->getExtension());
    $this->assertEquals('class.php',$file->getName());
    $this->assertEquals('phar://'.$pf.'does/not/matter/my.phar.gz/i/am/wrapped/', (string) $file->getDirectory());
    $this->assertEquals($fileString, (string) $file);
  }
  
  public function testReadableinPhar() {
    $phar = $this->getFile('some.phar.gz');
    $wrapped = 'phar://'.str_replace(DIRECTORY_SEPARATOR, '/', (string) $phar).'/Imagine/Exception/Exception.php';
    
    $file = new File($wrapped);
    $this->assertTrue($file->isReadable());
    $this->assertTrue($file->exists());
  }
  
  public function testAppendName() {
    $path = self::absPath('Filme', 'Serien', 'The Big Bang Theory', 'Season 5');
    
    $file = new File($path.'The.Big.Bang.Theory.S05E07.en.IMMERSE.srt');
    $file->setName($file->getName(File::WITHOUT_EXTENSION).'-en.srt');
    
    $this->assertEquals($path.'The.Big.Bang.Theory.S05E07.en.IMMERSE-en.srt',(string) $file);
  }
  
  /**
   * @expectedException \BadMethodCallException
   */
  public function testConstructorException1() {
    $file = new File('keindir','keinfilename');
  }

  /**
   * @expectedException \BadMethodCallException
   */
  public function testConstructorException2() {
    $file = new File(new File('/tmp/src'));
  }
  
  /**
   * @dataProvider provideGetURL
   */
  public function testGetURL($expectedURL, $fileString, $dirString = NULL) {  
    $file = new File($fileString);
    $dir = isset($dirString) ? new Dir($dirString) : NULL;
    
    $this->assertEquals($expectedURL, $file->getURL($dir));
  }
  
  public static function provideGetURL() {
    $tests = array();
    $test = function ($file, $dir, $url) use (&$tests) {
      $tests[] = array($url, $file, $dir);
    };
    
    $test(self::absPath('www', 'test', 'base', 'ka', 'auch').'banane.php',
          self::absPath('www', 'test', 'base', 'ka'),
          '/auch/banane.php');
    $test(self::absPath('www', 'psc-cms', 'Umsetzung', 'base', 'src', 'tpl').'throwsException.html',
          self::absPath('www', 'psc-cms', 'Umsetzung', 'base', 'src', 'tpl'),
          '/throwsException.html'
         );
    
    return $tests;
  }
  
  public function testGetURL_noSubdir() {
    $fileString = self::absPath('www', 'test', 'base', 'ka', 'auch').'banane.php';
    $file = new File($fileString);
  }

  public function testStaticCreateFromURL() {
    $dir = new Dir($path = self::absPath('www', 'ePaper42', 'Umsetzung', 'base', 'files', 'testdata', 'fixtures', 'ResourceManagerTest', 'xml'));
    $url = "/in2days/2011_newyork/main.xml";
    
    $this->assertEquals($path.'in2days'.DIRECTORY_SEPARATOR.'2011_newyork'.DIRECTORY_SEPARATOR.'main.xml',
                        (string) File::createFromURL($url, $dir));
    $this->assertEquals(self::path('.', 'in2days', '2011_newyork'). 'main.xml', (string) File::createFromURL($url));
  }
    
  public function testGetFromURL_relativeFile() {
    // wird als Datei interpretiert die in in2days/ liegt !
    $url = "/in2days/2011_newyork";
    $this->assertEquals('.'.DIRECTORY_SEPARATOR.'in2days'.DIRECTORY_SEPARATOR.'2011_newyork', (string) File::createFromURL($url));
  }

  public function testWriteContentsCanDoAnExclusiveTempMove() {
    $file = File::createTemporary();

    $file->writeContents(123, File::EXCLUSIVE);
    $this->assertEquals(123, file_get_contents((string) $file));
    $file->delete();
  }

  public function testCreatingTemporaryWithExtension() {
    $file = File::createTemporary('jpg');

    $this->assertEquals('jpg', $file->getExtension());

    $file->delete();
  }

  public function testWritingIntoAFileWithoutAnExistingDirDoesFail() {
    $this->assertFalse($this->dir->exists());

    $this->setExpectedException('Webforge\Common\System\Exception');
    
    $this->dir->getFile('new.txt')->writeContents('some-content');
  }
  
  public function testSha1Hashing() {
    $content = 'sldfjsldfj';
    $otherContent = 's00000000';
    $file = File::createTemporary();
    $file->writeContents($content);
    $this->assertEquals(sha1($content), $file->getSha1());
    
    // test caching
    $file->writeContents($otherContent);
    //$this->assertNotEquals(sha1($content), $file->getSha1());
    $this->assertEquals(sha1($otherContent), $file->getSha1());
    $file->delete();
  }

  protected function setupNoExtensionFile() {
    $dir = vfsStream::setup('extension-files', NULL, array(
      'thefile.php' => '<?php // its php',
      'thefile.js' => 'define(function () {})',
      'thefile.csv' => 'foo,bar,baz'
    ));

    $dir = new Dir(vfsStream::url('extension-files').'/');

    return new File('thefile', $dir);
  }

  /**
   * @dataProvider providefindExtension
   */
  public function testFindExtensionTestsSeveralExtensionsForFileNameForExistanceAndReturnsNewFileInstance(Array $extensions, $expectedFile) {
    $noExtensionFile = $this->setupNoExtensionFile();

    $extensionFile = $noExtensionFile->findExtension($extensions);

    $this->assertInstanceOf(__NAMESPACE__.'\\File', $extensionFile);
    $this->assertNotSame($extensionFile, $noExtensionFile);

    $this->assertEquals(
      $expectedFile,
      $extensionFile->getName(File::WITH_EXTENSION)
    );
  }

  
  public static function providefindExtension() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test(array('php', 'js', 'csv'), 'thefile.php');
    $test(array('js', 'php', 'csv'), 'thefile.js');
    $test(array('csv', 'php', 'js'), 'thefile.csv');

    $test(array('nil', 'php', 'js'), 'thefile.php');
    $test(array('nil', 'nil2', 'js'), 'thefile.js');
  
    return $tests;
  }

  public function testFindExtensionThrowsExcetionIfNoExtensionIsFound() {
    $noExtensionFile = $this->setupNoExtensionFile();

    $this->setExpectedException('Webforge\Common\Exception\FileNotFoundException');

    $noExtensionFile->findExtension(array('nil', 'nihil', 'none'));
  }

  public function testGetOSPathIsCalledForDir() {
    $file = new File(self::absPath('www', 'test', 'base', 'ka', 'auch').'test.php');
    
    $dir = new Dir(self::absPath('www', 'test', 'base', 'ka', 'auch'));
    $filename = 'test.php';

    $this->assertEquals(
      $dir->getOSPath(Dir::WINDOWS).'test.php',
      $file->getOSPath(File::WINDOWS)
    );

    $this->assertEquals(
      $dir->getOSPath(Dir::UNIX).'test.php',
      $file->getOSPath(File::UNIX)
    );
  }

  public function testCopySourceHasToBeExisting() {
    $this->setExpectedException(__NAMESPACE__.'\Exception');
    $this->notExistingFile->copy($this->dir->getFile('new.txt'));
  }

  public function testCopyCanCopyIntoADirectoryAndUsesTheSameName() {
    $dir = Dir::createTemporary();

    $file = new File(__FILE__);
    $file->copy($dir);

    $this->assertFileExists((string) $dir->getFile(basename(__FILE__)));
    $dir->delete();
  }

  public function testTheDestinationDirHasToBeExisting() {
    $this->setExpectedException(__NAMESPACE__.'\Exception');

    $file = new File(__FILE__);
    $file->copy($this->dir);
  }

  public function testDestinationfromCopyCannotBeAstring() {
    $this->setExpectedException('InvalidArgumentException');

    $file = new File(__FILE__);
    $file->copy('to-wrong');
  }

  public function testIsRelativeIsReflectedFromDirectory() {
    $this->assertFalse($this->notExistingFile->isRelative());
  }

  public function testResolvePathIsCalledFromDirectory() {
    $file = new File(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'file.txt');
    $file->resolvePath();

    $this->assertEquals(
      realpath(__DIR__.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR.'file.txt',
      (string) $file
    );
  }

  public function testGetSizeReturnsTheFileSizeINBytes() {
    $f = File::createTemporary()->writeContents(str_repeat('1', 16)); 
    clearstatcache();
    $this->assertEquals(16, $f->getSize());
    $f->delete();
  }

  public function testFileGetContentsCannotReadNotExistingFile() {
    $this->setExpectedException(__NAMESPACE__.'\Exception');
    $this->notExistingFile->getContents();
  }

  public function testFileGetContentsCannotReadNotExistingFileWithSize() {
    $this->setExpectedException(__NAMESPACE__.'\Exception');
    $this->notExistingFile->getContents(2);
  }

  public function testGetContentsCanBeRestrictedToBytes() {
    $f = File::createTemporary()->writeContents(str_repeat('1', 16)); 
    $this->assertEquals(2, mb_strlen($f->getContents(2)));
  }

  public function testSafeNameIsNotCool() {
    $this->assertEquals(
      'yAElsdfjIO',
      File::safename('ýÂÊlsdfjÎÔ')
    );
  }
}
