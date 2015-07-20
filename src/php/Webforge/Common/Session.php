<?php

namespace Webforge\Common;

/**
 * Simple interface for a session implementation
 * 
 * @see Psc\Session\Session for a default implementation
 * @see Webforge\Common\Mock\Sessionx
 */
interface Session {

  /**
   * Returns the value from a path of keys from the session
   * 
   * $session->get('user', 'password');
   * 
   * returns NULL if the path of keys is not existing
   * @param string $key1, ...
   * @return mixed
   */
  public function get();
  
  /**
   * Sets the value from a path of keys from the session
   * 
   * $session->set('user', 'password', 'secret');
   * $session->get('user', 'password'); // 'secret'
   * @param string $key2, ...
   * @param mixed $value
   * @chainable
   */
  public function set();

  /**
   * Connects the session with the environment
   * 
   * calling it more than once is safe
   * @chainable
   */  
  public function init();

  /**
   * Destroys the whole session and disconnects from environment
   */
  public function destroy();

}