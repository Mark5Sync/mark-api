<?php
namespace markapi\_markers;
use markdi\markdi;
use markapi\exec\Executor;

/**
 * @property-read Executor $executor

*/
trait exec {
    use markdi;

   function executor(): Executor { return new Executor; }

}