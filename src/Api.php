<?php

namespace markapi;

use markapi\_markers\tools;

abstract class Api extends Doc 
{
    use tools;

    final function __construct()
    {
        header('Content-Type: application/json');
        $result = [];

        try {

            $result['data'] = $this->applytask($this->request->task, $this->request->props);
        } catch (\Throwable $th) {
            $result['error'] = [
                'message' => $th->getMessage(),
                'code' => $th->getCode(),
            ];
        }

        exit(json_encode($result));
    }



    private function applyTask($taskName, $props)
    {
        if (!method_exists($this, $taskName))
            throw new \Exception("task is Undefined [$taskName]", 1);

        return $this->{$taskName}(...$props);
    }

}
