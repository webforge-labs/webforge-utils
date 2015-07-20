<?php

namespace Webforge\Common\Exception;

/**
 * Interface for objects that give additional context informations when exportet with the framework
 */
interface InExceptionExportable {

  public function exportExceptionText();
}
