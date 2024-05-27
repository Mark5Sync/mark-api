<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\exec\Executor;

/**
 * @property-read Executor $executor

*/
trait exec {
    use provider;

   function createExecutor(): Executor { return new Executor; }

}