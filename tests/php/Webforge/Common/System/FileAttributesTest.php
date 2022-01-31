<?php

namespace Webforge\Common\System;

use org\bovigo\vfs\vfsStream;

class FileAttributesTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->file = new File(__FILE__);
    }

    public function testGetModifiedTimeReturnsATimestamp()
    {
        $this->assertInstanceOf('Webforge\Common\DateTime\DateTime', $this->file->getModifiedTime());
    }

    public function testGetCreateTimeReturnsATimestamp()
    {
        $this->assertInstanceOf('Webforge\Common\DateTime\DateTime', $this->file->getCreationTime());
    }

    public function testgetAccessTimeReturnsATimestamp()
    {
        $this->assertInstanceOf('Webforge\Common\DateTime\DateTime', $this->file->getAccessTime());
    }
}
