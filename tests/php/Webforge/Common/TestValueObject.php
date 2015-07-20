<?php

namespace Webforge\Common;

/**
 * @todo start something like this in testdata repository
 */
class TestValueObject {
  
  protected $prop1;
  protected $prop2;
  
  public function __construct($p1Value, $p2Value) {
    $this->prop1 = $p1Value;
    $this->prop2 = $p2Value;
  }
  
  public function getProp1() {
    return $this->prop1;
  }
  
  public function setProp1($p1Value) {
    $this->prop1 = $p1Value;
    return $this;
  }

  public function getProp2() {
    return $this->prop2;
  }
  
  public function setProp2($p2Value) {
    $this->prop2 = $p2Value;
    return $this;
  }
}
