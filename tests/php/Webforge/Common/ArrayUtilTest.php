<?php

namespace Psc;

use Webforge\Common\ArrayUtil AS A;
use Webforge\Common\PHPClass;

class ArrayUtilTest extends \PHPUnit_Framework_TestCase {
  
  public function testPeek() {
    $array = array('s1','s2');
    
    $this->assertEquals('s2', A::peek($array));
    $this->assertEquals(array('s1','s2'), $array); // Not popped

    array_pop($array);
    $this->assertEquals('s1', A::peek($array));
    $this->assertEquals(array('s1'), $array); // Not popped (but popped before)
  }
  
  public function testFirst() {
    $array = array('f1','f2');
    $this->assertEquals('f1', A::first($array));
    $this->assertEquals(array('f1','f2'), $array);
  }

  public function testPush() {
    $array = array('f1','f2');
    $this->assertEquals(array('f1','f2','f3'), A::push($array, 'f3'));
    $this->assertEquals(array('f1','f2'), $array);
  }
  
  public function testInsert() {
    $array = array('f1','f3');
    
    A::insert($array, 'f2', 1);
    $this->assertEquals(array('f1','f2','f3'), $array);
    
    A::insert($array, 'f0', 0);
    $this->assertEquals(array('f0','f1','f2','f3'), $array);

    A::insert($array, 'f4', 4);
    $this->assertEquals(array('f0','f1','f2','f3','f4'), $array);

    A::insert($array, 'fbL', -1); // before last position
    $this->assertEquals(array('f0','f1','f2','f3','fbL','f4'), $array);

    A::insert($array, 'fL', A::END); // at the end
    $this->assertEquals(array('f0','f1','f2','f3','fbL','f4','fL'), $array);

    A::insert($array, 'f-2', -2); // before position -2
    $this->assertEquals(array('f0','f1','f2','f3','fbL','f-2','f4','fL'), $array);
  }

  public function testInsert0PrependsToArray() {
    $array = array('two');
    
    A::insert($array, 'one', 0);
    $this->assertEquals(array('one','two'), $array);
  }
  
  public function testIRemoveWithMatch_RemovesTheElement() {
    $array = array('0','1','2','3');
    A::remove($array, '1');

    $this->assertEquals(array('0', '2', '3'), $array);
  }

  public function testRemoveWithNoMatch_RemovesTheElement_searchStrictEnabledPerDefault() {
    $array = array('0','1','2','3');
    A::remove($array, 1); // searchstrict per default

    $this->assertEquals(array('0', '1', '2', '3'), $array);
  }

  public function testRemoveWithMatch_RemovesTheElement_searchStrictCanBeDisabled() {
    $array = array('0','1','2','3');
    A::remove($array, 1, $searchStrict = FALSE); // searchstrict per default

    $this->assertEquals(array('0', '2', '3'), $array);
  }

  public function testRemovesOnlyFirstMatchFromArray() {
    $array = array('0','1','2','3', '1');
    A::remove($array, '1');

    $this->assertEquals(array('0', '2', '3', '1'), $array);
  }

  public function testInsertArrayPositiveIndexInsertsBeforeIndex() {
    $array = array(0,1,4,5);
    
    A::insertArray($array, array(2,3), 2);
    $this->assertEquals(array(0,1,2,3,4,5), $array);
  }

  public function testInsertArrayNegativeIndexInsertsBeforeIndexCountedFromEndOfarray() {
    $array = array(0,1,4,5);
    
    A::insertArray($array, array(2,3), -2);
    $this->assertEquals(array(0,1,2,3,4,5), $array);
  }
  
  public function testInsertArrayWithENDConstantAppends() {
    $array = array(0,1,2,3);
    
    A::insertArray($array, array(4,5), A::END);
    $this->assertEquals(array(0,1,2,3,4,5), $array);
  }

  public function testInsertArrayWith0Prepends() {
    $array = array(2,3,4,5);
    
    A::insertArray($array, array(0,1), 0);
    $this->assertEquals(array(0,1,2,3,4,5), $array);
  }

  
  public function testSet() {
    $php = array();
    $my = array();
    
    $php[0] = NULL;
    A::set($my, 0, NULL);
    $this->assertEquals($php, $my);

    $var = 'v';
    $php[1] = $var;
    A::set($my, 1, $var);
    $this->assertEquals($php, $my);

    $php[5] = 'nix';
    A::set($my, 5, 'nix');
    $this->assertEquals($php, $my);

    $php[0] = '0';
    A::set($my, 0, '0');
    $this->assertEquals($php, $my);
  }

