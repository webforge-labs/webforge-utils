<?php

declare(strict_types=1);

namespace Webforge\Common;

interface ClassInterface
{
  /**
   * @return string the full qualified name (namespace + name without backslash in front)
   */
    public function getFQN(): string;

    /**
     * @return string the name of the class (without namespace)
     */
    public function getName(): string;

    /**
     * @return string the namespace of the class without trailing backslash
     */
    public function getNamespace(): string;

    public function getReflection(): \ReflectionClass;

    public function equals(ClassInterface $other): bool;
}
