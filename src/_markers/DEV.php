<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\DEV\Test;
use markapi\DEV\Tests;

/**
 * @property-read Test $test
 * @property-read Tests $tests

*/
trait DEV {
    use provider;

   function createTest(): Test { return new Test; }
   function createTests(): Tests { return new Tests; }

}