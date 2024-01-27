<?php
namespace markapi\_markers;
use markdi\markdi;
use markapi\doc_clients\TypescriptClient;

/**

*/
trait doc_clients {
    use markdi;

   function typescriptClient(): TypescriptClient { return new TypescriptClient; }

}