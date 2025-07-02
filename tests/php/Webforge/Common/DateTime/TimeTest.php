<?php

namespace Webforge\Common\DateTime;

class TimeTest extends \Webforge\Common\TestCase
{
    /**
     * @dataProvider provideFormatSpan
     */
    public function testFormatSpan($seconds, $assertion): void
    {
        self::assertEquals($assertion, Time::formatSpan($seconds, '%H:%I:%S'));
    }

    public static function provideFormatSpan()
    {
        return [
            [60, '00:01:00'],
            [61, '00:01:01'],
            [71, '00:01:11'],
            [60 * 60 * 2 + 23 * 60, '02:23:00'],
            [60 * 60 * 2 + 23 * 60 + 10, '02:23:10'],
            [60 * 60 * 2 + 23 * 60 + 1, '02:23:01'],
            [23 * 60 * 60 + 59 * 60 + 59, '23:59:59'],
            [24 * 60 * 60 + 59 * 60 + 59, '00:59:59'],
        ];
    }
}
