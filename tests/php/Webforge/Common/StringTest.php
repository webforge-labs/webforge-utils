<?php

namespace Webforge\Common;

use Webforge\Common\StringUtil AS S;

class StringTest extends \PHPUnit_Framework_TestCase {
  
  public function testStartsWithChecksIfStringStartsWithPrefix() {
    $string = 'EnvironmentEncoding';
    $prefix = 'Environment';
    
    $this->assertTrue(S::startsWith($string, $prefix));
  }

  public function testStartsWithChecksIfStringStartNOTWithPrefix() {
    $string = 'EnvironmentEncoding';
    $prefix = 'other';
    
    $this->assertFalse(S::startsWith($string, $prefix));
  }
  

  public function testEnvironmentMbStringInternalEncodingIsUTF8() {
    $this->assertEquals('UTF-8', ini_get('mbstring.internal_encoding'), 'mbstring.internal_encoding is set to wrong value');
  }

  public function testIndent() {
    
    $string = 'aaa';
    $expect = '  aaa';
    $this->assertEquals($expect,S::indent($string,2));
    
    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc\n";
    
    $expect  = "  aaa\n";
    $expect .= "  bbb\n";
    $expect .= "  cccc\n";
    $this->assertEquals($expect,S::indent($string,2));

    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc";

    $expect  = "  aaa\n";
    $expect .= "  bbb\n";
    $expect .= "  cccc";
    $this->assertEquals($expect,S::indent($string,2));
    
    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc\n";
    $this->assertEquals($string,S::indent($string,0));
  }
  
  public function testPrefix() {
    $string = 'aaa';
    $expect = '[prefix]aaa';
    $this->assertEquals($expect,S::prefixLines($string,'[prefix]'));
    
    $string  = "aaa\n";
    $string .= "bbb\n";
    $string .= "cccc\n";
    
    $expect  = "[prefix]aaa\n";
    $expect .= "[prefix]bbb\n";
    $expect .= "[prefix]cccc\n";
    $this->assertEquals($expect,S::prefixLines($string,'[prefix]'));

    $string  = "\r\naaa\r\n";
    $string .= "bbb\r\n";
    $string .= "cccc\r\n";
    
    $expect  = "[prefix]\r\n[prefix]aaa\r\n";
    $expect .= "[prefix]bbb\r\n";
    $expect .= "[prefix]cccc\r\n";
    $this->assertEquals($expect,S::prefixLines($string,'[prefix]'));
  }
  
  public function testLineNumbersWithEOLOnEnd() {
    $string  = "line1\n";
    $string .= "line2\n";

    $expect  = "1 line1\n";
    $expect .= "2 line2\n";

    $this->assertEquals($expect, S::lineNumbers($string));
  }
  
  public function testLineNumbersWithoutEOLOnEnd() {
    $string = <<<'JAVASCRIPT'
jQuery.when( jQuery.psc.loaded() ).then( function(main) {
  main.getLoader().onReady(['Psc.UI.Main', 'Psc.UI.Tabs'], function () {
    var j = new Psc.UI.Main({
      'tabs': new Psc.UI.Tabs({
        'widget': $('#psc-ui-tabs')
      })
    });
  });
 });
});
JAVASCRIPT;

    $expect = <<<'JAVASCRIPT'
