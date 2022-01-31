<?php

namespace Webforge\Common\DateTime;

class TimeTest extends \Webforge\Common\TestCase
{
  /**
   * @dataProvider provideFormatSpan
   */
    public function testFormatSpan($seconds, $assertion)
    {
        $this->assertEquals($assertion, Time::formatSpan($seconds, '%H:%I:%S'));
    }

    public function provideFormatSpan()
    {
        return array(
      array(60, '00:01:00'),
      array(61, '00:01:01'),
      array(71, '00:01:11'),
      array(60 * 60 * 2 + 23 * 60, '02:23:00'),
      array(60 * 60 * 2 + 23 * 60 + 10, '02:23:10'),
      array(60 * 60 * 2 + 23 * 60 + 1, '02:23:01'),
      array(23 * 60 * 60 + 59 * 60 + 59, '23:59:59'),
      array(24 * 60 * 60 + 59 * 60 + 59, '00:59:59'),
    );
    }
}
