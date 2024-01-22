<?php
namespace markapi\_markers;
use markdi\markdi;
use markapi\DEV\Test;
use markapi\DEV\Tests;

/**
 * @property-read Test $test
 * @property-read Tests $tests

*/
trait DEV {
    use markdi;

   function test(): Test { return new Test; }
   function tests(): Tests { return new Tests; }

}