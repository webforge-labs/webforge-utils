<?php

namespace Webforge\Common\DateTime;

use Webforge\Common\Preg;

class DateInterval extends \DateInterval
{
  /**
   * @var int microseconds of the interval
   */
    protected static $usData = array();

    public function __construct($interval_spec)
    {
        $interval_spec = $this->convertSimpleSpec($interval_spec);
        parent::__construct($interval_spec);

        self::$usData[spl_object_hash($this)] = 0;
    }

    /**
     * Nimmt eine intuitive IntervalSpezifikation (wie bei MySQL) und formt diese in PHP-spec um
     *
     * @return string
     */
    public function convertSimpleSpec($spec)
    {
        if (($year = Preg::qmatch($spec, '/^([0-9]+) YEARs?/i', 1, false)) !== false) {
            return sprintf('P%dY', $year);
        }

        if (($day = Preg::qmatch($spec, '/^([0-9]+) DAYs?/i', 1, false)) !== false) {
            return sprintf('P%dD', $day);
        }

        return $spec;
    }

    /**
     *
     * Zusätzlich zum normalen Format gehen %u (microseconds) und %n (milliseconds) analag %U und %N
     * @return string
     */
    public function format($string)
    {
        $ret = parent::format($string);

        if (mb_stripos($string, '%u')) {
            $ret = preg_replace('/(?<!%)%u/u', $this->getUS(), $ret);
            $ret = preg_replace('/(?<!%)%U/u', sprintf('%02d', $this->getUS()), $ret);
        }

        if (mb_stripos($string, '%n')) {
            $ret = preg_replace('/(?<!%)%n/u', sprintf('%d', $this->getUS()/1000), $ret);
            $ret = preg_replace('/(?<!%)%N/u', sprintf('%02d', $this->getUS()/1000), $ret);
        }

        return $ret;
    }

    /**
     * Returns a new instanceof DateTime with time added from this interval
     *
     * @return DateTime
     */
    public function addTo(DateTime $dateTime)
    {
        $dateTime = clone $dateTime;
        $dateTime->add($this);
        return $dateTime;
    }

    /**
     * Create a DateInterval from an PHP Dateinterval
     *
     * @param DateInterval $interval
     * @return DateInterval
     */
    public static function createFromDateInterval(\DateInterval $interval)
    {
        if ($interval instanceof DateInterval) {
            return $interval;
        }

        $ret = new DateInterval($interval->format('P%yY%mM%dDT%hH%iM%sS'));
        $ret->invert = $interval->invert;

        return $ret;
    }

    public static function create($spec)
    {
        return new static($spec);
    }

    /**
     * @return int
     */
    public function getUS()
    {
        return self::$usData[spl_object_hash($this)];
    }

    /**
     * @param int
     */
    public function setUS($us)
    {
        self::$usData[spl_object_hash($this)] = $us;
        return $this;
    }

    public function __toString()
    {
        return $this->format('%R Y%Y M%M D%D H%H I%I S%S');
    }
}
