<?php declare(strict_types=1);

namespace Webforge\Common\DateTime;

use Webforge\Common\Preg;

class DateInterval extends \DateInterval
{
  /**
   * @var array<string, int|float> microseconds of the interval indexed by object hash
   */
    protected static array $usData = [];

    public function __construct(string $interval_spec)
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
     * Zusätzlich zum normalen Format gehen %u (microseconds) und %n (milliseconds) analag %U und %N
     */
    public function format(string $string): string
    {
        $ret = parent::format($string);

        if (mb_stripos($string, '%u') !== false) {
            $ret = preg_replace('/(?<!%)%u/u', (string) $this->getUS(), $ret);
            $ret = preg_replace('/(?<!%)%U/u', sprintf('%02d', $this->getUS()), $ret);
        }

        if (mb_stripos($string, '%n') !== false) {
            $ret = preg_replace('/(?<!%)%n/u', sprintf('%d', (int)($this->getUS() / 1000)), $ret);
            $ret = preg_replace('/(?<!%)%N/u', sprintf('%02d', (int)($this->getUS() / 1000)), $ret);
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

    public static function create(string $spec): static
    {
        return new self($spec);
    }

    public function getUS(): int|float
    {
        return self::$usData[spl_object_hash($this)];
    }

    public function setUS(int|float $us): self
    {
        self::$usData[spl_object_hash($this)] = $us;
        return $this;
    }

    public function __toString()
    {
        return $this->format('%R Y%Y M%M D%D H%H I%I S%S');
    }
}
