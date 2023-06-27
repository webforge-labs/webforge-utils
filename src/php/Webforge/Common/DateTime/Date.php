<?php

namespace Webforge\Common\DateTime;

/**
 * A Date is a DateTime with time set to 00:00:00
 *
 */
class Date extends DateTime
{
    public static function createFromDateTime(DateTime $dateTime)
    {
        $date = new static($dateTime->format('U'));
        //$date->setTime(0,0,0); // macht constructor schon

        return $date;
    }

    public function __construct($time = null, \DateTimeZone $object = null)
    {
        parent::__construct($time, $object);
        $this->setTime(0, 0, 0);
    }

    public function getVarInfo()
    {
        return '[Date: '.$this->format('d.m.Y').' '.$this->getTimezone()->getName().']';
    }
}
