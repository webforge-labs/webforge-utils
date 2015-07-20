<?php

namespace Webforge\Common\DateTime;

class TranslationEN extends Translation  {

  public $lang = 'en_GB';
  
  public $months = array(
    1=>'January',
    2=>'February',
    3=>'March',
    4=>'April',
    5=>'May',
    6=>'June',
    7=>'July',
    8=>'August',
    9=>'September',
    10=>'October',
    11=>'November',
    12=>'December'
  );
  
  public $weekdays = array(
    1=>'Monday',
    2=>'Tuesday',
    3=>'Wednesday',
    4=>'Thursday',
    5=>'Friday',
    6=>'Saturday',
    0=>'Sunday',
  );
  
  public $weekdaysAbbrev = array(
    1=>'Mo',
    2=>'Tu',
    3=>'We',
    4=>'Th',
    5=>'Fr',
    6=>'Sa',
    0=>'Su',
  );

  public $monthsAbbrev = array(
    1=>'Jan',
    2=>'Feb',
    3=>'Mar',
    4=>'Apr',
    5=>'May',
    6=>'Jun',
    7=>'Jul',
    8=>'Aug',
    9=>'Sep',
    10=>'Oct',
    11=>'Nov',
    12=>'Dec'
  );
}

?>