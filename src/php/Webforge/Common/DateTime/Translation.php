<?php

namespace Webforge\Common\DateTime;

abstract class Translation {
  public $lang;
  
  public $months;
  public $monthsAbbrev;
  
  public $weekdays;
  public $weekdaysAbbrev;
  
  
  
  public function getMonth($num) {
    return $this->months[$num];
  }

  public function getMonthAbbrev($num) {
    return $this->monthsAbbrev[$num];
  }
  
  public function getWeekDay($num) {
    return $this->weekdays[$num];
  }
  
  public function getWeekDayAbbrev($num) {
    return $this->weekdaysAbbrev[$num];
  }
}

?>