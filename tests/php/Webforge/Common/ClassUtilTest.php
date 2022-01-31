<?php

namespace Webforge\Common;

class ClassUtilTest extends \PHPUnit\Framework\TestCase
{
  /**
   * @dataProvider provideGetNamespace
   */
    public function testGetNamespaceTestsAreConciseWithPHP($className, $namespace)
    {
        $refl = new \ReflectionClass($className);

        self::assertEquals($namespace, $refl->getNamespaceName());
    }

    /**
     * @dataProvider provideGetClassName
     */
    public function testGetClassNameTestsAreConciseWithPHP($fqn, $expectedClassName)
    {
        $refl = new \ReflectionClass($fqn);

        self::assertEquals($expectedClassName, $refl->getShortName());
    }

    /**
     * @dataProvider provideGetClassName
     */
    public function testGetClassName($fqn, $expectedClassName)
    {
        self::assertEquals(
            $expectedClassName,
            ClassUtil::getClassName($fqn)
        );
    }

    /**
     * @dataProvider provideGetNamespace
     */
    public function testGetNamespace($className, $expectedNamespace)
    {
        self::assertEquals(
            $expectedNamespace,
            ClassUtil::getNamespace($className)
        );
    }


    /**
     * @dataProvider provideExpandNamespace
     */
    public function testExpandNamespace($className, $namespace, $expectedFQN)
    {
        self::assertEquals(
            $expectedFQN,
            ClassUtil::expandNamespace($className, $namespace)
        );
    }

    public static function provideExpandNamespace()
    {
        $tests = array();

        $test = function () use (&$tests) {
            $tests[] = func_get_args();
        };

        $test('ClassUtil', 'Webforge\Common', 'Webforge\Common\ClassUtil');
        $test('\ClassUtil', 'Webforge\Common', 'Webforge\Common\ClassUtil');
        $test('ClassUtil', 'Webforge\Common\\', 'Webforge\Common\ClassUtil');
        $test('ClassUtil', '\Webforge\Common\\', 'Webforge\Common\ClassUtil');
        $test('ClassUtil', '\Webforge\Common', 'Webforge\Common\ClassUtil');
        $test('\ClassUtil', '\Webforge\Common', 'Webforge\Common\ClassUtil');

        $test('Webforge\Common\ArrayUtil', '\Webforge\Common', 'Webforge\Common\ArrayUtil');
        $test('\Webforge\Common\ArrayUtil', '\Webforge\Common', 'Webforge\Common\ArrayUtil');
        $test('\Webforge\Common\ArrayUtil', 'Webforge\Common', 'Webforge\Common\ArrayUtil');
        $test('Webforge\Common\ArrayUtil', 'Webforge\Common', 'Webforge\Common\ArrayUtil');

        return $tests;
    }

    /**
     * @dataProvider provideSetNamespace
     */
    public function testSetNamespace($className, $namespace, $expectedFQN)
    {
        self::assertEquals($expectedFQN, ClassUtil::setNamespace($className, $namespace));
    }

    public static function provideSetNamespace()
    {
        $tests = array();

        $test = function () use (&$tests) {
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

    public static function provideGetNamespace()
    {
        $tests = array();

        $test = function () use (&$tests) {
            $tests[] = func_get_args();
        };

        // unfortunately classes do have to exist for php reflection

        $test('Webforge\Common\ClassUtil', 'Webforge\Common');
        $test('Traversable', null);

        // exception?
        $test('\Webforge\Common\ClassUtil', 'Webforge\Common');
        $test('\\Traversable', null);

        // undefined! (phpReflection throws error)
        //$test('\Webforge\Common\ClassUtil\\', 'Webforge\Common\ClassUtil');


        return $tests;
    }

    public static function provideGetClassName()
    {
        $tests = array();

        $test = function () use (&$tests) {
            $tests[] = func_get_args();
        };

        // unfortunately classes do have to exist for php reflection

        $test('Webforge\Common\ClassUtil', 'ClassUtil');
        $test('Traversable', 'Traversable');

        $test('\Webforge\Common\ClassUtil', 'ClassUtil');
        $test('\\Traversable', 'Traversable');

        // undefined! (phpReflection throws error)
        //$test('\Webforge\Common\ClassUtil\\', 'Webforge\Common\ClassUtil');


        return $tests;
    }

    public function testCreatesNewInstancesOfObjectsWithNewClassInstance()
    {
        $refl = ClassUtil::newClassInstance('ReflectionClass', array(__CLASS__));
        self::assertEquals(__CLASS__, $refl->getName());


        $refl = ClassUtil::newClassInstance(new \ReflectionClass('ReflectionClass'), array(__CLASS__));
        self::assertEquals(__CLASS__, $refl->getName());
    }

    public function testNewClassInstanceCanOnlyDoStringOrReflectionClass()
    {
        $this->expectException(\InvalidArgumentException::class);
        ClassUtil::newClassInstance(7, array());
    }
}
