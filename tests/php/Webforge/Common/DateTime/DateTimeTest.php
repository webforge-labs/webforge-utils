<?php declare(strict_types=1);

namespace Webforge\Common\DateTime;

/**
 * put test data into testdata repository
 */
class DateTimeTest extends \Webforge\Common\TestCase
{
  /**
   * @dataProvider i18nFormats
   */
    public function testI18nFormat($expectedFormat, $date, $formatString, $lang = 'en'): void
    {
        self::assertEquals($expectedFormat, $date->i18n_format($formatString, $lang));
    }

    public static function i18nFormats(): array
    {
        $tests = [];

        // in php this is the three chars abbrev!
        // there is no abbrev for 2 digits
        $tests[] = ['Mo', new DateTime('21.03.2011'), 'D'];
        $tests[] = ['Tu', new DateTime('22.03.2011'), 'D'];
        $tests[] = ['We', new DateTime('23.03.2011'), 'D'];
        $tests[] = ['Th', new DateTime('24.03.2011'), 'D'];
        $tests[] = ['Fr', new DateTime('25.03.2011'), 'D'];
        $tests[] = ['Sa', new DateTime('26.03.2011'), 'D'];
        $tests[] = ['Su', new DateTime('27.03.2011'), 'D'];

        $tests[] = ['Monday',    new DateTime('21.03.2011'), 'l'];
        $tests[] = ['Tuesday',   new DateTime('22.03.2011'), 'l'];
        $tests[] = ['Wednesday', new DateTime('23.03.2011'), 'l'];
        $tests[] = ['Thursday',  new DateTime('24.03.2011'), 'l'];
        $tests[] = ['Friday',    new DateTime('25.03.2011'), 'l'];
        $tests[] = ['Saturday',  new DateTime('26.03.2011'), 'l'];
        $tests[] = ['Sunday',    new DateTime('27.03.2011'), 'l'];

        $tests[] = ['Mo', new DateTime('21.03.2011'), 'D', 'de'];
        $tests[] = ['Di', new DateTime('22.03.2011'), 'D', 'de'];
        $tests[] = ['Mi', new DateTime('23.03.2011'), 'D', 'de'];
        $tests[] = ['Do', new DateTime('24.03.2011'), 'D', 'de'];
        $tests[] = ['Fr', new DateTime('25.03.2011'), 'D', 'de'];
        $tests[] = ['Sa', new DateTime('26.03.2011'), 'D', 'de'];
        $tests[] = ['So', new DateTime('27.03.2011'), 'D', 'de'];

        $tests[] = ['Montag',    new DateTime('21.03.2011'), 'l', 'de'];
        $tests[] = ['Dienstag',  new DateTime('22.03.2011'), 'l', 'de'];
        $tests[] = ['Mittwoch',  new DateTime('23.03.2011'), 'l', 'de'];
        $tests[] = ['Donnerstag',new DateTime('24.03.2011'), 'l', 'de'];
        $tests[] = ['Freitag',   new DateTime('25.03.2011'), 'l', 'de'];
        $tests[] = ['Samstag',   new DateTime('26.03.2011'), 'l', 'de'];
        $tests[] = ['Sonntag',   new DateTime('27.03.2011'), 'l', 'de'];

        return $tests;
    }

    public function testYesterday(): void
    {
        $now = time();
        $yesterday = $now - 24 * 60 * 60;
        $beforeYesterday = $now - 48 * 60 * 60;

        $now = DateTime::factory($now);
        $yesterday = DateTime::factory($yesterday);
        $beforeYesterday = DateTime::factory($beforeYesterday);

        self::assertTrue($yesterday->isYesterday());
        self::assertTrue($yesterday->isYesterday($now));

        self::assertFalse($beforeYesterday->isYesterday());
        self::assertFalse($beforeYesterday->isYesterday($now));

        $now->add(DateInterval::createFromDateString('1 DAY'));
        self::assertFalse($yesterday->isYesterday($now));
    }

    public function testToday(): void
    {
        $now = DateTime::now();
        self::assertTrue($now->isToday());
    }

    public function testisWeekDay(): void
    {
        $now = DateTime::parse('d.m.Y H:i', '5.1.2012 12:00');
        $we = DateTime::parse('d.m.Y H:i', '4.1.2012 12:00');
        $mo = DateTime::parse('d.m.Y H:i', '2.1.2012 12:00');
        $su = DateTime::parse('d.m.Y H:i', '8.1.2012 12:00');

        self::assertTrue($we->isWeekDay($now));
        self::assertTrue($mo->isWeekDay($now));
        self::assertTrue($su->isWeekDay($now));

        $now = DateTime::parse('d.m.Y', '10.1.2012');
        self::assertFalse($we->isWeekDay($now));
        self::assertFalse($mo->isWeekDay($now));
        self::assertFalse($su->isWeekDay($now));
    }

