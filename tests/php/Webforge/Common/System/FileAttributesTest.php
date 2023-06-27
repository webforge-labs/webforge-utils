<?php

namespace Webforge\Common\System;

use org\bovigo\vfs\vfsStream;

class FileAttributesTest extends \PHPUnit\Framework\TestCase
{
    private File $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = new File(__FILE__);
    }

    public function testGetModifiedTimeReturnsATimestamp()
    {
        self::assertInstanceOf('Webforge\Common\DateTime\DateTime', $this->file->getModifiedTime());
    }

    public function testGetCreateTimeReturnsATimestamp()
    {
        self::assertInstanceOf('Webforge\Common\DateTime\DateTime', $this->file->getCreationTime());
    }

    public function testgetAccessTimeReturnsATimestamp()
    {
        self::assertInstanceOf('Webforge\Common\DateTime\DateTime', $this->file->getAccessTime());
    }
}
