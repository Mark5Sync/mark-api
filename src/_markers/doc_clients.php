<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\doc_clients\ApiResult;
use markapi\doc_clients\TaskSandbox;

/**
 * @property-read ApiResult $apiResult

*/
trait doc_clients {
    use provider;

   function createApiResult(): ApiResult { return new ApiResult; }
   function createTaskSandbox(mixed $module, string $task, $onResult): TaskSandbox { return new TaskSandbox($module, $task, $onResult); }

}