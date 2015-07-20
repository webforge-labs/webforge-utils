<?php

namespace Webforge\Common\Mock;

class SessionTest extends \PHPUnit_Framework_TestCase {

  protected $session;
  
  public function setUp() {
    parent::setUp();

    $this->session = new Session();
    $this->session->init();
  }

  public function testInitIsSaveToCallInCLI() {
    $this->session->init();

    $this->assertInternalType('array', $this->session->getKeysMap()->toArray());
  }

  public function testReturnsNULLForUndefinedKeys() {
    $this->session->set('some', 'value');

    $this->assertNull($this->session->get('some','undefined'));
  }

  public function testReturnsTheValueOfAsettedKey() {
    $this->session->set('some', 'value');

    $this->assertEquals('value', $this->session->get('some'));
  }

  public function testUnwrapsKeyMapForOtherDirtyTests() {
    $this->assertInstanceOf('Webforge\Collections\KeysMap', $this->session->getKeysMap());
  }
}
