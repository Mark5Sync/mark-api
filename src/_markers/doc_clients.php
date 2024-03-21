<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\doc_clients\TypescriptClient;

/**

*/
trait doc_clients {
    use provider;

   function typescriptClient(): TypescriptClient { return new TypescriptClient; }

}