1  jQuery.when( jQuery.psc.loaded() ).then( function(main) {
2    main.getLoader().onReady(['Psc.UI.Main', 'Psc.UI.Tabs'], function () {
3      var j = new Psc.UI.Main({
4        'tabs': new Psc.UI.Tabs({
5          'widget': $('#psc-ui-tabs')
6        })
7      });
8    });
9   });
10 });
JAVASCRIPT;

    $this->assertEquals($expect, S::lineNumbers($string));
  }

  
  public function testExpandEnd() {
    $this->assertEquals('StringType', S::expand('String', 'Type', S::END));
    $this->assertEquals('StringType', S::expand('StringType', 'Type', S::END));
  }

  public function testExpandStart() {
    $this->assertEquals('@return', S::expand('return', '@', S::START));
    $this->assertEquals('@return', S::expand('@return', '@', S::START));
  }
  
  public function testCutAtLast() {
    $sentence = "this is a sentence";
    //           0123456789a1234567
    $ender = '...';
    
    // no cutting-test
    $this->assertEquals($sentence, S::cutAtLast($sentence, 18, ' ', $ender));
    
    // cutting at whitespace not inbetween "sentence"
    $this->assertEquals('this is a'.$ender, S::cutAtLast($sentence, 12, ' ', $ender));

    // cutting at whitespace not inbetween "sentence"
    $this->assertEquals('this is a'.$ender, S::cutAtLast($sentence, 17, ' ', $ender));

    // cutting at whitespace not inbetween "is"
    $this->assertEquals('this'.$ender, S::cutAtLast($sentence, 5, ' ', $ender));
    
    // non defined
    $this->assertEquals(''.$ender, S::cutAtLast($sentence, 17, 'b', $ender));
  }
  
  public function testCut() {
    $sentence = "this is a sentence";
    //           0123456789a1234567
    $ender = '...';
    
    // no cutting-test (edge case)
    $this->assertEquals($sentence, S::cut($sentence, 18, $ender));

    // cutting hard at 12
    $this->assertEquals('this is a se'.$ender, S::cut($sentence, 12, $ender));

    // cutting hard at 0 (rubbish test)
    $this->assertEquals(''.$ender, S::cut($sentence, 0, $ender));
  }
  
  public function testRandomAcceptance_ReturnsAStringFromLengthWithAZ09() {
    $length = 12;
    for ($i = 1; $i <= 10; $i++) {
      $this->assertRegExp('/^[a-z0-9]{'.$length.'}$/', S::random($length));
    }
  }
  
  public function testUCFirstUpcasesFirstLetter() {
    $this->assertEquals('UpperWord', S::ucfirst('upperWord'));
  }

  public function testUCFirstUpcasesFirstLetter_MultiByteSafe() {
    $this->assertEquals('Upper„Word“', S::ucfirst('upper„Word“')); // <- this would also be multibyte safe with php (interestingly)
    $this->assertEquals('ÖpperWort', S::ucfirst('öpperWort'));
  }

  public function testLCFirstLowCasesFirstLetter_MultiByteSafe() {
    $this->assertEquals('lower„Word“', S::lcfirst('Lower„Word“'));
    $this->assertEquals('äuer', S::lcfirst('Äuer'));
  }
  
  public function testEOLVisibleMarksEOLs() {
    $this->assertEquals(
      "some-n-\nstring-rn-\r\n",
      S::eolVisible("some\nstring\r\n")
    );
  }
  
  public function testFixEOLMakesEveryEOLsToUnixEOLs() {
    $textWithMixed  = "firstLine\r\n";
    $textWithMixed .= "seondLine\r";
    $textWithMixed .= "thirdLine\r\n";
    $textWithMixed .= "fourthLine\n";
    $textWithMixed .= "\n";
    
    $expectedText  = "firstLine\n";
    $expectedText .= "seondLine\n";
    $expectedText .= "thirdLine\n";
    $expectedText .= "fourthLine\n";
    $expectedText .= "\n";
    
    $this->assertEquals(
      $expectedText,
      S::fixEOL($textWithMixed)
    );
  }
  
  public function testDebugEqualsAcceptance() {
    $this->assertNotEmpty(S::debugEquals('value1', 'value2'));
  }
  
  public function testPadLeft_padsTheStringOnTheLeft() {
    $this->assertEquals('01', S::padLeft('01', 2, '0'));
    $this->assertEquals('01', S::padLeft('1', 2, '0'));
    $this->assertEquals('11', S::padLeft('11', 2, '0'));
  }
  
  public function testWrapWrapsWithAChar() {
    $this->assertEquals('"its wrapped"', S::wrap('its wrapped', '"'));
  }
  
  /**
   * @dataProvider provideSubstring
   * this function is really weird: maybe dont use it anymore
   */
  public function testSubstringIsStringCuttingFROMPosition_TOPosition_NotIncludingPosition($expectedString, $string, $from, $to) {
    $this->assertEquals(
      $expectedString,
      S::substring($string, $from, $to)
    );
  }
  
  public static function provideSubstring() {
    $tests = array();
    
    $string = '0123456789';
    
    $tests[] = array(
      '0123',
      $string, 0, 4
    );
    
    $tests[] = array(
      '',
      $string, 0, 0
    );

    $tests[] = array(
      $string,
      $string, 0, 10
    );

    // never use weirdo -1 or else
    $tests[] = array(
      '0',
      $string, 0, -1
    );
    
    return $tests;
  }
  
  /**
   * @dataProvider getSymmetricWrapTests
   */
  public function testSymmetricWrap($input, $symmetric, $expected) {
    $this->assertEquals($expected, S::swrap($input, $symmetric));
  }
  
  public static function getSymmetricWrapTests() {
    $tests = array();
    
    $tests[] = array(
      '2-TAF_0001',
      '(',
      '(2-TAF_0001)'
    );

    $tests[] = array(
      '2-TAF_0001',
      '[',
      '[2-TAF_0001]'
    );
    
    $tests[] = array(
      '2-TAF_0001',
      '[',
      '[2-TAF_0001]'
    );

    $tests[] = array(
      '2-TAF_0001',
      ']',
      ']2-TAF_0001]'
    );

    $tests[] = array(
      "what's up",
      '"',
      '"what\'s up"'
    );
    
    return $tests;
  }

  /**
   * @dataProvider provideMiniTemplate
   */
  public function testMiniTemplate($template, $vars, $expected) {
    $this->assertEquals(
      $expected,
      S::miniTemplate($template, $vars)
    );
  }
  
  public static function provideMiniTemplate() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test(
      "OIDs %usedOIDs% are used in this game",
      array('usedOIDs'=>'11601,11602,11603,11604,11605,11606,11617,11618'),
      "OIDs 11601,11602,11603,11604,11605,11606,11617,11618 are used in this game"
    );

    $test(
      '%some %thing%',
      array('thing'=>'other'),
      '%some other'
    );
  
    return $tests;
  }


  /**
   * @dataProvider provideCamelCaseToDash
   */
  public function testCamelCaseToDash($camelName, $dashName) {
    $this->assertEquals(
      $dashName,
      S::camelCaseToDash($camelName)
    );
  }

  /**
   * @dataProvider provideDashToCamelCase
   */
  public function testDashToCamelCase($camelName, $dashName) {
    $this->assertEquals(
      $camelName,
      S::dashToCamelCase($dashName)
    );
  }
  
  public static function provideCamelCaseToDash() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test('RegisterPackage', 'register-package');
    $test('RegisterOtherPackage', 'register-other-package');
    $test('API', 'api');

    return $tests;
  }

  public static function provideDashToCamelCase() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test('RegisterPackage', 'register-package');
    $test('RegisterOtherPackage', 'register-other-package');
    $test('Api', 'api'); // this is not bidirectional to the last testcase of the provider above

    return $tests;
  }

}
