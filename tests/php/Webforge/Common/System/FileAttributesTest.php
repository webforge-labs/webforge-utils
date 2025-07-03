<?php declare(strict_types=1);

namespace Webforge\Common\System;

class FileAttributesTest extends \PHPUnit\Framework\TestCase
{
    private File $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = new File(__FILE__);
    }

    public function testGetModifiedTimeReturnsATimestamp(): void
    {
        self::assertInstanceOf(\Webforge\Common\DateTime\DateTime::class, $this->file->getModifiedTime());
    }

    public function testGetCreateTimeReturnsATimestamp(): void
    {
        self::assertInstanceOf(\Webforge\Common\DateTime\DateTime::class, $this->file->getCreationTime());
    }

    public function testgetAccessTimeReturnsATimestamp(): void
    {
        self::assertInstanceOf(\Webforge\Common\DateTime\DateTime::class, $this->file->getAccessTime());
    }
}
