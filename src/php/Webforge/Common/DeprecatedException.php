<?php

namespace Webforge\Common;

class DeprecatedException extends Exception
{
    public static function fromMethod($method)
    {
        return new static(sprintf('The function %s is deprecated', $method));
    }

    public static function fromMethodParam($method, $paramNum, $msg)
    {
        return new static(sprintf('The parameter #%d from function %s is deprecated: %s', $paramNum, $method, $msg));
    }
}
