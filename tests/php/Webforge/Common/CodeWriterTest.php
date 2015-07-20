<?php

namespace Webforge\Common;

class CodeWriterTest extends \PHPUnit_Framework_TestCase {

  protected $codeWriter;
  
  public function setUp() {
    parent::setUp();

    $this->codeWriter = new CodeWriter();
  }

  /**
   * @dataProvider provideTestExportBaseTypeValue
   */
  public function testExportBaseTypeValue($expectedPHP, $var, $exception = NULL) {
    $codeWriter = $this->codeWriter;
    
    /* Exception Test */
    if (isset($exception)) {
      $this->assertException($exception, function() use ($codeWriter,$var) {
        $codeWriter->exportBaseTypeValue($var);
      });
    
    
    /* Value Test */
    } else {
      
      $this->assertEquals($expectedPHP, $codeWriter->exportBaseTypeValue($var));
    }
  }
  
  public static function provideTestExportBaseTypeValue() {
    $tests = array();
    $value = function($expectedPHP, $var) use (&$tests) {
      $tests[] = array($expectedPHP, $var);
    };
    $badType = 'Psc\Code\Generate\BadExportTypeException';
    
    $ex = function ($exceptionClass, $var) {
      $tests[] = array(NULL, $var, $exceptionClass);
    };
    
    // das ist ja die var_export funktionalität, aber zur Dokumentation
    $value("'meinstring'", 'meinstring');
    $value('1', 1);
    $value('0.23', 0.23);
    $value('0x000000', 0x000000);
    
    // exceptions: alles was nicht wirklich einfach ist => error
    $ex($badType, array());
    $ex($badType, (object) array('blubb'));
    
    return $tests;
  }
  
  
  /**
   * @dataProvider provideTestExportList
   */
  public function testExportList($expectedPHP, $export) {
    $this->assertEquals($expectedPHP, $this->codeWriter->exportList($export));
  }

  public static function provideTestExportList() {
    $tests = array();
    $tel = function () use (&$tests) {
      $tests[] = func_get_args();
    };
    
    //testListExport = tel
    
    // normal-export
    $tel("array('eins','zwei','drei')",
         array('eins','zwei','drei')
        );
    
    // verschachtelung wird "geplättet"
    $tel("array(array('gemein'),'drei','vier')",
         array(array('gemein'), 'drei','vier')
        );

    // integers
    $tel("array(1,2,3)",
         array(1,2,3)
        );

    // mixed types
    $tel("array(1,array('eins','zwei'),3)",
         array(1,array('eins','zwei'),3)
        );

    // schlüssel haben keine bedeutung
    $tel("array(1,array('eins','zwei'),3)",
         array('gemein'=>1,array('gm'=>'eins','zwei'),3)
        );

    // floats
    // http://php.net/manual/de/function.var-export.php#113770
    /*
    $tel("array(0.12,array('eins','zwei'),3)",
         array(0.12, array('eins','zwei'), 3)
        );
    */

    // stdClass ist erlaubt (alles andere nicht)
    $tel("array((object) array('objProp'=>'eins'))",
        array((object) array('objProp'=>'eins')));

    // stdClass verschachtelt
    $tel("array((object) array('objProp'=>array('eins','zwei','drei',(object) array('innerObjectProp'=>'blubb'))))",
        array((object) array('objProp'=>array('eins','zwei','drei',(object) array('innerObjectProp'=>'blubb')))));
    
    return $tests;
  }

  /**
   * @dataProvider provideTestExportListException
   */
  public function testExportListException($exceptionClass, $export) {
    $this->setExpectedException($exceptionClass);

    $this->codeWriter->exportList($export);
  }
  
  public static function provideTestExportListException() {
    $tests = array();
    $ex = function () use (&$tests) {
      $tests[] = func_get_args();
    };
    $badType = 'RuntimeException';
    
    $complexObject = new Exception('this is to complex to export');

    $ex($badType,
         array($complexObject)
        );

    $ex($badType,
         array('eins','zwei',$complexObject)
        );

    $ex($badType,
         array('eins','zwei',array($complexObject))
        );
    
    return $tests;
  }

  /**
   * @dataProvider provideConstructor
   */
  public function testWriteConstructor($expectedPHP, $class, Array $parameters) {
    $this->assertEquals($expectedPHP, $this->codeWriter->exportConstructor($class, $parameters));
  }
  
  public static function provideConstructor() {
    $tests = array();
    $test = function () use (&$tests) {
      $tests[] = func_get_args();
    };
    
    $simpleTest = function ($parametersPHP, Array $parameters) use ($test, &$tests) {
      $test('new \Psc\Code\Generate\Simple('.$parametersPHP.')',
        new PHPClass('Psc\Code\Generate\Simple'),
        $parameters
      );
    };
    
    $simpleTest(
      "'mynicestring'",
      array('mynicestring')
    );
    
    $simpleTest(
      "'mynicestring',12,(object) array('objProp'=>array('eins','zwei','drei',(object) array('innerObjectProp'=>'blubb')))",
      array('mynicestring',12,(object) array('objProp'=>array('eins','zwei','drei',(object) array('innerObjectProp'=>'blubb'))))
    );
    
    return $tests;
  }
}
