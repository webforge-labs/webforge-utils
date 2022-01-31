<?php

namespace Webforge\Common;

class PregTest extends \PHPUnit\Framework\TestCase
{
    protected $matchers;

    protected function setUp(): void
    {
        $this->matchers = array(
      '/web-dl/i' => 'one',
      '/^mother/' => 'two',
      '/German/'  => 'three'
    );
    }

    public function testPregReplaceIsMultiByteSafe()
    {
        // internal test: preg_replace is not overloaded or something
        self::assertNotEquals(
            'Upper„Word“',
            preg_replace('/(„)(Nerd)(“)/', 'Upper„Nerd“', '\\1Word\\3'),
            'failed asserting that php function is NOT multibytesafe'
        );

        self::assertEquals(
            'Upper„Word“',
            Preg::replace('Upper„Nerd“', '/(„)(Nerd)(“)/', '\\1Word\\3')
        );
    }

    public function testPregMatchisMultiByteSafe()
    {
        // internal test: preg_replace is not overloaded or something
        $pattern = '/^\x{201E}Nerd“$/';
        $subject = '„Nerd“';

        self::assertEquals(
            1,
            Preg::match($subject, $pattern)
        );

        // this will fail, but i cant find an example where preg_match does not fail with error but still does not match
    //self::assertEquals(
    //  0,
    //  preg_match($pattern, $subject, $match),
    //  'failed asserting that php function preg_match is NOT multibytesafe'
    //);
    }

    public function testPregMatchHasGModifierWhichMatchesAll()
    {
        // internal test: preg_replace is not overloaded or something
        $pattern = '/([0-9]{1})/';
        $subject = '123';

        self::assertEquals(
            3,
            Preg::match($subject, $pattern.'g')
        );

        self::assertEquals(
            1,
            Preg::match($subject, $pattern)
        );
    }

    public function testPregMatchThrowsExceptionIfInternalErrorAccurs()
    {
        $this->expectException(\Webforge\Common\Exception::class);

        Preg::match(
            '{aaaaaaaaaaaaaaaaaa{aaaaaaaaaaa{aaaaaaaaaaaaaaaaaaaaaaaaaaa}',
            '/\{(([^{}]*|(?R))*)\}/'
        );
    }

    public function testMatchArrayMatchesOnElementFromRegexArray()
    {
        self::assertNotEquals(
            'two',
            Preg::matchArray(
          $this->matchers,
          'mother.web-dl'
      )
        );
    }
    public function testMatchArrayMatchesTHEFIRSTRegexFromArray()
    {
        self::assertEquals(
            'one',
            Preg::matchArray(
          $this->matchers,
          'How.I.Met.Your.Mother.S06E13.Schlechte.Nachrichten.German.Dubbed.WEB-DL.XViD'
      )
        );
    }

    public function testFullMatchArrayMatchesAllFromArray()
    {
        self::assertEquals(
            array('one','three'),
            Preg::matchFullArray(
          $this->matchers,
          'How.I.Met.Your.Mother.S06E13.Schlechte.Nachrichten.German.Dubbed.WEB-DL.XViD'
      )
        );
    }

    public function testMatchFullArrayCanReturnNoMatchDefaultValue()
    {
        self::assertEquals(
            'noMatchDefaultValue',
            Preg::matchFullArray(
          $this->matchers,
          'doesnotmatch',
          'noMatchDefaultValue'
      )
        );
    }

    public function testMatchArrayCanReturnNoMatchDefaultValue()
    {
        self::assertEquals(
            'noMatchDefaultValue',
            Preg::matchArray(
          $this->matchers,
          'doesnotmatch',
          'noMatchDefaultValue'
      )
        );
    }

    public function testMatchFullArrayCanAssertAMatch()
    {
        $this->expectException(\Webforge\Common\NoMatchException::class);

        Preg::matchFullArray(
            $this->matchers,
            'doesnotmatch'
        );
    }

    public function testMatchArrayThrowsExceptionWhenAnyRegexDoesNotMatch()
    {
        $this->expectException(\Webforge\Common\NoMatchException::class);

        Preg::matchArray($this->matchers, 'nix');
    }

    public function testMatchArrayRegressionSerienLoader()
    {
        $value = 'How.I.Met.Your.Mother.S06E13.Schlechte.Nachrichten.German.Dubbed.WEB-DL.XViD';

        self::assertEquals(
            'WEB-DL',
            Preg::matchArray(
          array(
          '/dvdrip/i' => 'DVDRip',
          '/WEB-DL/i' => 'WEB-DL'
        ),
          $value
      )
        );
    }

    public function testSetModifierCanRemoveUModifierFromPattern()
    {
        self::assertEquals(
            '/something/i',
            Preg::setModifier('/something/ui', 'u', false)
        );
    }

    public function testSetModifierCanAddUModifierToPattern()
    {
        self::assertEquals(
            '/something/iu',
            Preg::setModifier('/something/i', 'u', true)
        );
    }

    /**
     * @dataProvider provideTestqmatch
     */
    public function testqmatch($string, $rx, $set, $expectedReturn)
    {
        self::assertEquals($expectedReturn, Preg::qmatch($string, $rx, $set));
    }

    public static function provideTestqmatch()
    {
        $tests = array();
        $equals = function ($expectedReturn, $string, $rx, $set) use (&$tests) {
            $tests[] = array($string, $rx, $set, $expectedReturn);
        };

        $s1 = 'How.I.Met.Your.Mother.S06E13.Schlechte.Nachrichten.German.Dubbed.WEB-DL.XViD';
        $episodeRX = '/How.I.Met.Your.Mother.S([0-9]{2})E([0-9]{2,})/'; // jaja ich weiß, . nicht escaped
        //var_dump(Preg::match($s1, $episodeRX,$match),$match); // deppen ausscließen

        // set 0
        $equals(
            $s1,
            $s1,
            '/^.*$/',
            0
        );

        // set 1
        $equals(
            '06',
            $s1,
            $episodeRX,
            1
        );

        // set 2
        $equals(
            '13',
            $s1,
            $episodeRX,
            2
        );

        // set größer match ergibt notice, das können wir schlecht testen (ist aber gewollt)
//    $equals('13',
//            $s1, $episodeRX, 3);

        // set-array
        $equals(
            array('06','13'),
            $s1,
            $episodeRX,
            array(1,2)
        );

        // set-array
        $equals(
            array('13'),
            $s1,
            $episodeRX,
            array(2)
        );

        // set-array empty
        $equals(
            array(),
            $s1,
            $episodeRX,
            array()
        );

        // no match
        $equals(
            null,
            $s1,
            '/(wolf|katze)/i',
            1
        );
        $equals(
            null,
            $s1,
            '/(wolf|katze)/i',
            array(0,1)
        );

        // set-array oversized (schmeisst keine notice)
        $equals(
            array('06','13'),
            $s1,
            $episodeRX,
            array(1,2,3)
        );

        $equals(
            array('06','13'),
            $s1,
            $episodeRX,
            array(1,2,3,4,5,6,7,13)
        );

        return $tests;
    }
}
