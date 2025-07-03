<?php declare(strict_types=1);

namespace Webforge\Common\Exception;

class NotImplementedExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testFromStringConstruct(): void
    {
        $this->expectException(NotImplementedException::class);
        throw NotImplementedException::fromString('parameter #2');
    }
}
