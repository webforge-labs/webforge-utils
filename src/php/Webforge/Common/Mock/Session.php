<?php

namespace Webforge\Common\Mock;

use Webforge\Collections\KeysMap;

class Session implements \Webforge\Common\Session {

  protected $keysMap;

  protected $init;
  
  public function init() {
    if (!$this->init) {
      $this->keysMap = new KeysMap();
    
      $this->init = TRUE;
    }
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function get() {
    return $this->keysMap->get(func_get_args(), $default = NULL);
  }

  /**
   * @inheritdoc
   */
  public function set() {
    $args = func_get_args();
    $value = array_pop($args);

    $this->keysMap->set($args, $value);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getKeysMap() {
    return $this->keysMap;
  }

  public function destroy() {
    $this->keysMap->reset();
    return $this;
  }
}
