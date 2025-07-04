<?php declare(strict_types=1);

namespace Webforge\Common;

class PHPClassTest extends \PHPUnit\Framework\TestCase
{
    private PHPClass $phpClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpClass = new PHPClass(self::class);
    }

    public function testImplementsClassInterface(): void
    {
        self::assertInstanceOf(\Webforge\Common\ClassInterface::class, $this->phpClass);
    }

    public function testNamespaceAndNameAndFQNAreSetFromFQNString(): void
    {
        self::assertEquals(self::class, $this->phpClass->getFQN());
        self::assertEquals('PHPClassTest', $this->phpClass->getName());
        self::assertEquals(__NAMESPACE__, $this->phpClass->getNamespace());
    }

    public function testReflectionClassIsReturned(): void
    {
        self::assertInstanceOf('ReflectionClass', $this->phpClass->getReflection());
    }

    public function testReflectionIsCached(): void
    {
        $refl = $this->phpClass->getReflection();
        self::assertSame($refl, $this->phpClass->getReflection());
    }

    public function testReflectionIsChangedWhenFQNIsChanged(): void
    {
        $refl = $this->phpClass->getReflection();

        $this->phpClass->setName('PHPClass');

        self::assertNotSame($refl, $this->phpClass->getReflection(), 'reflection should be refreshed fro setName');
    }

    public function testCanHaveNoNamespaceWithFQNSetter(): void
    {
        $this->phpClass->setFQN('stdClass');
        self::assertNull($this->phpClass->getNamespace());
    }

    public function testCanHaveNoNamespaceWithNamespaceSetter(): void
    {
        $this->phpClass->setNamespace(null);
        self::assertNull($this->phpClass->getNamespace());
        self::assertEquals('PHPClassTest', $this->phpClass->getName());
    }

    public function testToStringContainsFQN(): void
    {
        self::assertStringContainsString(self::class, (string) $this->phpClass);
    }

    public function testReflectionCanbeReplacedForTests(): void
    {
        $refl = new \ReflectionClass(ClassInterface::class);

        $this->phpClass->injectReflection($refl);
        self::assertSame($refl, $this->phpClass->getReflection());
    }

    public function testEquals(): void
    {
        self::assertTrue($this->phpClass->equals(new PHPClass(self::class)));
        self::assertFalse($this->phpClass->equals(new PHPClass(self::class . 'Nope')));
    }
}
