<?php

namespace Webforge\Common;

use ReflectionClass;
use Webforge\Common\StringUtil as S;

class PHPClass implements ClassInterface {

  /**
   * @var string
   */
  protected $name;

  /**
   * @var string
   */
  protected $namespace;

  
  public function __construct($fqn)  {
    $this->setFQN($fqn);
  }

  /**
   * Replaces the Namespace and Name of the Class
   *
   * @param string $fqn no \ before
   */
  public function setFQN($fqn) {
    $this->reflection = NULL;
    if (FALSE !== ($pos = mb_strrpos($fqn,'\\'))) {
      $this->namespace = ltrim(mb_substr($fqn, 0, $pos+1), '\\'); // +1 to add the trailing slash, see setNamespace
      $this->setName(mb_substr($fqn, $pos));
    } else {
      $this->namespace = NULL;
      $this->setName($fqn);
    }
  }

  /**
   * Returns the Fully Qualified Name of the Class
   *
   * this is the whole path including Namespace without a backslash before
   * @return string
   */
  public function getFQN() {
    return $this->namespace.$this->name;
  }

  /**
   * returns the Name of the Class
   *
   * its the Name of the FQN without the Namespace
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Sets the Name of the Class
   *
   * this is not the FQN, its only the FQN without the namespace
   * @chainable
   */
  public function setName($name) {
    $this->reflection = NULL;
    $this->name = trim($name, '\\');
    return $this;
  }

  /**
   * Returns the Namespace
   *
   * @return string The namespace has no \ before and after
   */
  public function getNamespace() {
    // i think its faster to compute the FQN with concatenation add the trailingslash in the setter and remove the trailslash here
    return isset($this->namespace) ? rtrim($this->namespace, '\\') : NULL;
  }

  
  /**
   * @chainable
   */
  public function setNamespace($ns) {
    $this->reflection = NULL;
    $this->namespace = $ns != NULL ? ltrim(S::expand($ns, '\\', S::END), '\\') : NULL;
    return $this;
  }

  /**
   * @return ReflectionClass
   */
  public function getReflection() {
    if (!isset($this->reflection)) {
      $this->reflection = new ReflectionClass($this->getFQN());
    }

    return $this->reflection;
  }

  /**
   * Used in tests for webforge-types
   */
  public function injectReflection($refl) {
    $this->reflection = $refl;
    return $this;
  }

  /**
   * @return bool
   */
  public function equals(ClassInterface $otherClass) {
    return $this->getFQN() === $otherClass->getFQN();
  }

  /**
   * @return string
   */
  public function __toString() {
    return $this->getFQN();
  }
}
