<?php

namespace Webforge\Common\DateTime;

class DateIntervalTest extends \Webforge\Common\TestCase {
  
  protected $interval;

  public function setUp() {
    $this->interval = new DateInterval('P1Y');
    
    parent::setUp();
  }

  public function testConstruct() {
    $this->assertInstanceOf('Webforge\Common\DateTime\DateInterval',$this->interval);
  }
  
  /**
   * @dataProvider provideSimpleSpec
   */
  public function testConstructConvertSimpleSpec(\DateInterval $expectedInterval, $testSpec) {
    $format = '%R %Y %M %D %H %I %S';
    $actualInterval = new DateInterval($testSpec);
    $this->assertEquals($expectedInterval->format($format), $actualInterval->format($format));
  }
  
  public static function provideSimpleSpec() {
    $tests = array();
    $test = function ($intervalSpec,$testSpec) use (&$tests) {
      $tests[] = array(new \DateInterval($intervalSpec), $testSpec);
    };
    
    $test('P1Y', '1 YEAR');
    $test('P5Y', '5 YEARS');
    
    $test('P2D', '2 DAY');
    $test('P1D', '1 DAYS');
    
    // @TODO hours/minutes/seconds usw
    
    return $tests;
  }
  
  public function testAddTo() {
    $start = DateTime::create('21.11.1984 13:00');
    $iv = DateInterval::create('1 DAY');
    
    $this->assertEquals('22.11.1984 13:00', $iv->addTo($start)->format('d.m.Y H:i'));
    $this->assertEquals('21.11.1984 13:00', $start->format('d.m.Y H:i'));
  }
}
?>