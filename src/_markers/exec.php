<?php
namespace markapi\_markers;
use markapi\exec\Executor;
use marksync\provider\provider;

/**
 * @property-read Executor $executor

*/
trait exec {
    use provider;

   function executor(): Executor { return new Executor; }

}