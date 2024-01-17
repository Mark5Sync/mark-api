<?php
namespace markapi\_markers;
use markdi\markdi;
use markapi\Test;

/**
 * @property-read Test $test

*/
trait main {
    use markdi;

   function test(): Test { return new Test; }

}