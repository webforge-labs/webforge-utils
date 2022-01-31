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
        $this->assertInstanceOf('Webforge\Common\ClassInterface', $this->phpClass);
    }

    public function testNamespaceAndNameAndFQNAreSetFromFQNString()
    {
        $this->assertEquals(__CLASS__, $this->phpClass->getFQN());
        $this->assertEquals('PHPClassTest', $this->phpClass->getName());
        $this->assertEquals(__NAMESPACE__, $this->phpClass->getNamespace());
    }

    public function testReflectionClassIsReturned()
    {
        $this->assertInstanceOf('ReflectionClass', $this->phpClass->getReflection());
    }

    public function testReflectionIsCached()
    {
        $refl = $this->phpClass->getReflection();
        $this->assertSame($refl, $this->phpClass->getReflection());
    }

    public function testReflectionIsChangedWhenFQNIsChanged()
    {
        $refl = $this->phpClass->getReflection();

        $this->phpClass->setName('PHPClass');

        $this->assertNotSame($refl, $this->phpClass->getReflection(), 'reflection should be refreshed fro setName');
    }

    public function testCanHaveNoNamespaceWithFQNSetter()
    {
        $this->phpClass->setFQN('stdClass');
        $this->assertNull($this->phpClass->getNamespace());
    }

    public function testCanHaveNoNamespaceWithNamespaceSetter()
    {
        $this->phpClass->setNamespace(null);
        $this->assertNull($this->phpClass->getNamespace());
        $this->assertEquals('PHPClassTest', $this->phpClass->getName());
    }

    public function testToStringContainsFQN()
    {
        $this->assertContains(__CLASS__, (string) $this->phpClass);
    }

    public function testReflectionCanbeReplacedForTests()
    {
        $this->phpClass->injectReflection($refl = $this->getMock('ReflectionClass', array(), array('Webforge\Common\ClassInterface')));
        $this->assertSame($refl, $this->phpClass->getReflection());
    }

    public function testEquals()
    {
        $this->assertTrue($this->phpClass->equals(new PHPClass(__CLASS__)));
        $this->assertFalse($this->phpClass->equals(new PHPClass(__CLASS__.'Nope')));
    }
}
