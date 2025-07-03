<?php declare(strict_types=1);

namespace Webforge\Common;

/**
 * @todo start something like this in testdata repository
 */
class TestValueObject
{
    public function __construct(protected $prop1, protected $prop2)
    {
    }

    public function getProp1()
    {
        return $this->prop1;
    }

    public function setProp1($p1Value): static
    {
        $this->prop1 = $p1Value;
        return $this;
    }

    public function getProp2()
    {
        return $this->prop2;
    }

    public function setProp2($p2Value): static
    {
        $this->prop2 = $p2Value;
        return $this;
    }
}
