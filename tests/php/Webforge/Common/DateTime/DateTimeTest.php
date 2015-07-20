<?php

namespace Webforge\Common\DateTime;

/**
 * put test data into testdata repository
 */
class DateTimeTest extends \Webforge\Common\TestCase {
  
  /**
   * @dataProvider i18nFormats
   */
  public function testI18nFormat($expectedFormat, $date, $formatString, $lang = 'en') {
    $this->assertEquals($expectedFormat, $date->i18n_format($formatString, $lang));
  }
  
  public function i18nFormats() {
    $tests = array();
    
    // in php this is the three chars abbrev!
    // there is no abbrev for 2 digits
    $tests[] = array('Mo', new DateTime('21.03.2011'), 'D');
    $tests[] = array('Tu', new DateTime('22.03.2011'), 'D');
    $tests[] = array('We', new DateTime('23.03.2011'), 'D');
    $tests[] = array('Th', new DateTime('24.03.2011'), 'D');
    $tests[] = array('Fr', new DateTime('25.03.2011'), 'D');
    $tests[] = array('Sa', new DateTime('26.03.2011'), 'D');
    $tests[] = array('Su', new DateTime('27.03.2011'), 'D');

    $tests[] = array('Monday',    new DateTime('21.03.2011'), 'l');
    $tests[] = array('Tuesday',   new DateTime('22.03.2011'), 'l');
    $tests[] = array('Wednesday', new DateTime('23.03.2011'), 'l');
    $tests[] = array('Thursday',  new DateTime('24.03.2011'), 'l');
    $tests[] = array('Friday',    new DateTime('25.03.2011'), 'l');
    $tests[] = array('Saturday',  new DateTime('26.03.2011'), 'l');
    $tests[] = array('Sunday',    new DateTime('27.03.2011'), 'l');

    $tests[] = array('Mo', new DateTime('21.03.2011'), 'D', 'de');
    $tests[] = array('Di', new DateTime('22.03.2011'), 'D', 'de');
    $tests[] = array('Mi', new DateTime('23.03.2011'), 'D', 'de');
    $tests[] = array('Do', new DateTime('24.03.2011'), 'D', 'de');
    $tests[] = array('Fr', new DateTime('25.03.2011'), 'D', 'de');
    $tests[] = array('Sa', new DateTime('26.03.2011'), 'D', 'de');
    $tests[] = array('So', new DateTime('27.03.2011'), 'D', 'de');

    $tests[] = array('Montag',    new DateTime('21.03.2011'), 'l', 'de');
    $tests[] = array('Dienstag',  new DateTime('22.03.2011'), 'l', 'de');
    $tests[] = array('Mittwoch',  new DateTime('23.03.2011'), 'l', 'de');
    $tests[] = array('Donnerstag',new DateTime('24.03.2011'), 'l', 'de');
    $tests[] = array('Freitag',   new DateTime('25.03.2011'), 'l', 'de');
    $tests[] = array('Samstag',   new DateTime('26.03.2011'), 'l', 'de');
    $tests[] = array('Sonntag',   new DateTime('27.03.2011'), 'l', 'de');
    
    return $tests;
  }
  
    
  public function testYesterday() {
    $now = time();
    $yesterday = $now-24*60*60;
    $beforeYesterday = $now-48*60*60;
    
    $now = DateTime::factory($now);
    $yesterday = DateTime::factory($yesterday);
    $beforeYesterday = DateTime::factory($beforeYesterday);
    
    $this->assertTrue($yesterday->isYesterday());
    $this->assertTrue($yesterday->isYesterday($now));
    
    $this->assertFalse($beforeYesterday->isYesterday());
    $this->assertFalse($beforeYesterday->isYesterday($now));

    $now->add(DateInterval::createFromDateString('1 DAY'));
    $this->assertFalse($yesterday->isYesterday($now));
  }
  
  public function testToday() {
    $now = DateTime::now();
    $this->assertTrue($now->isToday());
  }
  
  public function testisWeekDay() {
    $now = DateTime::parse('d.m.Y H:i','5.1.2012 12:00');
    $we = DateTime::parse('d.m.Y H:i', '4.1.2012 12:00');
    $mo = DateTime::parse('d.m.Y H:i', '2.1.2012 12:00');
    $su = DateTime::parse('d.m.Y H:i', '8.1.2012 12:00');
    
    
    $this->assertTrue($we->isWeekDay($now));
    $this->assertTrue($mo->isWeekDay($now));
    $this->assertTrue($su->isWeekDay($now));
    
    $now = DateTime::parse('d.m.Y','10.1.2012');
    $this->assertFalse($we->isWeekDay($now));
    $this->assertFalse($mo->isWeekDay($now));
    $this->assertFalse($su->isWeekDay($now));
  }

  /**
   * @dataProvider provideFormatSpan
   */
  public function testGetWeekday($day, $date, $assertion) {
    $this->assertEquals($assertion, $date->getWeekday($day)->format('d.m.Y'));
  }
  
  public function testParseFromRFC1123() {
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', DateTime::parse(DateTime::RFC1123 , 'Thu, 10 Nov 2011 07:28:18 GMT'));
  }


