<?php


namespace markapi\DEV;

use Attribute;
use markdi\NotMark;

#[NotMark]
#[Attribute]
class Test
{

    public $props;

    function __construct(...$props)
    {
        $this->props = $props;
    }
}
