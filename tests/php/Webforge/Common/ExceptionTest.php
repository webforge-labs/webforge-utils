<?php

namespace Webforge\Common;

class ExceptionTest extends \PHPUnit_Framework_TestCase {
  
  public function setUp() {
    $this->e = new Exception('this is the #1 exception', 0);
    
    $this->nested = new Exception('this is the #2 exception', 0, $this->e);
  }
  
  public function testExceptionTextContainsException1() {
    $text = $this->e->toString('text');
    $this->assertContains('this is the #1 exception', $text);
  }

  public function testExceptionTextContainsException1_inHTML() {
    $html = $this->e->toString('html');
    $this->assertContains('this is the #1 exception', $html);
    $this->assertContains('<b>Fatal Error:</b>', $html);
  }

  public function testExceptionTextPathsGetReplacedWhenRelativeDirIsGiven() {
    try {
      // the exception must come from this file
      $this->throwIt();
      
    } catch (Exception $ex) {
      $text = $ex->toString('text', __DIR__, 'replacedDir');
      $this->assertContains('{replacedDir}', $text);
    }
  }
  
  public function testExceptionTextContainsException1And2ForNested() {
    $text = $this->nested->toString('text');
    
    $this->assertContains('this is the #1 exception', $text);
    $this->assertContains('this is the #2 exception', $text);
  }
  
  public function testHierarchy() {
    $this->assertInstanceof('Exception', $this->e);
  }
  
  public function testMessageCanbeOverwritten() {
    $this->e->setMessage('blubb');
    $this->assertEquals('blubb', $this->e->getMessage());
    $this->assertNotContains('this is the #1 exception', $this->e->toString('text'));
  }
  
  public function testAppendMessageAddsTextToTheMessageToTheEnd() {
    $text = $this->e->appendMessage('. more detailed info.')->getMessage();
    
    $this->assertContains('this is the #1 exception. more detailed info.', $text);
  }

  public function testPrependMessageAddsTextToTheMessageAtTheBeginning() {
    $text = $this->e->prependMessage('[verbose info] ')->getMessage();
    
    $this->assertContains('[verbose info] this is the #1 exception', $text);
  }
  
  public function throwIt() {
    throw new Exception('this is the #1 exception', 0);
  }
}
?>