<?php

namespace Webforge\Common;

abstract class TestCase extends \PHPUnit_Framework_TestCase {
  
  // implement this correctly when we have a solution for the Code\Test\Base Class
  /**
   * @return Webforge\Common\File
   */
  public function getFile($name) {
    return $this->getTestDirectory()->getFile($name);
  }
  
  /**
   * @return Webforge\Common\Dir
   */
  public function getTestDirectory($sub = '/') {
    return $GLOBALS['env']['root']->sub('tests/files/')->sub($sub);
  }
}
