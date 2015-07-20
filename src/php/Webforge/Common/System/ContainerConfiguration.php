<?php

namespace Webforge\Common\System;

interface ContainerConfiguration {

  /**
   * @return array see ExecutableFinder::__construct
   */
  public function forExecutableFinder();
}
