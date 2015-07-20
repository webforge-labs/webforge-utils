<?php

namespace Webforge\Common\System;

interface FileStorage {

  /**
   * @param string $url a relative path to a file in the directory with / and no / at the begining
   */
  public function getFile($url);

}
