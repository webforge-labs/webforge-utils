<?php

namespace Webforge\Common;

/**
 * A Command is the abstraction for a part of a program that gets executed
 *
 */
interface Command {
  
  const WARNING = 'Webforge.Common.Command.Warning';
  const INFO = 'Webforge.Common.Command.Info';
  
  /**
   * Run the actual command
   */
  public function execute(CommandOutput $output);
  
  /**
   * Return a textual Representation of the command
   *
   */
  public function describe();
}