    /**
     * @dataProvider provideFormatSpan
     */
    public function testGetWeekday(int $day, \Webforge\Common\DateTime\DateTime $date, string $assertion): void
    {
        self::assertEquals($assertion, $date->getWeekday($day)->format('d.m.Y'));
    }

    public function testParseFromRFC1123(): void
    {
        self::assertInstanceof(\Webforge\Common\DateTime\DateTime::class, DateTime::parse(DateTime::RFC1123, 'Thu, 10 Nov 2011 07:28:18 GMT'));
    }

    public function testCoolSettersAndGetters(): void
    {
        $day = 12;
        $month = 1;
        $year = 2012;

        $date = new DateTime('12.1.2012');
        self::assertSame($day, $date->getDay());
        self::assertSame($month, $date->getMonth());
        self::assertSame($year, $date->getYear());

        $date->setYear(1940);
        self::assertSame($day, $date->getDay());
        self::assertSame($month, $date->getMonth());
        self::assertSame(1940, $date->getYear());
    }

    public function testSetYearBecomesInt(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $date = DateTime::now();
        $date->setYear('2011');
    }

    public function testCopyReturnsAClonedAndRelativelyModifiedDate(): void
    {
        $date = new DateTime('07.08.2014');
        $copy = $date->copy();
        self::assertEquals('07.08.2014', $copy->format('d.m.Y'));

        $otherDate = $date->copy('+1 day');
        self::assertEquals('08.08.2014', $otherDate->format('d.m.Y'));

        self::assertEquals('07.08.2014', $date->format('d.m.Y'));
    }

