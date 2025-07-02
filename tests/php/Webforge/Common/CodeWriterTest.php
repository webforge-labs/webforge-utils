<?php

namespace Webforge\Common;

class CodeWriterTest extends \PHPUnit\Framework\TestCase
{
    protected $codeWriter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codeWriter = new CodeWriter();
    }

    /**
     * @dataProvider provideTestExportBaseTypeValue
     */
    public function testExportBaseTypeValue($expectedPHP, $var, $exception = null): void
    {
        $codeWriter = $this->codeWriter;

        /* Exception Test */
        if (isset($exception)) {
            self::assertException($exception, function () use ($codeWriter, $var): void {
                $codeWriter->exportBaseTypeValue($var);
            });

        /* Value Test */
        } else {
            self::assertEquals($expectedPHP, $codeWriter->exportBaseTypeValue($var));
        }
    }

    public static function provideTestExportBaseTypeValue()
    {
        $tests = [];
        $value = function ($expectedPHP, $var) use (&$tests): void {
            $tests[] = [$expectedPHP, $var];
        };
        $badType = 'Psc\Code\Generate\BadExportTypeException';

        $ex = function ($exceptionClass, $var): void {
            $tests[] = [null, $var, $exceptionClass];
        };

        // das ist ja die var_export funktionalität, aber zur Dokumentation
        $value("'meinstring'", 'meinstring');
        $value('1', 1);
        $value('0.23', 0.23);
        //$value('0x000000', 0x000000); // seems to have changed in php7 in var_export?

        // exceptions: alles was nicht wirklich einfach ist => error
        $ex($badType, []);
        $ex($badType, (object) ['blubb']);

        return $tests;
    }

    /**
     * @dataProvider provideTestExportList
     */
    public function testExportList($expectedPHP, $export): void
    {
        self::assertEquals($expectedPHP, $this->codeWriter->exportList($export));
    }

    public static function provideTestExportList()
    {
        $tests = [];
        $tel = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        //testListExport = tel

        // normal-export
        $tel(
            "array('eins','zwei','drei')",
            ['eins','zwei','drei']
        );

        // verschachtelung wird "geplättet"
        $tel(
            "array(array('gemein'),'drei','vier')",
            [['gemein'], 'drei','vier']
        );

        // integers
        $tel(
            "array(1,2,3)",
            [1,2,3]
        );

        // mixed types
        $tel(
            "array(1,array('eins','zwei'),3)",
            [1,['eins','zwei'],3]
        );

        // schlüssel haben keine bedeutung
        $tel(
            "array(1,array('eins','zwei'),3)",
            ['gemein' => 1,['gm' => 'eins','zwei'],3]
        );

        // floats
        // http://php.net/manual/de/function.var-export.php#113770
        /*
        $tel("array(0.12,array('eins','zwei'),3)",
             array(0.12, array('eins','zwei'), 3)
            );
        */

        // stdClass ist erlaubt (alles andere nicht)
        $tel(
            "array((object) array('objProp'=>'eins'))",
            [(object) ['objProp' => 'eins']]
        );

        // stdClass verschachtelt
        $tel(
            "array((object) array('objProp'=>array('eins','zwei','drei',(object) array('innerObjectProp'=>'blubb'))))",
            [(object) ['objProp' => ['eins','zwei','drei',(object) ['innerObjectProp' => 'blubb']]]]
        );

        return $tests;
    }

    /**
     * @dataProvider provideTestExportListException
     */
    public function testExportListException($exceptionClass, $export): void
    {
        $this->expectException($exceptionClass);

        $this->codeWriter->exportList($export);
    }

    public static function provideTestExportListException()
    {
        $tests = [];
        $ex = function () use (&$tests): void {
            $tests[] = func_get_args();
        };
        $badType = 'RuntimeException';

        $complexObject = new Exception('this is to complex to export');

        $ex(
            $badType,
            [$complexObject]
        );

        $ex(
            $badType,
            ['eins','zwei',$complexObject]
        );

        $ex(
            $badType,
            ['eins','zwei',[$complexObject]]
        );

        return $tests;
    }

    /**
     * @dataProvider provideConstructor
     */
    public function testWriteConstructor($expectedPHP, $class, array $parameters): void
    {
        self::assertEquals($expectedPHP, $this->codeWriter->exportConstructor($class, $parameters));
    }

    public static function provideConstructor()
    {
        $tests = [];
        $test = function () use (&$tests): void {
            $tests[] = func_get_args();
        };

        $simpleTest = function ($parametersPHP, array $parameters) use ($test, &$tests): void {
            $test(
                'new \Psc\Code\Generate\Simple(' . $parametersPHP . ')',
                new PHPClass('Psc\Code\Generate\Simple'),
                $parameters
            );
        };

        $simpleTest(
            "'mynicestring'",
            ['mynicestring']
        );

        $simpleTest(
            "'mynicestring',12,(object) array('objProp'=>array('eins','zwei','drei',(object) array('innerObjectProp'=>'blubb')))",
            ['mynicestring',12,(object) ['objProp' => ['eins','zwei','drei',(object) ['innerObjectProp' => 'blubb']]]]
        );

        return $tests;
    }
}
