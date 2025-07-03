<?php declare(strict_types=1);

namespace Webforge\Common;

class ClassUtilTest extends \PHPUnit\Framework\TestCase
{
  /**
   * @dataProvider provideGetNamespace
   */
    public function testGetNamespaceTestsAreConciseWithPHP($className, $namespace): void
    {
        $refl = new \ReflectionClass($className);

        self::assertEquals($namespace, $refl->getNamespaceName());
    }

    /**
     * @dataProvider provideGetClassName
     */
    public function testGetClassNameTestsAreConciseWithPHP($fqn, $expectedClassName): void
    {
        $refl = new \ReflectionClass($fqn);

        self::assertEquals($expectedClassName, $refl->getShortName());
    }

    /**
     * @dataProvider provideGetClassName
     */
    public function testGetClassName($fqn, $expectedClassName): void
    {
        self::assertEquals(
            $expectedClassName,
            ClassUtil::getClassName($fqn)
        );
    }

    /**
     * @dataProvider provideGetNamespace
     */
    public function testGetNamespace($className, $expectedNamespace): void
    {
        self::assertEquals(
            $expectedNamespace,
            ClassUtil::getNamespace($className)
        );
    }

    /**
     * @dataProvider provideExpandNamespace
     */
    public function testExpandNamespace($className, $namespace, $expectedFQN): void
    {
        self::assertEquals(
            $expectedFQN,
            ClassUtil::expandNamespace($className, $namespace)
        );
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideExpandNamespace(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test('ClassUtil', 'Webforge\Common', \Webforge\Common\ClassUtil::class);
        $test('\ClassUtil', 'Webforge\Common', \Webforge\Common\ClassUtil::class);
        $test('ClassUtil', 'Webforge\Common\\', \Webforge\Common\ClassUtil::class);
        $test('ClassUtil', '\Webforge\Common\\', \Webforge\Common\ClassUtil::class);
        $test('ClassUtil', '\Webforge\Common', \Webforge\Common\ClassUtil::class);
        $test('\ClassUtil', '\Webforge\Common', \Webforge\Common\ClassUtil::class);

        $test(\Webforge\Common\ArrayUtil::class, '\Webforge\Common', \Webforge\Common\ArrayUtil::class);
        $test(\Webforge\Common\ArrayUtil::class, '\Webforge\Common', \Webforge\Common\ArrayUtil::class);
        $test(\Webforge\Common\ArrayUtil::class, 'Webforge\Common', \Webforge\Common\ArrayUtil::class);
        $test(\Webforge\Common\ArrayUtil::class, 'Webforge\Common', \Webforge\Common\ArrayUtil::class);

        return $tests;
    }

    /**
     * @dataProvider provideSetNamespace
     */
    public function testSetNamespace($className, $namespace, $expectedFQN): void
    {
        self::assertEquals($expectedFQN, ClassUtil::setNamespace($className, $namespace));
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideSetNamespace(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test('CMS\Container', 'Webforge', 'Webforge\CMS\Container');
        $test('\CMS\Container', 'Webforge', 'Webforge\CMS\Container');
        $test('\CMS\Container\\', 'Webforge', 'Webforge\CMS\Container');

        $test('Container', 'Webforge\CMS', 'Webforge\CMS\Container');
        $test('\Container', 'Webforge\CMS', 'Webforge\CMS\Container');
        $test('\Container\\', 'Webforge\CMS', 'Webforge\CMS\Container');

        return $tests;
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideGetNamespace(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        // unfortunately classes do have to exist for php reflection

        $test(\Webforge\Common\ClassUtil::class, 'Webforge\Common');
        $test('Traversable', null);

        // exception?
        $test(\Webforge\Common\ClassUtil::class, 'Webforge\Common');
        $test('\\Traversable', null);

        // undefined! (phpReflection throws error)
        //$test('\Webforge\Common\ClassUtil\\', 'Webforge\Common\ClassUtil');

        return $tests;
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideGetClassName(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        // unfortunately classes do have to exist for php reflection

        $test(\Webforge\Common\ClassUtil::class, 'ClassUtil');
        $test('Traversable', 'Traversable');

        $test(\Webforge\Common\ClassUtil::class, 'ClassUtil');
        $test('\\Traversable', 'Traversable');

        // undefined! (phpReflection throws error)
        //$test('\Webforge\Common\ClassUtil\\', 'Webforge\Common\ClassUtil');

        return $tests;
    }

    public function testCreatesNewInstancesOfObjectsWithNewClassInstance(): void
    {
        $refl = ClassUtil::newClassInstance('ReflectionClass', [self::class]);
        self::assertEquals(self::class, $refl->getName());

        $refl = ClassUtil::newClassInstance(new \ReflectionClass('ReflectionClass'), [self::class]);
        self::assertEquals(self::class, $refl->getName());
    }

    public function testNewClassInstanceCanOnlyDoStringOrReflectionClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ClassUtil::newClassInstance(7, []);
    }
}
