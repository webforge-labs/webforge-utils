<?php

namespace Webforge\Common\Exception;

class NotImplementedException extends \Webforge\Common\Exception
{
    public static function fromString($that)
    {
        return new static(sprintf("Behaviour for '%s' is not implemented, yet", $that));
    }
}
