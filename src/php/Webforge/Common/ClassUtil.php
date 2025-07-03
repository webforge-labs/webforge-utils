<?php

declare(strict_types=1);

namespace Webforge\Common;

use InvalidArgumentException;
use ReflectionClass;

class ClassUtil
{
  /**
   * Returns the FQN for $className with $namespace if $className has not an namespace
   *
   * @return string always without a \ in front
   */
    public static function expandNamespace($className, $namespace): string
    {
        if (mb_strpos($className, '\\', 1) !== false) {
            return ltrim($className, '\\');
        }

        return trim($namespace, '\\') . '\\' . trim($className, '\\');
    }

    /**
     * Returns the FQN for $className with $namespace in front
     *
     * @return string always without a \ in front
     */
    public static function setNamespace($className, $namespace): string
    {
        return trim($namespace, '\\') . '\\' . trim($className, '\\');
    }

    public static function getNamespace($className): ?string
    {
        $className = ltrim($className, '\\');
        if (($pos = mb_strrpos($className, '\\')) !== false) {
            return mb_substr($className, 0, $pos);
        }

        return null;
    }

    public static function getClassName($fqn): string
    {
        $fqn = ltrim($fqn, '\\');
        if (($pos = mb_strrpos($fqn, '\\')) !== false) {
            return mb_substr($fqn, $pos + 1);
        }

        return $fqn;
    }

    /**
     * @param string $class the full qualified class name
     * @return instanceOf $class
     */
    public static function newClassInstance($class, array $constructorArgs): object
    {
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
     */
    public static function hasPublicProperty($object, $property): bool
    {
        return array_key_exists($property, (array) $object);
    }
}
