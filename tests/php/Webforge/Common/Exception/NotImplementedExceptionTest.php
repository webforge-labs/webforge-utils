<?php

namespace Webforge\Common\Exception;

class NotImplementedExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testFromStringConstruct()
    {
        $this->setExpectedException(__NAMESPACE__ . '\\NotImplementedException');
        throw NotImplementedException::fromString('parameter #2');
    }
}
