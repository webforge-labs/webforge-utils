<?php

namespace Webforge\Common\Exception;

interface MessageException {

  /**
   * @chainable
   */
  public function setMessage($msg);
  
  /**
   * @chainable
   */
  public function appendMessage($msg);
  
  /**
   * @chainable
   */
  public function prependMessage($msg);

}
