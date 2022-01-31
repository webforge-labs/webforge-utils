<?php

namespace Webforge\Common;

class DeprecatedExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testDeprecationConstructionrFromMethod()
    {
        $this->assertInstanceOf(__NAMESPACE__.'\DeprecatedException', $e = DeprecatedException::fromMethod(__METHOD__));
        $this->assertContains('deprecated', $e->getMessage());
    }

    public function testDeprecationConstructionrFromMethodParam()
    {
        $this->assertInstanceOf(__NAMESPACE__.'\DeprecatedException', $e = DeprecatedException::fromMethodParam(__METHOD__, 1, 'dont use this'));
        $this->assertContains('#1', $e->getMessage());
        $this->assertContains('deprecated', $e->getMessage());
        $this->assertContains('dont use this', $e->getMessage());
    }
}
