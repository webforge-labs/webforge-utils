<?php

namespace Webforge\Common\JS;

use Webforge\Common\System\File;
use Closure;
use Webforge\Common\JS\JSONConverter;

class JSONFile {

  protected $file;

  public function __construct(File $file) {
    $this->file = $file;
    $this->jsonc = new JSONConverter();
  }

  /**
   * Modifies the JSON read from the file and saves it
   * 
   * if the modify function returns a object<stdClass> the file contents are replaced with the json representation from it
   * if the modify function does not return something it is assumed that the $json is just modified
   * @param Closure $modify function(&$json)
   */
  public function modify(Closure $modify) {
    $contents = $this->jsonc->parseFile($this->file);
    
    $modifiedContents = $modify($contents);

    if ($modifiedContents !== NULL) {
      $contents = $modifiedContents;
    }

    $this->file->writeContents(
      $this->jsonc->stringify($contents, JSONConverter::PRETTY_PRINT)
    );
  }
}