    public static function provideFormatSpan(): array
    {
        return [

      // 14.03. ist der Montag der Woche
      [DateTime::MON, new DateTime('14.03.2011'), '14.03.2011'],
      [DateTime::TUE, new DateTime('14.03.2011'), '15.03.2011'],
      [DateTime::WED, new DateTime('14.03.2011'), '16.03.2011'],
      [DateTime::THU, new DateTime('14.03.2011'), '17.03.2011'],
      [DateTime::FRI, new DateTime('14.03.2011'), '18.03.2011'],
      [DateTime::SAT, new DateTime('14.03.2011'), '19.03.2011'],
      [DateTime::SUN, new DateTime('14.03.2011'), '20.03.2011'],

      // 15.03. ist der Dienstag der Woche
      [DateTime::MON, new DateTime('15.03.2011'), '14.03.2011'],
      [DateTime::TUE, new DateTime('15.03.2011'), '15.03.2011'],
      [DateTime::WED, new DateTime('15.03.2011'), '16.03.2011'],
      [DateTime::THU, new DateTime('15.03.2011'), '17.03.2011'],
      [DateTime::FRI, new DateTime('15.03.2011'), '18.03.2011'],
      [DateTime::SAT, new DateTime('15.03.2011'), '19.03.2011'],
      [DateTime::SUN, new DateTime('15.03.2011'), '20.03.2011'],

      // 16.03. ist der Mittwoch der Woche
      [DateTime::MON, new DateTime('16.03.2011'), '14.03.2011'],
      [DateTime::TUE, new DateTime('16.03.2011'), '15.03.2011'],
      [DateTime::WED, new DateTime('16.03.2011'), '16.03.2011'],
      [DateTime::THU, new DateTime('16.03.2011'), '17.03.2011'],
      [DateTime::FRI, new DateTime('16.03.2011'), '18.03.2011'],
      [DateTime::SAT, new DateTime('16.03.2011'), '19.03.2011'],
      [DateTime::SUN, new DateTime('16.03.2011'), '20.03.2011'],

      // 17.03. ist der Donnerstag der Woche
      [DateTime::MON, new DateTime('17.03.2011'), '14.03.2011'],
      [DateTime::TUE, new DateTime('17.03.2011'), '15.03.2011'],
      [DateTime::WED, new DateTime('17.03.2011'), '16.03.2011'],
      [DateTime::THU, new DateTime('17.03.2011'), '17.03.2011'],
      [DateTime::FRI, new DateTime('17.03.2011'), '18.03.2011'],
      [DateTime::SAT, new DateTime('17.03.2011'), '19.03.2011'],
      [DateTime::SUN, new DateTime('17.03.2011'), '20.03.2011'],

      // 18.03. ist der Freitag der Woche
      [DateTime::MON, new DateTime('18.03.2011'), '14.03.2011'],
      [DateTime::TUE, new DateTime('18.03.2011'), '15.03.2011'],
      [DateTime::WED, new DateTime('18.03.2011'), '16.03.2011'],
      [DateTime::THU, new DateTime('18.03.2011'), '17.03.2011'],
      [DateTime::FRI, new DateTime('18.03.2011'), '18.03.2011'],
      [DateTime::SAT, new DateTime('18.03.2011'), '19.03.2011'],
      [DateTime::SUN, new DateTime('18.03.2011'), '20.03.2011'],

      // 19.03. ist der Samstag der Woche
      [DateTime::MON, new DateTime('19.03.2011'), '14.03.2011'],
      [DateTime::TUE, new DateTime('19.03.2011'), '15.03.2011'],
      [DateTime::WED, new DateTime('19.03.2011'), '16.03.2011'],
      [DateTime::THU, new DateTime('19.03.2011'), '17.03.2011'],
      [DateTime::FRI, new DateTime('19.03.2011'), '18.03.2011'],
      [DateTime::SAT, new DateTime('19.03.2011'), '19.03.2011'],
      [DateTime::SUN, new DateTime('19.03.2011'), '20.03.2011'],

      // 20.03. ist der Sonntag der Woche
      [DateTime::MON, new DateTime('20.03.2011'), '14.03.2011'],
      [DateTime::TUE, new DateTime('20.03.2011'), '15.03.2011'],
      [DateTime::WED, new DateTime('20.03.2011'), '16.03.2011'],
      [DateTime::THU, new DateTime('20.03.2011'), '17.03.2011'],
      [DateTime::FRI, new DateTime('20.03.2011'), '18.03.2011'],
      [DateTime::SAT, new DateTime('20.03.2011'), '19.03.2011'],
      [DateTime::SUN, new DateTime('20.03.2011'), '20.03.2011'],

      // 17.03. ist der Montag der folgenden Woche
      [DateTime::MON, new DateTime('21.03.2011'), '21.03.2011'],
      [DateTime::TUE, new DateTime('21.03.2011'), '22.03.2011'],
      [DateTime::WED, new DateTime('21.03.2011'), '23.03.2011'],
      [DateTime::THU, new DateTime('21.03.2011'), '24.03.2011'],
      [DateTime::FRI, new DateTime('21.03.2011'), '25.03.2011'],
      [DateTime::SAT, new DateTime('21.03.2011'), '26.03.2011'],
      [DateTime::SUN, new DateTime('21.03.2011'), '27.03.2011'],
    ];
    }

    /**
     * @dataProvider provideBefore
     */
    public function testBefore($expected, string $subjectDate, string $objectDate): void
    {
        $subject = new DateTime($subjectDate);
        $object = new DateTime($objectDate);

        self::assertEquals($expected, $subject->isBefore($object), 'failed asserting that: ' . $subjectDate . '->isBefore(' . $objectDate . ')');
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideBefore(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test(true, '20.03.2014', '21.03.2014');
        $test(false, '21.03.2014', '21.03.2014');
        $test(false, '22.03.2014', '21.03.2014');

        return $tests;
    }

    /**
     * @dataProvider provideAfter
     */
    public function testAfter($expected, string $subjectDate, string $objectDate): void
    {
        $subject = new DateTime($subjectDate);
        $object = new DateTime($objectDate);

        self::assertEquals($expected, $subject->isAfter($object), 'failed asserting that: ' . $subjectDate . '->isAfter(' . $objectDate . ')');
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideAfter(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test(false, '20.03.2014', '21.03.2014');
        $test(false, '21.03.2014', '21.03.2014');
        $test(true, '22.03.2014', '21.03.2014');

        return $tests;
    }

    /**
     * @dataProvider provideEqual
     */
    public function testEqual($expected, string $subjectDate, string $objectDate): void
    {
        $subject = new DateTime($subjectDate);
        $object = new DateTime($objectDate);

        self::assertEquals($expected, $subject->isEqual($object), 'failed asserting that: ' . $subjectDate . '->isEqual(' . $objectDate . ')');
    }

    /**
     * @return list<list<mixed>>
     */
    public static function provideEqual(): array
    {
        $tests = [];

        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $test(false, '20.03.2014', '21.03.2014');
        $test(true, '21.03.2014', '21.03.2014');
        $test(false, '22.03.2014', '21.03.2014');

        return $tests;
    }
}