  /**
   * @expectedException OutOfBoundsException
   * @dataProvider provideInsert_OOB
   */
  public function testInsert_OOB($array, $offset) {
    A::insert($array, NULL, $offset);
  }
  
  
  public static function provideInsert_OOB() {
    $tests = array();
    
    $test = function ($array, $offset) use (&$tests) {
      $tests[] = array($array, $offset);
    };
    
    $test(array(0,1,2), 4);
    $test(array(1,2,3), -5);
    
    return $tests;
  }

  public function testJoin_normal() {
    $array = array('eins','zwei','drei');
    
    $this->assertEquals('->eins<- ->zwei<- ->drei<- ', A::join($array, '->%s<- '));
    $this->assertEquals('->eins<- ->zwei<- ->drei<- ', A::joinc($array, '->%s<- '));
  }

  public function testJoinWithInnerArrays() {
    $array = array('one', array('thatsbad'), 'three');

    $this->assertEquals('|one| |Array| |three| ', A::join($array, '|%s| '));
  }

  public function testImplode_normal() {
    $array = array(array(0=>'eins'),array(0=>'zwei'),array(0=>'drei'));
    
    $this->assertEquals('eins<- ->zwei<- ->drei', A::implode($array, '<- ->', function ($value) { return $value[0]; }));
  }

  public function testImplode_keys() {
    $array = array('eins','zwei','drei');
    
    $this->assertEquals('[0]EINS<- ->[1]ZWEI<- ->[2]DREI',
                        A::implode(
                          $array,
                          '<- ->',
                          function ($value, $key) {
                            return '['.$key.']'.mb_strtoupper($value);
                          }));
  }

  public function testJoin_withKeys() {
    $array = array('1'=>'eins','2'=>'zwei','3'=>'drei');
    
    $this->assertEquals('[1]eins [2]zwei [3]drei ', A::join($array, '[%2$s]%1$s '));
    $this->assertEquals('[1]eins [2]zwei [3]drei ', A::joinc($array, '[%2$s]%1$s '));
    $this->assertEquals('[1]EINS [2]ZWEI [3]DREI ', A::joinc($array, '[%2$s]%1$s ', function ($value) { return mb_strtoupper($value); } ));
  }
  
  public function testIndex() {
    $array = array();
    $array[1] = 7;
    $array['int'] = 8;
    
    $this->assertEquals(7, A::index($array, 1));
    $this->assertEquals(8, A::index($array, 'int'));
    $this->assertEquals('blubb', A::index(array('blubb'), 0));
  }
  
  public function testIndexRef() {
    $array = array();
    $array[1] = 7;
    $array['int'] = 8;

    $ref =& A::indexRef($array, 'int');
    $ref = 'val';
    $this->assertEquals('val', $array['int']);

    $ref =& A::indexRef($array, 1);
    $ref++;
    $this->assertEquals(8, $array[1]);
  }

  public function testShuffle_doesNotChangeOriginal() {
    $elems = array(0,1,2,3,4,5);
    
    $shuffled = A::shuffle($elems);
    
    if ($elems != $shuffled) {
      $this->assertEquals(array(0,1,2,3,4,5), $elems);
    } else {
      $this->markTestSkipped('Shuffle shuffled not the array');
    }
  }
  
  public function testRandomValue() {
    $elems = array('one','two','three');
    for ($i = 0; $i <= 10; $i++) {
      $this->assertNotEmpty($rEl = A::randomValue($elems)); // no value is empty in $elems
      $this->assertContains($rEl, $elems); // constrain that rEl is out of elems
    }
  }
  
