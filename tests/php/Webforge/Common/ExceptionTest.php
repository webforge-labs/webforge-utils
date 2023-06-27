<?php

namespace Webforge\Common;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    private Exception $e;
    private Exception $nested;

    protected function setUp(): void
    {
        $this->e = new Exception('this is the #1 exception', 0);
        $this->nested = new Exception('this is the #2 exception', 0, $this->e);
    }

    public function testExceptionTextContainsException1()
    {
        $text = $this->e->toString('text');
        self::assertStringContainsString('this is the #1 exception', $text);
    }

    public function testExceptionTextContainsException1_inHTML()
    {
        $html = $this->e->toString('html');
        self::assertStringContainsString('this is the #1 exception', $html);
        self::assertStringContainsString('<b>Fatal Error:</b>', $html);
    }

    public function testExceptionTextPathsGetReplacedWhenRelativeDirIsGiven()
    {
        try {
            // the exception must come from this file
            $this->throwIt();
        } catch (Exception $ex) {
            $text = $ex->toString('text', __DIR__, 'replacedDir');
            self::assertStringContainsString('{replacedDir}', $text);
        }
    }

    public function testExceptionTextContainsException1And2ForNested()
    {
        $text = $this->nested->toString('text');

        self::assertStringContainsString('this is the #1 exception', $text);
        self::assertStringContainsString('this is the #2 exception', $text);
    }

    public function testHierarchy()
    {
        self::assertInstanceof('Exception', $this->e);
    }

    public function testMessageCanbeOverwritten()
    {
        $this->e->setMessage('blubb');
        self::assertEquals('blubb', $this->e->getMessage());
        self::assertStringNotContainsString('this is the #1 exception', $this->e->toString('text'));
    }

    public function testAppendMessageAddsTextToTheMessageToTheEnd()
    {
        $text = $this->e->appendMessage('. more detailed info.')->getMessage();

        self::assertStringContainsString('this is the #1 exception. more detailed info.', $text);
    }

    public function testPrependMessageAddsTextToTheMessageAtTheBeginning()
    {
        $text = $this->e->prependMessage('[verbose info] ')->getMessage();

        self::assertStringContainsString('[verbose info] this is the #1 exception', $text);
    }

    public function throwIt()
    {
        throw new Exception('this is the #1 exception', 0);
    }
}
