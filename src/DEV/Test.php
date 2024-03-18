<?php


namespace markapi\DEV;

use Attribute;
use marksync\provider\NotMark;

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
