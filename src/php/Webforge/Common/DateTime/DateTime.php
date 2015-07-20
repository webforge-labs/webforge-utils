<?php

namespace Webforge\Common\DateTime;

use DateTimeZone;
use Webforge\Common\Util AS Code;
use Webforge\Common\Exception;
use InvalidArgumentException;

class DateTime extends \DateTime {
  
  const MON = 1;
  const TUE = 2;
  const WED = 3;
  const THU = 4;
  const FRI = 5;
  const SAT = 6;
  const SUN = 7;
  
  /**
   * Dies ist TRUE wenn dem Konstruktor ein leer Timestamp-String übergeben wurde
   *
   * die Funktion format() gibt dann NULL zurück
   */
  protected $empty = FALSE;
  
  public function __construct($time = NULL, DateTimeZone $object = NULL) {
    if (isset($object)) {
      throw new Exception('Timezone im Constructor setzen geht nicht in PHP 5.3');
    }
    
    if ($time instanceof \DateTime) {
      $object = $time->getTimeZone(); // gets lost!
      $time = $time->getTimeStamp();
    }
    
    if (is_numeric($time) || is_int($time)) {
      $time = '@'.$time;
    }
    
    /* NULL object */
    if ($time == '@0' || $time == NULL) {
      $this->empty = TRUE;
    }

    /* hier object an den constructor zu übergeben bringt nichts! php 5.3 */
    parent::__construct($time);

    // exception für invalid Date
    $state = DateTime::getLastErrors();
    if ($state['warning_count'] > 0) {
      if (isset($state['warnings'][11])) {
        throw new ParsingException($state['warnings'][11]);
      }
    }
    
    $this->setTimeZone(new DateTimeZone(date_default_timezone_get()));
  }
  
  /**
   * @return DateTime
   */
  public static function factory($time = NULL, DateTimeZone $object = NULL) {
    return new DateTime($time, $object);
  }
  
  public static function create($time = NULL, DateTimeZone $object = NULL) { // alias
    return new static($time, $object);
  }
  
  public static function now(DateTimeZone $object = NULL) {
    return new static(time(),$object);
  }

  public static function createFromJSON($json) {
    if (is_numeric($json->date)) {
      return self::parse('U', (int) $json->date, new DateTimeZone($json->timezone));
    } else {
      return self::parse('Y-m-d H:i:s', $json->date, new DateTimeZone($json->timezone));
    }
  }

  public static function createFromMysql($string, DateTimeZone $timezone = NULL) {
    return self::parse('Y-m-d H:i:s', $string, $timezone);
  }
  
  /**
   * @return bool
   */
  public function isYesterday(DateTime $now = NULL) {
    if (!isset($now))
      $now = self::now();
    else
      $now = clone $now;
    
    $now->sub(DateInterval::createFromDateString('1 Day'));
    return $this->format('d.m.Y') === $now->format('d.m.Y');
  }
  
  /**
   * @return bool
   */
  public function isToday(DateTime $now = NULL) {
    if (!isset($now))
      $now = self::now();
    else
      $now = clone $now;
    
    return $this->format('d.m.Y') === $now->format('d.m.Y');
  }

  public function isBefore(DateTime $other) {
    return $this->getTimestamp() < $other->getTimestamp();
  }

  public function isAfter(DateTime $other) {
    return $this->getTimestamp() > $other->getTimestamp();
  }

  public function isEqual(DateTime $other) {
    return $this->getTimestamp() == $other->getTimestamp();
  }
  
  /**
   * Gibt Zurück ob der angegebene Timestamp in der Woche des aktuellen Timestamps (now) ist
   *
   * Die Woche beginnt bei Montag.
   * @return bool
   */
  public function isWeekDay(DateTime $now = NULL) {
    if (!isset($now)) $now = self::now();
    
    return $now->format('Y.W') === $this->format('Y.W'); // vergleich nach jahr + kalenderwoche
  }
  
  public function add($interval) {
    return parent::add($interval);
  }
  
  public function diff($object, $absolute = NULL) {
    return DateInterval::createFromDateInterval(parent::diff($object, $absolute));
  }

  /**
   * @return DateTime
   */
  public function copy($relativeDateIntervalString = NULL) {
    $date = clone $this;

    if ($relativeDateIntervalString) {
      $date->add(DateInterval::createFromDateString($relativeDateIntervalString));
    }

    return $date;
  }
  
