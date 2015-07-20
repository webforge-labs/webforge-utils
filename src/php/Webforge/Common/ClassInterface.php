<?php

namespace Webforge\Common;

interface ClassInterface {

  /**
   * @return string the full qualified name (namespace + name without backslash in front)
   */
  public function getFQN();

  /**
   * @return string the name of the class (without namespace)
   */
  public function getName();

  /**
   * @return string the namespace of the class without trailing backslash
   */
  public function getNamespace();

  /**
   * @return ReflectionClass
   */
  public function getReflection();

  /**
   * @return bool
   */
  public function equals(ClassInterface $other);
}