  public function testCoolSettersAndGetters() {
    $day = 12;
    $month = 1;
    $year = 2012;
    
    $date = new DateTime('12.1.2012');
    $this->assertSame($day,$date->getDay());
    $this->assertSame($month,$date->getMonth());
    $this->assertSame($year,$date->getYear());
    
    $date->setYear(1940);
    $this->assertSame($day,$date->getDay());
    $this->assertSame($month,$date->getMonth());
    $this->assertSame(1940,$date->getYear());
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testSetYearBecomesInt() {
    $date = DateTime::now();
    $date->setYear('2011');
  }

  public function testCopyReturnsAClonedAndRelativelyModifiedDate() {
    $date = new DateTime('07.08.2014');
    $copy = $date->copy();
    $this->assertEquals('07.08.2014', $copy->format('d.m.Y'));

    $otherDate = $date->copy('+1 day');
    $this->assertEquals('08.08.2014', $otherDate->format('d.m.Y'));

    $this->assertEquals('07.08.2014', $date->format('d.m.Y'));
  }
  
  public function provideFormatSpan() {
    return Array(
      
      // 14.03. ist der Montag der Woche
      array(DateTime::MON, new DateTime('14.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('14.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('14.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('14.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('14.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('14.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('14.03.2011'), '20.03.2011'),

      // 15.03. ist der Dienstag der Woche
      array(DateTime::MON, new DateTime('15.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('15.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('15.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('15.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('15.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('15.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('15.03.2011'), '20.03.2011'),
      
      // 16.03. ist der Mittwoch der Woche
      array(DateTime::MON, new DateTime('16.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('16.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('16.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('16.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('16.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('16.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('16.03.2011'), '20.03.2011'),

      // 17.03. ist der Donnerstag der Woche
      array(DateTime::MON, new DateTime('17.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('17.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('17.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('17.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('17.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('17.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('17.03.2011'), '20.03.2011'),


      // 18.03. ist der Freitag der Woche
      array(DateTime::MON, new DateTime('18.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('18.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('18.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('18.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('18.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('18.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('18.03.2011'), '20.03.2011'),


      // 19.03. ist der Samstag der Woche
      array(DateTime::MON, new DateTime('19.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('19.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('19.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('19.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('19.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('19.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('19.03.2011'), '20.03.2011'),


      // 20.03. ist der Sonntag der Woche
      array(DateTime::MON, new DateTime('20.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('20.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('20.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('20.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('20.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('20.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('20.03.2011'), '20.03.2011'),


      // 17.03. ist der Montag der folgenden Woche
      array(DateTime::MON, new DateTime('21.03.2011'), '21.03.2011'),
      array(DateTime::TUE, new DateTime('21.03.2011'), '22.03.2011'),
      array(DateTime::WED, new DateTime('21.03.2011'), '23.03.2011'),
      array(DateTime::THU, new DateTime('21.03.2011'), '24.03.2011'),
      array(DateTime::FRI, new DateTime('21.03.2011'), '25.03.2011'),
      array(DateTime::SAT, new DateTime('21.03.2011'), '26.03.2011'),
      array(DateTime::SUN, new DateTime('21.03.2011'), '27.03.2011'),
    );
  }

  /**
   * @dataProvider provideBefore
   */
  public function testBefore($expected, $subjectDate, $objectDate) {
    $subject = new DateTime($subjectDate);
    $object = new DateTime($objectDate);

    $this->assertEquals($expected, $subject->isBefore($object), 'failed asserting that: '.$subjectDate.'->isBefore('.$objectDate.')');
  }
  
  public static function provideBefore() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test(TRUE, '20.03.2014', '21.03.2014');
    $test(FALSE, '21.03.2014', '21.03.2014');
    $test(FALSE, '22.03.2014', '21.03.2014');
  
    return $tests;
  }

  /**
   * @dataProvider provideAfter
   */
  public function testAfter($expected, $subjectDate, $objectDate) {
    $subject = new DateTime($subjectDate);
    $object = new DateTime($objectDate);

    $this->assertEquals($expected, $subject->isAfter($object), 'failed asserting that: '.$subjectDate.'->isAfter('.$objectDate.')');
  }
  
  public static function provideAfter() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test(FALSE, '20.03.2014', '21.03.2014');
    $test(FALSE, '21.03.2014', '21.03.2014');
    $test(TRUE, '22.03.2014', '21.03.2014');
  
    return $tests;
  }

  /**
   * @dataProvider provideEqual
   */
  public function testEqual($expected, $subjectDate, $objectDate) {
    $subject = new DateTime($subjectDate);
    $object = new DateTime($objectDate);

    $this->assertEquals($expected, $subject->isEqual($object), 'failed asserting that: '.$subjectDate.'->isEqual('.$objectDate.')');
  }
  
  public static function provideEqual() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test(FALSE, '20.03.2014', '21.03.2014');
    $test(TRUE, '21.03.2014', '21.03.2014');
    $test(FALSE, '22.03.2014', '21.03.2014');
  
    return $tests;
  }
}
?>