<?php declare(strict_types=1);

namespace Webforge\Common\DateTime;

class DateIntervalTest extends \Webforge\Common\TestCase
{
    protected $interval;

    protected function setUp(): void
    {
        $this->interval = new DateInterval('P1Y');

        parent::setUp();
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(\Webforge\Common\DateTime\DateInterval::class, $this->interval);
    }

    /**
     * @dataProvider provideSimpleSpec
     */
    public function testConstructConvertSimpleSpec(\DateInterval $expectedInterval, $testSpec): void
    {
        $format = '%R %Y %M %D %H %I %S';
        $actualInterval = new DateInterval($testSpec);
        self::assertEquals($expectedInterval->format($format), $actualInterval->format($format));
    }

    /**
     * @return list<array{DateInterval, mixed}>
     */
    public static function provideSimpleSpec(): array
    {
        $tests = [];
        $test = function ($intervalSpec, $testSpec) use (&$tests): void {
            $tests[] = [new \DateInterval($intervalSpec), $testSpec];
        };

        $test('P1Y', '1 YEAR');
        $test('P5Y', '5 YEARS');

        $test('P2D', '2 DAY');
        $test('P1D', '1 DAYS');

        // @TODO hours/minutes/seconds usw

        return $tests;
    }

    public function testAddTo(): void
    {
        $start = DateTime::create('21.11.1984 13:00');
        $iv = DateInterval::create('1 DAY');

        self::assertEquals('22.11.1984 13:00', $iv->addTo($start)->format('d.m.Y H:i'));
        self::assertEquals('21.11.1984 13:00', $start->format('d.m.Y H:i'));
    }
}
