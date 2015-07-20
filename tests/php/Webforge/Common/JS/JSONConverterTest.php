<?php

namespace Webforge\Common\JS;

use stdClass;

class JSONConverterTest extends \Webforge\Common\TestCase {
  
  protected $converter;
  
  public function setUp() {
    parent::setUp();
    $this->converter = new JSONConverter();
  }
  
  public function testConverterCreate_ReturnsAnInstance() {
    $this->assertInstanceOf('Webforge\Common\JS\JSONConverter', JSONConverter::create());
  }
  
  /**
   * @dataProvider data2json
   */
  public function testStringifyConvertsStructuresToJSOn($data, $jsonEncoded) {
    $this->assertJsonStringEqualsJsonString(
      $jsonEncoded,
      $this->converter->stringify($data)
    );
  }
  
  /**
   * @dataProvider data2json
   */
  public function testParseConvertsJSONToStructures($data, $jsonEncoded) {
    $this->assertEquals(
      $data,
      $this->converter->parse($jsonEncoded)
    );
  }
  
  public static function data2json() {
    $tests = array();
    
    $test = function ($data) use (&$tests) {
      $tests[] = array($data, json_encode($data));
    };
    
    $test(
      array()
    );

    $test(
      new stdClass
    );
    
    $test(
       array (
         0 =>
         (object)array(
            'label' => 'Tag: Russland',
            'value' => 1,
            'tci' =>
           (object)array(
              'identifier' => 1,
              'id' => 'entities-tag-1-form',
              'label' => 'Tag: Russland',
              'fullLabel' => 'Tag: Russland',
              'drag' => false,
              'type' => 'entities-tag',
              'url' => NULL,
          )
        )
      )
    );
    
    return $tests;
  }
  
  public function testParseFileDecodesTheContentsOfAJSONFile() {
    $jsonFile = $this->getFile('some.json');
    
    // thats really stupid, because this is exactly the same code as in the lib
    // aquivalent would be to mock the file.. hmm
    $this->assertEquals(
      json_decode($jsonFile->getContents()),
      $this->converter->parseFile($jsonFile)
    );
  }
  
  public function testWrongJSONTHrowsAJSONParsingException() {
    $this->setExpectedException('Webforge\Common\JS\JSONParsingException', 'Parse error on line 13:');

    $this->converter->parse(<<<'JSON'
{
  "name": "webforge/common",
  "type": "library",
  "description": "Boilerplate for Webforge and Psc - CMS",
  "keywords": ["framework"],
  "homepage": "http://github.com/pscheit/webforge-common",
  "license": "MIT",
  "authors": [
    {"name": "Philipp Scheit", "email": "p.scheit@ps-webforge.com"}
  ],
  "require": {
    "php": ">=5.3.2",
    "ext-mbstring": "*",
  }
}
JSON
      );
  }

  public function testThrowsExceptionForNotRightlyEncodedValues() {
    $this->setExpectedException('Webforge\Common\JS\JSONParsingException', 'unknown JSON error: 5 while parsing string:');

    $this->converter->parse(
      base64_decode('eyJ1c2VybmFtZSI6Im1heG11c3Rlcm1hbm4iLCJkZXNjcmlwdGlvbiI6IldpbGxrb21tZW5zcHLkbWllIn0=')
    );
  }

  public function testParseThrowsExceptionWhenEmptyAsserted() {
    $this->setExpectedException('Webforge\Common\JS\JSONParsingException');
    
    $this->converter->parse('[]', 0);
  }

  public function testParseThrowsNOExceptionWhenEmptyAsserted_AndJSONNotEmpty() {
    $this->assertEquals(
      array('not empty'),
      $this->converter->parse('["not empty"]', 0)
    );
  }
  
  /**
   * @dataProvider data2json
   */
  public function testPrettyPrintDoesOnlyReformatting($data, $jsonEncoded) {
    $this->assertJsonStringEqualsJsonString(
      $jsonEncoded,
      $this->converter->stringify($data, JSONConverter::PRETTY_PRINT)
    );

    $this->assertJsonStringEqualsJsonString(
      $jsonEncoded,
      $this->converter->prettyPrint($jsonEncoded)
    );    
  }
}
