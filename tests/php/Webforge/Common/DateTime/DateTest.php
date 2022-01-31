<?php

namespace Webforge\Common\DateTime;

class DateTest extends \Webforge\Common\TestCase
{
    public function testConstruct()
    {
        // createm from datetime
        $dateTime = new DateTime('21.11.84 21:12');
        $date = Date::createFromDateTime($dateTime);
        self::assertInstanceOf('Webforge\Common\DateTime\Date', $date);
        self::assertEquals('21.11.1984 00:00:00', $date->format('d.m.Y H:i:s'));
    }

    public function testInvalidDate()
    {
        $this->expectException(\Webforge\Common\DateTime\ParsingException::class);

        $date = new Date('29.02.2011'); // 2012 ist das schaltjahr

        print $date->format('d.m.Y');
    }
}
