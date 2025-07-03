<?php

declare(strict_types=1);

namespace Webforge\Common\Exception;

interface MessageException
{
    public function setMessage($msg): self;

    public function appendMessage($msg): self;

    public function prependMessage($msg): self;
}