  /**
   * Parsed einen String und gibt ein DateTime Objekt zurück
   *
   * http://www.php.net/manual/de/datetime.createfromformat.php
   */
  public static function parse($format, $time, DateTimeZone $timezone =  NULL) {
    if (!isset($timezone) && date_default_timezone_get() != '') {
      $timezone = new DateTimeZone(date_default_timezone_get());
    }

    $ret = parent::createFromFormat($format, $time, $timezone);
    
    if ($ret === FALSE) {
      throw new ParsingException('Aus '.Code::varInfo($time).' konnte keinen Zeitinformationen des Formates: '.Code::varInfo($format).' geparst werden. lastErrors: '.print_r(DateTime::getLastErrors(),true));
    }
    
    return new static($ret);
  }
  
  /**
   * Gibt den WochenTag der Woche zurück in dem sich der Timestamp befindet
   *
   * Die Woche beginnt am Montag. Wollen wir also den Sonntag eines Montages liegt das Datum in der Zukunft
   * Wollen wir einen Montag eines Samstags liegt das Datum in der Vergangenheit
   * Wollen wir den Montag des Sonntages liegt das Datum in der Vergangenheit
   * 
   * ist heute also Sonntag der 20.3.2011 und der Parameter ist MON gibt Funktion Montag den 14.03.2011 zurück (gleiche Uhrzeit wie jetzt)
   * @return DateTime
   */
  public function getWeekday($day) {
    
    $weekday = $this->format('w');
    /* das *-1 sortiert die Woche so (diese ist dann invers zu key, wo mo 6 ist):
      SO
      SA
      FR
      DO
      MI
      DI
      MO
      der zweite teil +($day-7) ist dann die relative Verschiebung vom Montag weg zum richtigen Wochentag
    */
      
    $diff = (($weekday-7)*-1) % 7 + ($day-7);
    
    $target = $this->add(DateInterval::createFromDateString($diff.' days'));
    return $target;
  }
  
  public function i18n_format($format, $lang = 'de') {
    $origformat = $format;
    if ($this->empty) return NULL;
    
    $constants = array('l'=>'\!\l',
                       'D'=>'\!\D',
                       
                       'F'=>'\!\F',
                       'M'=>'\!\M');
    
    $format = $this->format(str_replace(array_keys($constants),array_values($constants),$format));
    
    $search = $replace = array();
    switch ($lang) {
      case 'de':
      case 'de_DE':
        $transl = new TranslationDE();
        break;
      
      case 'en':
      case 'en_GB':
      case 'en_US':
      default:
        $transl = new TranslationEN();
        break;

      case 'fr':
      case 'fr_FR':
      default:
        $transl = new TranslationFR();
        break;
    }
    
    
    foreach ($constants as $constant => $NULL) {
      $search[] = '!'.$constant;
      
      switch ($constant) {
        case 'l':
          $replace[] = $transl->getWeekDay($this->format('w'));
        break;
      
        case 'D':
          $replace[] = $transl->getWeekDayAbbrev($this->format('w'));
        break;

        case 'F':
          $replace[] = $transl->getMonth($this->format('n'));
        break;

        case 'M':
          $replace[] = $transl->getMonthAbbrev($this->format('n'));
        break;
      }
    }
    
    return str_replace($search,$replace,$format);
  }
  
  
  public function getWalkableFields() {
    return array('date'=>$this->format('U'),
                 'timezone'=>$this->getTimezone()->getName()
                );
  }
  
  public function export() {
    return (object) array(
      'date'=>$this->format('U'),
      'timezone'=>$this->getTimezone()->getName()
    );
  }
  
  public static function import(\stdClass $o) {
    return self::parse('U', $o->date, new DateTimeZone($o->getTimeZone()));
  }
  
  // coole setters
  /**
   * @param int $year unbedingt ein int
   */
  public function setYear($year) {
    if (!is_int($year)) throw new InvalidArgumentException('Parameter 1 muss ein Integer sein');
    $this->setDate($year, $this->getMonth(), $this->getDay()); // das ist die PHP Funktion
    return $this;
  }
  
  public function getMonth() {
    return (int) $this->format('m');
  }

  public function getDay() {
    return (int) $this->format('d');
  }

  public function getYear() {
    return (int) $this->format('Y');
  }
  
  public function __toString() {
    return $this->format('U');
  }
  
  public function getVarInfo() {
    return '[DateTime: '.$this->format('d.m.Y H:i:s').' '.$this->getTimezone()->getName().']';
  }
}
?>