  public function testKeys() {
    $vars = array(
      'mainTemplate'=>array(
        'name'=>'main',
        'display'=>TRUE,
        'subTemplate'=>array(
          'value1'=>'info1',
          'value2'=>'info2'
        )
      )
    );
    
    $this->assertEquals(array('mainTemplate'=>
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
  
  
  public function testMapKeys() {
    $array = array('key1'=>'value1',
                   'key2'=>'value2',
                   'key3'=>'value3');
    
    $this->assertEquals(array('Key1'=>'value1',
                              'Key2'=>'value2',
                              'Key3'=>'value3'
                             ),
                        A::mapKeys($array,
                                   function ($key) { return ucfirst($key); }
                                  )
                       );

    $this->assertEquals(array('Key1'=>'VALUE1',
                              'Key2'=>'VALUE2',
                              'Key3'=>'VALUE3'
                             ),
                        A::mapKeys($array,
                                   function ($key) { return ucfirst($key); },
                                   function ($value) { return strtoupper($value); }
                                  )
                       );
  }
  
  public function testGetType() {
    $this->assertEquals('assoc', A::getType(array('my'=>'values','are'=>'all','assoc'=>'keyed')));
    $this->assertEquals('numeric', A::getType(array('my','values','are', 'numeric')));
    $this->assertEquals('numeric', A::getType(array(1=>'my',7=>'values',4=>'are', 6=>'numeric',7=>'but shuffled')));
    $this->assertEquals('assoc', A::getType(array(1=>'my',7=>'values',4=>'are',    'mixed'=>'and',    6=>'numeric')));
  }
  
  public function testIsOnlyType() {
    $this->assertTrue(A::isOnlyType(array('string1','string2','string3'), 'string'));
    $this->assertTrue(A::isOnlyType(array(1,2,3), 'int'));
    $this->assertTrue(A::isOnlyType(array(array(),array(),array()), 'array'));
    $this->assertTrue(A::isOnlyType(array(array(),array(),array(3)), 'array')); // look closely
    $this->assertTrue(A::isOnlyType(array(array(),array(array()), array()), 'array'));
    
    $this->assertFalse(A::isOnlyType(array(1,'s',3), 'int'));
    $this->assertFalse(A::isOnlyType(array('b','s',3), 'string'));
    $this->assertFalse(A::isOnlyType(array(array(),NULL, array()), 'array'));
    
    $this->assertTrue(A::isOnlyType(array(), 'string'));
    $this->assertTrue(A::isOnlyType(array(), 'int'));
    $this->assertTrue(A::isOnlyType(array(), 'array'));
    $this->assertTrue(A::isOnlyType(array(), 'float'));
  }
  
  public function testFillUp() {
    $array = array(FALSE);
    
    $this->assertEquals(array(FALSE,TRUE,TRUE), A::fillUp($array, TRUE, 3));
    $this->assertEquals(array(FALSE), $array); // not modified

    $this->assertEquals(array(FALSE), A::fillUp($array, TRUE, 0));
    $this->assertEquals(array(FALSE), $array); // not modified

    $this->assertEquals(array(FALSE), A::fillUp($array, TRUE, -1));
    $this->assertEquals(array(FALSE), $array); // not modified
  }
  
  /**
   * @dataProvider getPluckTests
   */
  public function testPluck(Array $input, $prop, Array $expected) {
    $this->assertEquals(
      $expected,
      A::pluck($input, $prop)
    );
  }
  
  public static function getPluckTests() {
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
      array(NULL,'larry','curly')
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

  public function testArrayFilterForKeys() {
    $headers = array(
      'Content-Type'=>'application/json',
      'Content-Length'=>723,
      'Accept'=>'*/*'
    );

    $filter = function($headerName, $headerValue) {
      return $headerName != 'Content-Length';
    };

    $this->assertEquals(
      array(
        'Content-Type'=>'application/json',
        'Accept'=>'*/*'
      ),
      A::filterKeys($headers, $filter),
      'A::filterKeys does not filter'
    );
  }

  public function testIndexByWithObjectProperty() {
    $array = json_decode('[
  { "dir": "left", "code": 97 },
  { "dir": "right", "code": 100 }
]');

    $this->assertEquals(
      (array) json_decode('{ "left": { "dir": "left", "code": 97 }, "right": { "dir": "right", "code": 100 } }'),
      A::indexBy($array, 'dir')
    );
  }

  public function testReindexWithClosure() {
    $array = json_decode('[
  { "dir": "left", "code": 97 },
  { "dir": "right", "code": 100 }
]');

    $this->assertEquals(
      (array) json_decode('{ "left": { "dir": "left", "code": 97 }, "right": { "dir": "right", "code": 100 } }'),
      A::indexBy($array, function($item) {
        return $item->dir;
      })
    );
  }

  public function testIndexByWithEmptyArray() {
    $this->assertEquals(
      array(),
      A::indexBy(array(), 'index')
    );
  }
}
