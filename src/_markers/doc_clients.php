<?php
namespace markapi\_markers;
use markapi\doc_clients\TypescriptClient;
use marksync\provider\provider;

/**

*/
trait doc_clients {
    use provider;

   function typescriptClient(): TypescriptClient { return new TypescriptClient; }

}