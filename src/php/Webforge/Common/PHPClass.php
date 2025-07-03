<?php

declare(strict_types=1);

namespace Webforge\Common;

use ReflectionClass;
use Webforge\Common\StringUtil as S;

class PHPClass implements ClassInterface, \Stringable
{
  /**
   * @var string
   */
    protected $name;

    /**
     * @var string
     */
    protected $namespace;

    private ?ReflectionClass $reflection = null;

    public function __construct($fqn)
    {
        $this->setFQN($fqn);
    }

    /**
     * Replaces the Namespace and Name of the Class
     *
     * @param string $fqn no \ before
     */
    public function setFQN($fqn): void
    {
        $this->reflection = null;
        if (false !== ($pos = mb_strrpos($fqn, '\\'))) {
            $this->namespace = ltrim(mb_substr($fqn, 0, $pos + 1), '\\'); // +1 to add the trailing slash, see setNamespace
            $this->setName(mb_substr($fqn, $pos));
        } else {
            $this->namespace = null;
            $this->setName($fqn);
        }
    }

    /**
     * Returns the Fully Qualified Name of the Class
     *
     * this is the whole path including Namespace without a backslash before
     */
    public function getFQN(): string
    {
        return $this->namespace . $this->name;
    }

    /**
     * returns the Name of the Class
     *
     * its the Name of the FQN without the Namespace
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the Name of the Class
     *
     * this is not the FQN, its only the FQN without the namespace
     */
    public function setName($name): static
    {
        $this->reflection = null;
        $this->name = trim($name, '\\');
        return $this;
    }

    /**
     * Returns the Namespace
     *
     * @return string The namespace has no \ before and after
     */
    public function getNamespace(): string
    {
        // i think its faster to compute the FQN with concatenation add the trailingslash in the setter and remove the trailslash here
        return isset($this->namespace) ? rtrim($this->namespace, '\\') : null;
    }

    public function setNamespace($ns): static
    {
        $this->reflection = null;
        $this->namespace = $ns != null ? ltrim(S::expand($ns, '\\', S::END), '\\') : null;
        return $this;
    }

    public function getReflection(): ReflectionClass
    {
        if (!isset($this->reflection)) {
            $this->reflection = new ReflectionClass($this->getFQN());
        }

        return $this->reflection;
    }

    /**
     * Used in tests for webforge-types
     */
    public function injectReflection(?\ReflectionClass $refl): static
    {
        $this->reflection = $refl;
        return $this;
    }

    public function equals(ClassInterface $otherClass): bool
    {
        return $this->getFQN() === $otherClass->getFQN();
    }

    public function __toString(): string
    {
        return $this->getFQN();
    }
}
