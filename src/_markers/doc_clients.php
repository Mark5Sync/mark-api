<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\doc_clients\ModuleTestContainer;
use markapi\doc_clients\TypescriptClient;
use markapi\doc_clients\MethodTestContainer;
use markapi\doc_clients\ApiResult;

/**
 * @property-read ApiResult $apiResult

*/
trait doc_clients {
    use provider;

   function createModuleTestContainer(string $name, \markapi\Route $module): ModuleTestContainer { return new ModuleTestContainer($name, $module); }
   function createTypescriptClient(): TypescriptClient { return new TypescriptClient; }
   function createMethodTestContainer(\markapi\Route $class, string $method): MethodTestContainer { return new MethodTestContainer($class, $method); }
   function createApiResult(): ApiResult { return new ApiResult; }

}