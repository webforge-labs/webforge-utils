<?php declare(strict_types=1);

namespace Webforge\Common;

use Webforge\Common\StringUtil as S;

class StringTest extends \PHPUnit\Framework\TestCase
{
    public function testStartsWithChecksIfStringStartsWithPrefix(): void
    {
        $string = 'EnvironmentEncoding';
        $prefix = 'Environment';

        self::assertTrue(S::startsWith($string, $prefix));
    }

    public function testStartsWithChecksIfStringStartNOTWithPrefix(): void
    {
        $string = 'EnvironmentEncoding';
        $prefix = 'other';

        self::assertFalse(S::startsWith($string, $prefix));
    }

    public function testIndent(): void
    {
        $string = 'aaa';
        $expect = '  aaa';
        self::assertEquals($expect, S::indent($string, 2));

        $string = "aaa\n";
        $string .= "bbb\n";
        $string .= "cccc\n";

        $expect = "  aaa\n";
        $expect .= "  bbb\n";
        $expect .= "  cccc\n";
        self::assertEquals($expect, S::indent($string, 2));

        $string = "aaa\n";
        $string .= "bbb\n";
        $string .= "cccc";

        $expect = "  aaa\n";
        $expect .= "  bbb\n";
        $expect .= "  cccc";
        self::assertEquals($expect, S::indent($string, 2));

        $string = "aaa\n";
        $string .= "bbb\n";
        $string .= "cccc\n";
        self::assertEquals($string, S::indent($string, 0));
    }

    public function testPrefix(): void
    {
        $string = 'aaa';
        $expect = '[prefix]aaa';
        self::assertEquals($expect, S::prefixLines($string, '[prefix]'));

        $string = "aaa\n";
        $string .= "bbb\n";
        $string .= "cccc\n";

        $expect = "[prefix]aaa\n";
        $expect .= "[prefix]bbb\n";
        $expect .= "[prefix]cccc\n";
        self::assertEquals($expect, S::prefixLines($string, '[prefix]'));

        $string = "\r\naaa\r\n";
        $string .= "bbb\r\n";
        $string .= "cccc\r\n";

        $expect = "[prefix]\r\n[prefix]aaa\r\n";
        $expect .= "[prefix]bbb\r\n";
        $expect .= "[prefix]cccc\r\n";
        self::assertEquals($expect, S::prefixLines($string, '[prefix]'));
    }

    public function testLineNumbersWithEOLOnEnd(): void
    {
        $string = "line1\n";
        $string .= "line2\n";

        $expect = "1 line1\n";
        $expect .= "2 line2\n";

        self::assertEquals($expect, S::lineNumbers($string));
    }

    public function testLineNumbersWithoutEOLOnEnd(): void
    {
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

        self::assertEquals($expect, S::lineNumbers($string));
    }

    public function testExpandEnd(): void
    {
        self::assertEquals('StringType', S::expand('String', 'Type', S::END));
        self::assertEquals('StringType', S::expand('StringType', 'Type', S::END));
    }

    public function testExpandStart(): void
    {
        self::assertEquals('@return', S::expand('return', '@', S::START));
        self::assertEquals('@return', S::expand('@return', '@', S::START));
    }

    public function testCutAtLast(): void
    {
        $sentence = "this is a sentence";
        //           0123456789a1234567
        $ender = '...';

        // no cutting-test
        self::assertEquals($sentence, S::cutAtLast($sentence, 18, ' ', $ender));

        // cutting at whitespace not inbetween "sentence"
        self::assertEquals('this is a' . $ender, S::cutAtLast($sentence, 12, ' ', $ender));

        // cutting at whitespace not inbetween "sentence"
        self::assertEquals('this is a' . $ender, S::cutAtLast($sentence, 17, ' ', $ender));

        // cutting at whitespace not inbetween "is"
        self::assertEquals('this' . $ender, S::cutAtLast($sentence, 5, ' ', $ender));

        // non defined
        self::assertEquals('' . $ender, S::cutAtLast($sentence, 17, 'b', $ender));
    }

    public function testCut(): void
    {
        $sentence = "this is a sentence";
        //           0123456789a1234567
        $ender = '...';

        // no cutting-test (edge case)
        self::assertEquals($sentence, S::cut($sentence, 18, $ender));

        // cutting hard at 12
        self::assertEquals('this is a se' . $ender, S::cut($sentence, 12, $ender));

        // cutting hard at 0 (rubbish test)
        self::assertEquals('' . $ender, S::cut($sentence, 0, $ender));
    }

    public function testRandomAcceptance_ReturnsAStringFromLengthWithAZ09(): void
    {
        $length = 12;
        for ($i = 1; $i <= 10; $i++) {
            self::assertMatchesRegularExpression('/^[a-z0-9]{' . $length . '}$/', S::random($length));
        }
    }

    public function testUCFirstUpcasesFirstLetter(): void
    {
        self::assertEquals('UpperWord', S::ucfirst('upperWord'));
    }

