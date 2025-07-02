<?php

namespace Webforge\Common;

use ArrayIterator;
use stdClass;
use Webforge\Collections\TraversableCollection;

class UtilTest extends \PHPUnit\Framework\TestCase
{
    public function testTraversablePreCondition(): void
    {
        $this->assertInstanceOf('Traversable', new ArrayIterator([1, 2, 3]));
    }

    /**
     * @dataProvider provideAllTypes
     */
    public function testTypeInfoAcceptance($typeSample): void
    {
        $this->assertNotEmpty(Util::typeInfo($typeSample));
    }

    /**
     * @dataProvider provideAllTypes
     */
    public function testVarInfoAcceptance($typeSample): void
    {
        $this->assertNotEmpty(Util::varInfo($typeSample));
    }

    public static function provideAllTypes()
    {
        $tests = [];

        $tests[] = [new stdClass()];

        $tests[] = [['someValue']];

        $tests[] = ['string'];

        $tests[] = [7];

        $tests[] = [true];

        $tests[] = [false];

        $tests[] = [0.17];

        $tests[] = [new TestValueObject('v1', 'v2')];

        // how can we create a resource type simple?

        return $tests;
    }

    /**
     * @dataProvider provideCastArray
     */
    public function testCastArray($item, $expected): void
    {
        $this->assertEquals(
            $expected, Util::castArray($item),
        );
    }

    public static function provideCastArray()
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test($iterator = new ArrayIterator([1, 2, 3]), [1, 2, 3]);
        $test([1, 2, 3], [1, 2, 3]);
        $test(new TraversableCollection(['0' => 'nil', '1' => 'one']), ['0' => 'nil', '1' => 'one']);

        return $tests;
    }
}
