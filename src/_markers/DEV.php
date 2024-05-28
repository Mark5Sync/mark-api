<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\DEV\Tests;
use markapi\DEV\Test;

/**
 * @property-read Tests $tests
 * @property-read Test $test

*/
trait DEV {
    use provider;

   function createTests(): Tests { return new Tests; }
   function createTest(): Test { return new Test; }

}