    public function testUCFirstUpcasesFirstLetter_MultiByteSafe(): void
    {
        self::assertEquals('Upper„Word“', S::ucfirst('upper„Word“')); // <- this would also be multibyte safe with php (interestingly)
        self::assertEquals('ÖpperWort', S::ucfirst('öpperWort'));
    }

    public function testLCFirstLowCasesFirstLetter_MultiByteSafe(): void
    {
        self::assertEquals('lower„Word“', S::lcfirst('Lower„Word“'));
        self::assertEquals('äuer', S::lcfirst('Äuer'));
    }

    public function testEOLVisibleMarksEOLs(): void
    {
        self::assertEquals(
            "some-n-\nstring-rn-\r\n",
            S::eolVisible("some\nstring\r\n")
        );
    }

    public function testFixEOLMakesEveryEOLsToUnixEOLs(): void
    {
        $textWithMixed = "firstLine\r\n";
        $textWithMixed .= "seondLine\r";
        $textWithMixed .= "thirdLine\r\n";
        $textWithMixed .= "fourthLine\n";
        $textWithMixed .= "\n";

        $expectedText = "firstLine\n";
        $expectedText .= "seondLine\n";
        $expectedText .= "thirdLine\n";
        $expectedText .= "fourthLine\n";
        $expectedText .= "\n";

        self::assertEquals(
            $expectedText,
            S::fixEOL($textWithMixed)
        );
    }

    public function testDebugEqualsAcceptance(): void
    {
        self::assertNotEmpty(S::debugEquals('value1', 'value2'));
    }

    public function testPadLeft_padsTheStringOnTheLeft(): void
    {
        self::assertEquals('01', S::padLeft('01', 2, '0'));
        self::assertEquals('01', S::padLeft('1', 2, '0'));
        self::assertEquals('11', S::padLeft('11', 2, '0'));
    }

    public function testWrapWrapsWithAChar(): void
    {
        self::assertEquals('"its wrapped"', S::wrap('its wrapped', '"'));
    }

    /**
     * @dataProvider provideSubstring
     * this function is really weird: maybe dont use it anymore
     */
    public function testSubstringIsStringCuttingFROMPosition_TOPosition_NotIncludingPosition($expectedString, $string, $from, $to): void
    {
        self::assertEquals(
            $expectedString,
            S::substring($string, $from, $to)
        );
    }

    public static function provideSubstring(): array
    {
        $tests = [];

        $string = '0123456789';

        $tests[] = [
      '0123',
      $string, 0, 4
    ];

        $tests[] = [
      '',
      $string, 0, 0
    ];

        $tests[] = [
      $string,
      $string, 0, 10
    ];

        // never use weirdo -1 or else
        $tests[] = [
      '0',
      $string, 0, -1
    ];

        return $tests;
    }

    /**
     * @dataProvider getSymmetricWrapTests
     */
    public function testSymmetricWrap(string $input, $symmetric, $expected): void
    {
        self::assertEquals($expected, S::swrap($input, $symmetric));
    }

    public static function getSymmetricWrapTests(): array
    {
        $tests = [];

        $tests[] = [
      '2-TAF_0001',
      '(',
      '(2-TAF_0001)'
    ];

        $tests[] = [
      '2-TAF_0001',
      '[',
      '[2-TAF_0001]'
    ];

        $tests[] = [
      '2-TAF_0001',
      '[',
      '[2-TAF_0001]'
    ];

        $tests[] = [
      '2-TAF_0001',
      ']',
      ']2-TAF_0001]'
    ];

        $tests[] = [
      "what's up",
      '"',
      '"what\'s up"'
    ];

        return $tests;
    }

    /**
     * @dataProvider provideMiniTemplate
     */
    public function testMiniTemplate($template, array $vars, $expected): void
    {
        self::assertEquals(
            $expected,
            S::miniTemplate($template, $vars)
        );
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideMiniTemplate(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test(
            "OIDs %usedOIDs% are used in this game",
            ['usedOIDs' => '11601,11602,11603,11604,11605,11606,11617,11618'],
            "OIDs 11601,11602,11603,11604,11605,11606,11617,11618 are used in this game"
        );

        $test(
            '%some %thing%',
            ['thing' => 'other'],
            '%some other'
        );

        return $tests;
    }

    /**
     * @dataProvider provideCamelCaseToDash
     */
    public function testCamelCaseToDash($camelName, $dashName): void
    {
        self::assertEquals(
            $dashName,
            S::camelCaseToDash($camelName)
        );
    }

    /**
     * @dataProvider provideDashToCamelCase
     */
    public function testDashToCamelCase($camelName, $dashName): void
    {
        self::assertEquals(
            $camelName,
            S::dashToCamelCase($dashName)
        );
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideCamelCaseToDash(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test('RegisterPackage', 'register-package');
        $test('RegisterOtherPackage', 'register-other-package');
        $test('API', 'api');

        return $tests;
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideDashToCamelCase(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test('RegisterPackage', 'register-package');
        $test('RegisterOtherPackage', 'register-other-package');
        $test('Api', 'api'); // this is not bidirectional to the last testcase of the provider above

        return $tests;
    }
}
