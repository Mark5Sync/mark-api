<?php


namespace markapi;

use Attribute;

#[Attribute()]
class Test
{

    public $props;

    function __construct(...$props)
    {
        $this->props = $props;
    }
}
