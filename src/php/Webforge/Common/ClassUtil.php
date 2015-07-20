<?php

namespace Webforge\Common;

use ReflectionClass;
use InvalidArgumentException;

class ClassUtil {

  /**
   * Returns the FQN for $className with $namespace if $className has not an namespace
   * 
   * @return string always without a \ in front
   */
  public static function expandNamespace($className, $namespace) {
    if (mb_strpos($className, '\\', 1) !== FALSE) {
      return ltrim($className, '\\');
    }

    return trim($namespace, '\\').'\\'.trim($className, '\\');
  }

  /**
   * Returns the FQN for $className with $namespace in front
   * 
   * @return string always without a \ in front
   */
  public static function setNamespace($className, $namespace) {
    return trim($namespace, '\\').'\\'.trim($className, '\\');
  }

  public static function getNamespace($className) {
    $className = ltrim($className, '\\');
    if (($pos = mb_strrpos($className, '\\')) !== FALSE) {
      return mb_substr($className, 0, $pos);
    }

    return NULL;
  }

  public static function getClassName($fqn) {
    $fqn = ltrim($fqn, '\\');
    if (($pos = mb_strrpos($fqn, '\\')) !== FALSE) {
      return mb_substr($fqn, $pos+1);
    }

    return $fqn;
  }

  /**
   * 
   * @param string $class the full qualified class name
   * @return instanceOf $class
   */
  public static function newClassInstance($class, Array $constructorArgs) {
    if ($class instanceof ReflectionClass) {
      $refl = $class;

    } elseif (is_string($class)) {
      $refl = new ReflectionClass($class);
      
    } else {
      throw new InvalidArgumentException('class can only be of ReflectionClass or String');
    }
    
    return $refl->newInstanceArgs($constructorArgs);
  }

  /**
   * Returns if the $object instance has the $property as public property
   * 
   * @return bool
   */
  public static function hasPublicProperty($object, $property) {
    return array_key_exists($property, (array) $object);
  }
}
