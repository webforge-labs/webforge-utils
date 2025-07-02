<?php

namespace Psc;

use Webforge\Common\ArrayUtil as A;
use Webforge\Common\PHPClass;

class ArrayUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testPeek(): void
    {
        $array = ['s1','s2'];

        self::assertEquals('s2', A::peek($array));
        self::assertEquals(['s1','s2'], $array); // Not popped

        array_pop($array);
        self::assertEquals('s1', A::peek($array));
        self::assertEquals(['s1'], $array); // Not popped (but popped before)
    }

    public function testFirst(): void
    {
        $array = ['f1','f2'];
        self::assertEquals('f1', A::first($array));
        self::assertEquals(['f1','f2'], $array);
    }

    public function testPush(): void
    {
        $array = ['f1','f2'];
        self::assertEquals(['f1','f2','f3'], A::push($array, 'f3'));
        self::assertEquals(['f1','f2'], $array);
    }

    public function testInsert(): void
    {
        $array = ['f1','f3'];

        A::insert($array, 'f2', 1);
        self::assertEquals(['f1','f2','f3'], $array);

        A::insert($array, 'f0', 0);
        self::assertEquals(['f0','f1','f2','f3'], $array);

        A::insert($array, 'f4', 4);
        self::assertEquals(['f0','f1','f2','f3','f4'], $array);

        A::insert($array, 'fbL', -1); // before last position
        self::assertEquals(['f0','f1','f2','f3','fbL','f4'], $array);

        A::insert($array, 'fL', A::END); // at the end
        self::assertEquals(['f0','f1','f2','f3','fbL','f4','fL'], $array);

        A::insert($array, 'f-2', -2); // before position -2
        self::assertEquals(['f0','f1','f2','f3','fbL','f-2','f4','fL'], $array);
    }

    public function testInsert0PrependsToArray(): void
    {
        $array = ['two'];

        A::insert($array, 'one', 0);
        self::assertEquals(['one','two'], $array);
    }

    public function testIRemoveWithMatch_RemovesTheElement(): void
    {
        $array = ['0','1','2','3'];
        A::remove($array, '1');

        self::assertEquals(['0', '2', '3'], $array);
    }

    public function testRemoveWithNoMatch_RemovesTheElement_searchStrictEnabledPerDefault(): void
    {
        $array = ['0','1','2','3'];
        A::remove($array, 1); // searchstrict per default

        self::assertEquals(['0', '1', '2', '3'], $array);
    }

    public function testRemoveWithMatch_RemovesTheElement_searchStrictCanBeDisabled(): void
    {
        $array = ['0','1','2','3'];
        A::remove($array, 1, $searchStrict = false); // searchstrict per default

        self::assertEquals(['0', '2', '3'], $array);
    }

    public function testRemovesOnlyFirstMatchFromArray(): void
    {
        $array = ['0','1','2','3', '1'];
        A::remove($array, '1');

        self::assertEquals(['0', '2', '3', '1'], $array);
    }

    public function testInsertArrayPositiveIndexInsertsBeforeIndex(): void
    {
        $array = [0,1,4,5];

        A::insertArray($array, [2,3], 2);
        self::assertEquals([0,1,2,3,4,5], $array);
    }

    public function testInsertArrayNegativeIndexInsertsBeforeIndexCountedFromEndOfarray(): void
    {
        $array = [0,1,4,5];

        A::insertArray($array, [2,3], -2);
        self::assertEquals([0,1,2,3,4,5], $array);
    }

    public function testInsertArrayWithENDConstantAppends(): void
    {
        $array = [0,1,2,3];

        A::insertArray($array, [4,5], A::END);
        self::assertEquals([0,1,2,3,4,5], $array);
    }

    public function testInsertArrayWith0Prepends(): void
    {
        $array = [2,3,4,5];

        A::insertArray($array, [0,1], 0);
        self::assertEquals([0,1,2,3,4,5], $array);
    }

    public function testSet(): void
    {
        $php = [];
        $my = [];

        $php[0] = null;
        A::set($my, 0, null);
        self::assertEquals($php, $my);

        $var = 'v';
        $php[1] = $var;
        A::set($my, 1, $var);
        self::assertEquals($php, $my);

        $php[5] = 'nix';
        A::set($my, 5, 'nix');
        self::assertEquals($php, $my);

        $php[0] = '0';
        A::set($my, 0, '0');
        self::assertEquals($php, $my);
    }

    /**
     * @dataProvider provideInsert_OOB
     */
    public function testInsert_OOB($array, $offset): void
    {
        $this->expectException(\OutOfBoundsException::class);
        A::insert($array, null, $offset);
    }

    public static function provideInsert_OOB()
    {
        $tests = [];

        $test = function ($array, $offset) use (&$tests): void {
            $tests[] = [$array, $offset];
        };

        $test([0,1,2], 4);
        $test([1,2,3], -5);

        return $tests;
    }

    public function testJoin_normal(): void
    {
        $array = ['eins','zwei','drei'];

        self::assertEquals('->eins<- ->zwei<- ->drei<- ', A::join($array, '->%s<- '));
        self::assertEquals('->eins<- ->zwei<- ->drei<- ', A::joinc($array, '->%s<- '));
    }

    public function testJoinWithInnerArrays(): void
    {
        $array = ['one', ['thatsbad'], 'three'];

        self::assertEquals('|one| |Array| |three| ', A::join($array, '|%s| '));
    }

    public function testImplode_normal(): void
    {
        $array = [[0 => 'eins'],[0 => 'zwei'],[0 => 'drei']];

        self::assertEquals('eins<- ->zwei<- ->drei', A::implode($array, '<- ->', function ($value) {
            return $value[0];
        }));
    }

    public function testImplode_keys(): void
    {
        $array = ['eins','zwei','drei'];

        self::assertEquals(
            '[0]EINS<- ->[1]ZWEI<- ->[2]DREI',
            A::implode(
                            $array,
                            '<- ->',
                            function ($value, $key) {
                              return '[' . $key . ']' . mb_strtoupper($value);
                          }
                        )
        );
    }

    public function testJoin_withKeys(): void
    {
        $array = ['1' => 'eins','2' => 'zwei','3' => 'drei'];

        self::assertEquals('[1]eins [2]zwei [3]drei ', A::join($array, '[%2$s]%1$s '));
        self::assertEquals('[1]eins [2]zwei [3]drei ', A::joinc($array, '[%2$s]%1$s '));
        self::assertEquals('[1]EINS [2]ZWEI [3]DREI ', A::joinc($array, '[%2$s]%1$s ', function ($value) {
            return mb_strtoupper($value);
        }));
    }

    public function testIndex(): void
    {
        $array = [];
        $array[1] = 7;
        $array['int'] = 8;

        self::assertEquals(7, A::index($array, 1));
        self::assertEquals(8, A::index($array, 'int'));
        self::assertEquals('blubb', A::index(['blubb'], 0));
    }

    public function testIndexRef(): void
    {
        $array = [];
        $array[1] = 7;
        $array['int'] = 8;

        $ref = & A::indexRef($array, 'int');
        $ref = 'val';
        self::assertEquals('val', $array['int']);

        $ref = & A::indexRef($array, 1);
        $ref++;
        self::assertEquals(8, $array[1]);
    }

    public function testShuffle_doesNotChangeOriginal(): void
    {
        $elems = [0,1,2,3,4,5];

        $shuffled = A::shuffle($elems);

        if ($elems != $shuffled) {
            self::assertEquals([0,1,2,3,4,5], $elems);
        } else {
            $this->markTestSkipped('Shuffle shuffled not the array');
        }
    }

    public function testRandomValue(): void
    {
        $elems = ['one','two','three'];
        for ($i = 0; $i <= 10; $i++) {
            self::assertNotEmpty($rEl = A::randomValue($elems)); // no value is empty in $elems
      self::assertContains($rEl, $elems); // constrain that rEl is out of elems
        }
    }

    public function testKeys(): void
    {
        $vars = [
      'mainTemplate' => [
        'name' => 'main',
        'display' => true,
        'subTemplate' => [
          'value1' => 'info1',
          'value2' => 'info2'
        ]
      ]
    ];

        self::assertEquals(
            ['mainTemplate' =>
                                ['name' => 'string',
                                      'display' => 'boolean',
                                      'subTemplate' => [
                                        'value1' => 'string',
                                        'value2' => 'string'
                                      ]
                                    ]
                             ],
            A::keys($vars)
        );
    }

    public function testMapKeys(): void
    {
        $array = ['key1' => 'value1',
                   'key2' => 'value2',
                   'key3' => 'value3'];

        self::assertEquals(
            ['Key1' => 'value1',
                              'Key2' => 'value2',
                              'Key3' => 'value3'
                             ],
            A::mapKeys(
                            $array,
                            function ($key) {
                                       return ucfirst($key);
                                   }
                        )
        );

        self::assertEquals(
            ['Key1' => 'VALUE1',
                              'Key2' => 'VALUE2',
                              'Key3' => 'VALUE3'
                             ],
            A::mapKeys(
                            $array,
                            function ($key) {
                                       return ucfirst($key);
                                   },
                            function ($value) {
                                       return strtoupper($value);
                                   }
                        )
        );
    }

    public function testGetType(): void
    {
        self::assertEquals('assoc', A::getType(['my' => 'values','are' => 'all','assoc' => 'keyed']));
        self::assertEquals('numeric', A::getType(['my','values','are', 'numeric']));
        self::assertEquals('numeric', A::getType([1 => 'my',7 => 'values',4 => 'are', 6 => 'numeric',7 => 'but shuffled']));
        self::assertEquals('assoc', A::getType([1 => 'my',7 => 'values',4 => 'are',    'mixed' => 'and',    6 => 'numeric']));
    }

    public function testIsOnlyType(): void
    {
        self::assertTrue(A::isOnlyType(['string1','string2','string3'], 'string'));
        self::assertTrue(A::isOnlyType([1,2,3], 'int'));
        self::assertTrue(A::isOnlyType([[],[],[]], 'array'));
        self::assertTrue(A::isOnlyType([[],[],[3]], 'array')); // look closely
        self::assertTrue(A::isOnlyType([[],[[]], []], 'array'));

        self::assertFalse(A::isOnlyType([1,'s',3], 'int'));
        self::assertFalse(A::isOnlyType(['b','s',3], 'string'));
        self::assertFalse(A::isOnlyType([[],null, []], 'array'));

        self::assertTrue(A::isOnlyType([], 'string'));
        self::assertTrue(A::isOnlyType([], 'int'));
        self::assertTrue(A::isOnlyType([], 'array'));
        self::assertTrue(A::isOnlyType([], 'float'));
    }

    public function testFillUp(): void
    {
        $array = [false];

        self::assertEquals([false,true,true], A::fillUp($array, true, 3));
        self::assertEquals([false], $array); // not modified

        self::assertEquals([false], A::fillUp($array, true, 0));
        self::assertEquals([false], $array); // not modified

        self::assertEquals([false], A::fillUp($array, true, -1));
        self::assertEquals([false], $array); // not modified
    }

    /**
     * @dataProvider getPluckTests
     */
    public function testPluck(array $input, $prop, array $expected): void
    {
        self::assertEquals(
            $expected,
            A::pluck($input, $prop)
        );
    }

    public static function getPluckTests()
    {
        $tests = [];

        $tests[] = [
      json_decode('[{"name": "moe", "age": 40}, {"name": "larry", "age": 50}, {"name": "curly", "age": 60}]'),
      'name',
      ['moe','larry','curly']
    ];

        // special case where the sample object tester is NULL
        $tests[] = [
      json_decode('[{"name": null, "age": 40}, {"name": "larry", "age": 50}, {"name": "curly", "age": 60}]'),
      'name',
      [null,'larry','curly']
    ];

        $tests[] = [
      [new PHPClass('Namespaced\one'), new PHPClass('Namespaced\two')],
      'name',
      ['one', 'two']
    ];

        $tests[] = [
      [],
      'not there',
      []
    ];

        return $tests;
    }

    public function testArrayFilterForKeys(): void
    {
        $headers = [
      'Content-Type' => 'application/json',
      'Content-Length' => 723,
      'Accept' => '*/*'
    ];

        $filter = function ($headerName, $headerValue) {
            return $headerName != 'Content-Length';
        };

        self::assertEquals(
            [
        'Content-Type' => 'application/json',
        'Accept' => '*/*'
      ],
            A::filterKeys($headers, $filter),
            'A::filterKeys does not filter'
        );
    }

    public function testIndexByWithObjectProperty(): void
    {
        $array = json_decode('[
  { "dir": "left", "code": 97 },
  { "dir": "right", "code": 100 }
]');

        self::assertEquals(
            (array) json_decode('{ "left": { "dir": "left", "code": 97 }, "right": { "dir": "right", "code": 100 } }'),
            A::indexBy($array, 'dir')
        );
    }

    public function testReindexWithClosure(): void
    {
        $array = json_decode('[
  { "dir": "left", "code": 97 },
  { "dir": "right", "code": 100 }
]');

        self::assertEquals(
            (array) json_decode('{ "left": { "dir": "left", "code": 97 }, "right": { "dir": "right", "code": 100 } }'),
            A::indexBy($array, function ($item) {
          return $item->dir;
      })
        );
    }

    public function testIndexByWithEmptyArray(): void
    {
        self::assertEquals(
            [],
            A::indexBy([], 'index')
        );
    }
}
