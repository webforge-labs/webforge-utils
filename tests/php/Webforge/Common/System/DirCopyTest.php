<?php

namespace Webforge\Common\System;

use Webforge\Common\TestCase;

/**
 * @covers Webforge\Common\System\Dir
 */
class DirCopyTest extends TestCase
{
  /**
   * @var Webforge\Common\System\Dir
   */
    protected $source;

    /**
     * @var Webforge\Common\System\Dir[]
     */
    protected $temps = array();


    protected function setUp(): void
    {
        $this->source = $this->getTestDirectory('htdocs/');
        $this->fqn = 'Webforge\Common\System\Dir';
    }

    public function testCopiesAllFilesINSourceToTarget()
    {
        $target = $this->createTemporary();

        self::assertInstanceOf($this->fqn, $this->source->copy($target));

        $files = array(
      '/README.md',
      '/main.html',
      '/img/0.gif',
      '/css/sample.css',
      '/js/vendor/pack.js'
    );

        foreach ($files as $url) {
            self::assertFileExists((string) $target->getFile($url), $url.' was not copied to target.');
            self::assertFileExists((string) $this->source->getFile($url), $url.' was removed from source!!');
        }
    }

    /**
     * @return Webforge\Common\System\Dir
     */
    protected function createTemporary()
    {
        $this->temps[] = $dir = Dir::createTemporary();

        return $dir;
    }

    protected function tearDown(): void
    {
        foreach ($this->temps as $dir) {
            $dir->delete();
        }

        parent::tearDown();
    }
}
