<?php

namespace Webforge\Common\JS;

use org\bovigo\vfs\vfsStream;
use Webforge\Common\System\File;

class JSONFileTest extends \PHPUnit_Framework_TestCase {
  
  public function setUp() {
    parent::setUp();

    vfsStream::setup('json-dir');
    $this->file = new File(vfsStream::url('json-dir/file.json'));
    $this->file->writeContents(<<<'JSON'
{
  "name": "webforge/common",
  "type": "library",
  "description": "Boilerplate for Webforge and Psc - CMS",
  "keywords": ["framework"],
  "homepage": "http://github.com/pscheit/webforge-common",
  "license": "LGPL-3.0"
}
JSON
    );

    $this->jsonFile = new JSONFile($this->file);
  }

  public function testModifyChangesTheFileInJSONFormatWhenClosureReturnsnewJsonObject() {
    $this->jsonFile->modify(function() {
      return (object) array('and-now'=>'something-completely-different');
    });

    $json = <<<'JSON'
{
  "and-now": "something-completely-different"
}
JSON;

    $this->assertJsonStringEqualsJsonFile((string) $this->file, $json);
  }

  public function testModifyChangesTheFileInJSONFormatWhenClosureModifiesTheObject() {
    $this->jsonFile->modify(function($json) {
      $json->name = 'webforge/common-changed';
    });

    $json = <<<'JSON'
{
  "name": "webforge/common-changed",
  "type": "library",
  "description": "Boilerplate for Webforge and Psc - CMS",
  "keywords": ["framework"],
  "homepage": "http://github.com/pscheit/webforge-common",
  "license": "LGPL-3.0"
}
JSON;

    $this->assertJsonStringEqualsJsonFile((string) $this->file, $json);
  }
}
