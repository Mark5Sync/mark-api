<?php


namespace markapi\DEV;

use Attribute;
use markdi\NotMark;

#[NotMark]
#[Attribute]
class Tests
{

    public $tests;

    function __construct(array $tests)
    {
        $this->tests = $tests;
    }
}
