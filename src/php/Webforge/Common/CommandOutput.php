<?php

namespace Webforge\Common;

/**
 * Abstract messages from a command
 */
interface CommandOutput {

  /**
   * Some normal printed message (a linebreak will be appended)
   * 
   */
  public function msg($msg);

  /**
   * A success message that is printed highlighted and should indicate a successful event
   */
  public function ok($msg);

  /**
   * A warning message that is printed highlighted and should indicate some information that is more important than a normal message, but not an error, yet
   */
  public function warn($msg);

}