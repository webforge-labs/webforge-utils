<?php

namespace Webforge\Common\DateTime;

/**
 * @todo check abbrevations
 */
class TranslationFR extends Translation  {

  public $lang = 'fr_FR';
  
  public $months = array(
    1=>'janvier',
    2=>'février',
    3=>'mars',
    4=>'avril',
    5=>'mai',
    6=>'juin',
    7=>'juillet',
    8=>'août',
    9=>'septembre',
    10=>'octobre',
    11=>'novembre',
    12=>'décembre'
  );
  
  public $weekdays = array(
    1=>'lundi',
    2=>'mardi',
    3=>'mercredi',
    4=>'jeudi',
    5=>'vendredi',
    6=>'samedi',
    0=>'dimanche'
  );

  
  public $weekdaysAbbrev = array(
    1=>'lu',
    2=>'ma',
    3=>'me',
    4=>'je',
    5=>'ve',
    6=>'sa',
    0=>'di',
  );

  // @Fixme: TODO
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