<?php

namespace Webforge\Common;

use stdClass;
use ArrayIterator;
use Webforge\Collections\TraversableCollection;

class UtilTest extends \PHPUnit\Framework\TestCase
{
    public function testTraversablePreCondition()
    {
        $this->assertInstanceOf('Traversable', new ArrayIterator(array(1,2,3)));
    }

    /**
     * @dataProvider provideAllTypes
     */
    public function testTypeInfoAcceptance($typeSample)
    {
        $this->assertNotEmpty(Util::typeInfo($typeSample));
    }

    /**
     * @dataProvider provideAllTypes
     */
    public function testVarInfoAcceptance($typeSample)
    {
        $this->assertNotEmpty(Util::varInfo($typeSample));
    }

    public function provideAllTypes()
    {
        $tests = array();

        $tests[] = array(
      new stdClass()
    );

        $tests[] = array(
      array('someValue')
    );

        $tests[] = array(
      'string'
    );

        $tests[] = array(
      7
    );

        $tests[] = array(
      true
    );

        $tests[] = array(
      false
    );

        $tests[] = array(
      0.17
    );

        $tests[] = array(
      new TestValueObject('v1', 'v2')
    );

        // how can we create a resource type simple?

        return $tests;
    }


    /**
     * @dataProvider provideCastArray
     */
    public function testCastArray($item, $expected)
    {
        $this->assertEquals(
            $expected,
            Util::castArray($item)
        );
    }

    public static function provideCastArray()
    {
        $tests = array();

        $test = function () use (&$tests) {
            $tests[] = func_get_args();
        };

        $test($iterator = new ArrayIterator(array(1, 2, 3)), array(1,2,3));
        $test(array(1,2,3), array(1, 2, 3));
        $test(new TraversableCollection(array('0'=>'nil', '1'=>'one')), array('0'=>'nil', '1'=>'one'));

        return $tests;
    }
}
