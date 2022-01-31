<?php

namespace Webforge\Common;

class PHPClassTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->phpClass = new PHPClass(__CLASS__);
    }

    public function testImplementsClassInterface()
    {
        self::assertInstanceOf('Webforge\Common\ClassInterface', $this->phpClass);
    }

    public function testNamespaceAndNameAndFQNAreSetFromFQNString()
    {
        self::assertEquals(__CLASS__, $this->phpClass->getFQN());
        self::assertEquals('PHPClassTest', $this->phpClass->getName());
        self::assertEquals(__NAMESPACE__, $this->phpClass->getNamespace());
    }

    public function testReflectionClassIsReturned()
    {
        self::assertInstanceOf('ReflectionClass', $this->phpClass->getReflection());
    }

    public function testReflectionIsCached()
    {
        $refl = $this->phpClass->getReflection();
        self::assertSame($refl, $this->phpClass->getReflection());
    }

    public function testReflectionIsChangedWhenFQNIsChanged()
    {
        $refl = $this->phpClass->getReflection();

        $this->phpClass->setName('PHPClass');

        self::assertNotSame($refl, $this->phpClass->getReflection(), 'reflection should be refreshed fro setName');
    }

    public function testCanHaveNoNamespaceWithFQNSetter()
    {
        $this->phpClass->setFQN('stdClass');
        self::assertNull($this->phpClass->getNamespace());
    }

    public function testCanHaveNoNamespaceWithNamespaceSetter()
    {
        $this->phpClass->setNamespace(null);
        self::assertNull($this->phpClass->getNamespace());
        self::assertEquals('PHPClassTest', $this->phpClass->getName());
    }

    public function testToStringContainsFQN()
    {
        self::assertStringContainsString(__CLASS__, (string) $this->phpClass);
    }

    public function testReflectionCanbeReplacedForTests()
    {
        $refl = new \ReflectionClass(ClassInterface::class);

        $this->phpClass->injectReflection($refl);
        self::assertSame($refl, $this->phpClass->getReflection());
    }

    public function testEquals()
    {
        self::assertTrue($this->phpClass->equals(new PHPClass(__CLASS__)));
        self::assertFalse($this->phpClass->equals(new PHPClass(__CLASS__.'Nope')));
    }
}
