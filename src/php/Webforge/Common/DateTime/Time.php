<?php

namespace Webforge\Common\DateTime;

class Time
{
  /**
   * Formatiert eine Zeitspanne in Sekunden in einen String
   *
   * @param float $timespan in Sekunden
   * @param string $format http://de2.php.net/manual/de/dateinterval.format.php
   * @return string
   */
    public static function formatSpan($timespan, $format = '%Y years %M Months %D days %I Minutes %S seconds %H hours %n milliseconds')
    {
        $dv = Time::getDateInterval($timespan);

        return $dv->format($format);
    }

    /**
     * @param float $timespan in Sekunden
     * @return DateInterval
     * @TODO Tests schreiben
     */
    public static function getDateInterval($timespan)
    {
        /* Man könnte meinen, dass es einfach return new DateInterval('PT'.$timespan.'S') auch tut
          dann werden aber die Sekunden nicht auf Jahre / Tage / Monate verteilt.

          zum Vergleich:
          $timespan = 3600;

          $iv = new DateInterval('PT'.$timespan.'S');
          var_dump($iv->format('%Y years %M Months %D days %I Minutes %S seconds %H hours'));
          //string(59) "00 years 00 Months 00 days 00 Minutes 3600 seconds 00 hours"


          $iv = Time::getDateInterval($timespan);
          var_dump($iv->format('%Y years %M Months %D days %I Minutes %S seconds %H hours'));
          //string(57) "00 years 00 Months 00 days 00 Minutes 00 seconds 01 hours"
        */

        if (is_string($timespan) && !is_numeric($timespan)) {
            return new DateInterval($timespan);
        } else {
            $frac = $timespan * 1000000 - floor($timespan) * 1000000;
            $negative = $timespan < 0;
            $timespan = (int) abs($timespan);

            $now = new DateTime();

            $d2 = clone $now;
            $d2->add(new DateInterval('PT'.$timespan.'S'));

            $dateInterval = $d2->diff($now);
            $dateInterval->setUS($frac);
            $dateInterval->invert = (int) $negative;

            return $dateInterval;
        }
    }
    /**
     * @return DateTime
     * @TODO Tests schreiben
     */
    public static function getFirstOfMonth($month = null, $year = null)
    {
        if (!isset($month)) {
            $month = date('m');
        }
        if (!isset($year)) {
            $year = date('Y');
        }
        return new DateTime(mktime(0, 0, 0, $month, 1, $year));
    }

    /**
     * @return DateTime
     * @TODO Tests schreiben
     */
    public static function getLastOfMonth($month = null, $year = null)
    {
        if (!isset($month)) {
            $month = date('m');
        }
        if (!isset($year)) {
            $year = date('Y');
        }
        return new DateTime(mktime(0, 0, 0, $month, self::getDaysNumOfMonth($month, $year), $year));
    }

    /**
     * @return int
     * @TODO Tests schreiben
     */
    public static function getDaysNumOfMonth($month = null, $year = null)
    {
        if (!isset($month)) {
            $month = date('m');
        }
        if (!isset($year)) {
            $year = date('Y');
        }
        $first = self::getFirstOfMonth($month, $year);
        return (int) $first->format('t');
    }

    /**
     * @return int
     */
    public static function getAge(DateTime $birthday, $now = null)
    {
        if (!isset($now)) {
            $now = DateTime::now();
        }

        $age = $now->diff($birthday);
        return $age->y;
    }
}
