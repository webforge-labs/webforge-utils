<?php

namespace Webforge\Common;

class DeprecatedExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testDeprecationConstructionrFromMethod()
    {
        self::assertInstanceOf(__NAMESPACE__.'\DeprecatedException', $e = DeprecatedException::fromMethod(__METHOD__));
        self::assertStringContainsString('deprecated', $e->getMessage());
    }

    public function testDeprecationConstructionrFromMethodParam()
    {
        self::assertInstanceOf(__NAMESPACE__.'\DeprecatedException', $e = DeprecatedException::fromMethodParam(__METHOD__, 1, 'dont use this'));
        self::assertStringContainsString('#1', $e->getMessage());
        self::assertStringContainsString('deprecated', $e->getMessage());
        self::assertStringContainsString('dont use this', $e->getMessage());
    }
}
