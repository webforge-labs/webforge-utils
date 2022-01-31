<?php

namespace Psc;

use PHPUnit\Framework\TestCase;
use Webforge\Common\ArrayUtil as A;
use Webforge\Common\PHPClass;

class ArrayUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testPeek()
    {
        $array = array('s1','s2');

        self::assertEquals('s2', A::peek($array));
        self::assertEquals(array('s1','s2'), $array); // Not popped

        array_pop($array);
        self::assertEquals('s1', A::peek($array));
        self::assertEquals(array('s1'), $array); // Not popped (but popped before)
    }

    public function testFirst()
    {
        $array = array('f1','f2');
        self::assertEquals('f1', A::first($array));
        self::assertEquals(array('f1','f2'), $array);
    }

    public function testPush()
    {
        $array = array('f1','f2');
        self::assertEquals(array('f1','f2','f3'), A::push($array, 'f3'));
        self::assertEquals(array('f1','f2'), $array);
    }

    public function testInsert()
    {
        $array = array('f1','f3');

        A::insert($array, 'f2', 1);
        self::assertEquals(array('f1','f2','f3'), $array);

        A::insert($array, 'f0', 0);
        self::assertEquals(array('f0','f1','f2','f3'), $array);

        A::insert($array, 'f4', 4);
        self::assertEquals(array('f0','f1','f2','f3','f4'), $array);

        A::insert($array, 'fbL', -1); // before last position
        self::assertEquals(array('f0','f1','f2','f3','fbL','f4'), $array);

        A::insert($array, 'fL', A::END); // at the end
        self::assertEquals(array('f0','f1','f2','f3','fbL','f4','fL'), $array);

        A::insert($array, 'f-2', -2); // before position -2
        self::assertEquals(array('f0','f1','f2','f3','fbL','f-2','f4','fL'), $array);
    }

    public function testInsert0PrependsToArray()
    {
        $array = array('two');

        A::insert($array, 'one', 0);
        self::assertEquals(array('one','two'), $array);
    }

    public function testIRemoveWithMatch_RemovesTheElement()
    {
        $array = array('0','1','2','3');
        A::remove($array, '1');

        self::assertEquals(array('0', '2', '3'), $array);
    }

    public function testRemoveWithNoMatch_RemovesTheElement_searchStrictEnabledPerDefault()
    {
        $array = array('0','1','2','3');
        A::remove($array, 1); // searchstrict per default

        self::assertEquals(array('0', '1', '2', '3'), $array);
    }

    public function testRemoveWithMatch_RemovesTheElement_searchStrictCanBeDisabled()
    {
        $array = array('0','1','2','3');
        A::remove($array, 1, $searchStrict = false); // searchstrict per default

        self::assertEquals(array('0', '2', '3'), $array);
    }

    public function testRemovesOnlyFirstMatchFromArray()
    {
        $array = array('0','1','2','3', '1');
        A::remove($array, '1');

        self::assertEquals(array('0', '2', '3', '1'), $array);
    }

    public function testInsertArrayPositiveIndexInsertsBeforeIndex()
    {
        $array = array(0,1,4,5);

        A::insertArray($array, array(2,3), 2);
        self::assertEquals(array(0,1,2,3,4,5), $array);
    }

    public function testInsertArrayNegativeIndexInsertsBeforeIndexCountedFromEndOfarray()
    {
        $array = array(0,1,4,5);

        A::insertArray($array, array(2,3), -2);
        self::assertEquals(array(0,1,2,3,4,5), $array);
    }

    public function testInsertArrayWithENDConstantAppends()
    {
        $array = array(0,1,2,3);

        A::insertArray($array, array(4,5), A::END);
        self::assertEquals(array(0,1,2,3,4,5), $array);
    }

    public function testInsertArrayWith0Prepends()
    {
        $array = array(2,3,4,5);

        A::insertArray($array, array(0,1), 0);
        self::assertEquals(array(0,1,2,3,4,5), $array);
    }


    public function testSet()
    {
        $php = array();
        $my = array();

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
    public function testInsert_OOB($array, $offset)
    {
        $this->expectException(\OutOfBoundsException::class);
        A::insert($array, null, $offset);
    }


    public static function provideInsert_OOB()
    {
        $tests = array();

        $test = function ($array, $offset) use (&$tests) {
            $tests[] = array($array, $offset);
        };

        $test(array(0,1,2), 4);
        $test(array(1,2,3), -5);

        return $tests;
    }

    public function testJoin_normal()
    {
        $array = array('eins','zwei','drei');

        self::assertEquals('->eins<- ->zwei<- ->drei<- ', A::join($array, '->%s<- '));
        self::assertEquals('->eins<- ->zwei<- ->drei<- ', A::joinc($array, '->%s<- '));
    }

    public function testJoinWithInnerArrays()
    {
        $array = array('one', array('thatsbad'), 'three');

        self::assertEquals('|one| |Array| |three| ', A::join($array, '|%s| '));
    }

    public function testImplode_normal()
    {
        $array = array(array(0=>'eins'),array(0=>'zwei'),array(0=>'drei'));

        self::assertEquals('eins<- ->zwei<- ->drei', A::implode($array, '<- ->', function ($value) {
            return $value[0];
        }));
    }

    public function testImplode_keys()
    {
        $array = array('eins','zwei','drei');

        self::assertEquals(
            '[0]EINS<- ->[1]ZWEI<- ->[2]DREI',
            A::implode(
                            $array,
                            '<- ->',
                            function ($value, $key) {
                              return '['.$key.']'.mb_strtoupper($value);
                          }
                        )
        );
    }

    public function testJoin_withKeys()
    {
        $array = array('1'=>'eins','2'=>'zwei','3'=>'drei');

        self::assertEquals('[1]eins [2]zwei [3]drei ', A::join($array, '[%2$s]%1$s '));
        self::assertEquals('[1]eins [2]zwei [3]drei ', A::joinc($array, '[%2$s]%1$s '));
        self::assertEquals('[1]EINS [2]ZWEI [3]DREI ', A::joinc($array, '[%2$s]%1$s ', function ($value) {
            return mb_strtoupper($value);
        }));
    }

    public function testIndex()
    {
        $array = array();
        $array[1] = 7;
        $array['int'] = 8;

        self::assertEquals(7, A::index($array, 1));
        self::assertEquals(8, A::index($array, 'int'));
        self::assertEquals('blubb', A::index(array('blubb'), 0));
    }

    public function testIndexRef()
    {
        $array = array();
        $array[1] = 7;
        $array['int'] = 8;

        $ref =& A::indexRef($array, 'int');
        $ref = 'val';
        self::assertEquals('val', $array['int']);

        $ref =& A::indexRef($array, 1);
        $ref++;
        self::assertEquals(8, $array[1]);
    }

    public function testShuffle_doesNotChangeOriginal()
    {
        $elems = array(0,1,2,3,4,5);

        $shuffled = A::shuffle($elems);

        if ($elems != $shuffled) {
            self::assertEquals(array(0,1,2,3,4,5), $elems);
        } else {
            $this->markTestSkipped('Shuffle shuffled not the array');
        }
    }

    public function testRandomValue()
    {
        $elems = array('one','two','three');
        for ($i = 0; $i <= 10; $i++) {
            self::assertNotEmpty($rEl = A::randomValue($elems)); // no value is empty in $elems
      self::assertContains($rEl, $elems); // constrain that rEl is out of elems
        }
    }

    public function testKeys()
    {
        $vars = array(
      'mainTemplate'=>array(
        'name'=>'main',
        'display'=>true,
        'subTemplate'=>array(
          'value1'=>'info1',
          'value2'=>'info2'
        )
      )
    );

        self::assertEquals(
            array('mainTemplate'=>
                                array('name'=>'string',
                                      'display'=>'boolean',
                                      'subTemplate'=>array(
                                        'value1'=>'string',
                                        'value2'=>'string'
                                      )
                                    )
                             ),
            A::keys($vars)
        );
    }


    public function testMapKeys()
    {
        $array = array('key1'=>'value1',
                   'key2'=>'value2',
                   'key3'=>'value3');

        self::assertEquals(
            array('Key1'=>'value1',
                              'Key2'=>'value2',
                              'Key3'=>'value3'
                             ),
            A::mapKeys(
                            $array,
                            function ($key) {
                                       return ucfirst($key);
                                   }
                        )
        );

        self::assertEquals(
            array('Key1'=>'VALUE1',
                              'Key2'=>'VALUE2',
                              'Key3'=>'VALUE3'
                             ),
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

    public function testGetType()
    {
        self::assertEquals('assoc', A::getType(array('my'=>'values','are'=>'all','assoc'=>'keyed')));
        self::assertEquals('numeric', A::getType(array('my','values','are', 'numeric')));
        self::assertEquals('numeric', A::getType(array(1=>'my',7=>'values',4=>'are', 6=>'numeric',7=>'but shuffled')));
        self::assertEquals('assoc', A::getType(array(1=>'my',7=>'values',4=>'are',    'mixed'=>'and',    6=>'numeric')));
    }

    public function testIsOnlyType()
    {
        self::assertTrue(A::isOnlyType(array('string1','string2','string3'), 'string'));
        self::assertTrue(A::isOnlyType(array(1,2,3), 'int'));
        self::assertTrue(A::isOnlyType(array(array(),array(),array()), 'array'));
        self::assertTrue(A::isOnlyType(array(array(),array(),array(3)), 'array')); // look closely
        self::assertTrue(A::isOnlyType(array(array(),array(array()), array()), 'array'));

        self::assertFalse(A::isOnlyType(array(1,'s',3), 'int'));
        self::assertFalse(A::isOnlyType(array('b','s',3), 'string'));
        self::assertFalse(A::isOnlyType(array(array(),null, array()), 'array'));

        self::assertTrue(A::isOnlyType(array(), 'string'));
        self::assertTrue(A::isOnlyType(array(), 'int'));
        self::assertTrue(A::isOnlyType(array(), 'array'));
        self::assertTrue(A::isOnlyType(array(), 'float'));
    }

    public function testFillUp()
    {
        $array = array(false);

        self::assertEquals(array(false,true,true), A::fillUp($array, true, 3));
        self::assertEquals(array(false), $array); // not modified

        self::assertEquals(array(false), A::fillUp($array, true, 0));
        self::assertEquals(array(false), $array); // not modified

        self::assertEquals(array(false), A::fillUp($array, true, -1));
        self::assertEquals(array(false), $array); // not modified
    }

    /**
     * @dataProvider getPluckTests
     */
    public function testPluck(array $input, $prop, array $expected)
    {
        self::assertEquals(
            $expected,
            A::pluck($input, $prop)
        );
    }

    public static function getPluckTests()
    {
        $tests = array();

        $tests[] = array(
      json_decode('[{"name": "moe", "age": 40}, {"name": "larry", "age": 50}, {"name": "curly", "age": 60}]'),
      'name',
      array('moe','larry','curly')
    );

        // special case where the sample object tester is NULL
        $tests[] = array(
      json_decode('[{"name": null, "age": 40}, {"name": "larry", "age": 50}, {"name": "curly", "age": 60}]'),
      'name',
      array(null,'larry','curly')
    );

        $tests[] = array(
      array(new PHPClass('Namespaced\one'), new PHPClass('Namespaced\two')),
      'name',
      array('one', 'two')
    );

        $tests[] = array(
      array(),
      'not there',
      array()
    );

        return $tests;
    }

    public function testArrayFilterForKeys()
    {
        $headers = array(
      'Content-Type'=>'application/json',
      'Content-Length'=>723,
      'Accept'=>'*/*'
    );

        $filter = function ($headerName, $headerValue) {
            return $headerName != 'Content-Length';
        };

        self::assertEquals(
            array(
        'Content-Type'=>'application/json',
        'Accept'=>'*/*'
      ),
            A::filterKeys($headers, $filter),
            'A::filterKeys does not filter'
        );
    }

    public function testIndexByWithObjectProperty()
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

    public function testReindexWithClosure()
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

    public function testIndexByWithEmptyArray()
    {
        self::assertEquals(
            array(),
            A::indexBy(array(), 'index')
        );
    }
}
