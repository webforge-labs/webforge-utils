<?php

namespace Webforge\Common\System;

use SplFileInfo;

/**
 * @covers Webforge\Common\System\Dir
 */
class DirFilesTest extends \PHPUnit_Framework_TestCase {
  
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

  public function testDirGetFile() {
    $dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $file = __FILE__;
    $fname = basename($file);
    
    //$this->assertEquals('D:\www\psc-cms\Umsetzung\base\src\psc\readme.txt',
                        //(string) $dir->getFile('readme.txt'));

    $this->assertEquals($file, (string) $dir->getFile($fname));
    $this->assertEquals($file, (string) $dir->getFile(new File($fname)));
    $this->assertEquals($file, (string) $dir->getFile(new File(new Dir('.'.DIRECTORY_SEPARATOR),$fname)));

  /*
    das ist unexpected! ich will aber keinen test auf sowas machen..
    $this->assertEquals('D:\www\psc-cms\Umsetzung\base\readme.txt',
                        (string)  $dir->getFile('..\\..\\readme.txt'));
  */

    if (DIRECTORY_SEPARATOR === '\\') {
      $this->assertEquals(__DIR__.'\lib\docu\readme.txt',
                          (string) $dir->getFile(new File('.\lib\docu\readme.txt')));
      $this->assertEquals(__DIR__.'\lib\docu\readme.txt',
                          (string) $dir->getFile(new File(new Dir('.\lib\docu\\'),'readme.txt')));
                          
    } else {
      $this->assertEquals(__DIR__.'/lib/docu/readme.txt',
                          (string) $dir->getFile(new File('./lib/docu/readme.txt')));
      $this->assertEquals(__DIR__.'/lib/docu/readme.txt',
                          (string) $dir->getFile(new File(new Dir('./lib/docu/'),'readme.txt')));
      
    }
    

    $absoluteDir = __DIR__.DIRECTORY_SEPARATOR;
    $this->setExpectedException('InvalidArgumentException');
    $dir->getFile(new File(new Dir($absoluteDir),'readme.txt'));
  }
  
  public function testDirgetFiles() {
    $dir = Dir::factoryTS(__DIR__);
    
    if ($dir->exists()) {
      $files = $dir->getFiles('php');
      $this->assertNotEmpty($files);
      
      foreach ($files as $file) {
        if (isset($dirone)) {
          $this->assertFalse($dirone === $file->getDirectory(),'Die Verzeichnisse der dateien von getFiles() müssen kopien des ursprünglichen objektes sein. keine Referenzen');
          $this->assertFalse($dirone === $dir,'Die Verzeichnisse der dateien von getFiles() müssen kopien des ursprünglichen objektes sein. keine Referenzen');
        }
        
        $dirone = $file->getDirectory();
        $this->assertFalse($file->getDirectory()->isRelative());
        
        $file->makeRelativeTo($dir);
        
        $this->assertTrue($file->isRelative());
      }
    } else {
      $this->markTestSkipped('ui dev für test nicht da');
    }
  }

  public function testIsEmpty() {
    $nonEx = Dir::factoryTS(__DIR__)->sub('blablabla/non/existent/');
    $this->assertTrue($nonEx->isEmpty());
    
    $temp = Dir::createTemporary();
    $this->assertTrue($temp->isEmpty());
    
    $f = $temp->getFile('blubb.txt');
    $f->writeContents('wurst');
    $this->assertFileExists((string) $f);
    $this->assertFalse($temp->isEmpty());
  }  
}
 