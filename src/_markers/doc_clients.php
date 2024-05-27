<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\doc_clients\ApiResult;
use markapi\doc_clients\TypescriptClient;

/**
 * @property-read ApiResult $apiResult

*/
trait doc_clients {
    use provider;

   function createApiResult(): ApiResult { return new ApiResult; }
   function createTypescriptClient(string $class): TypescriptClient { return new TypescriptClient($class); }

}