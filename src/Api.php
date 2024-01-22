<?php

namespace markapi;

use markapi\_markers\location;

abstract class Api extends Doc
{
    use location;

    public $prefix = 'api';

    final function __construct()
    {
        header('Content-Type: application/json');
        ini_set('display_errors', 0);

        $result = [];

        $this->request->setPrefix($this->prefix);

        $task = $this->request->task;
        $props = $this->request->props;


        try {
            $result['data'] = $this->applytask($task, $props);
        } catch (\Throwable $th) {
            $result['error'] = [
                'message' => $th->getMessage(),
                'code' => $th->getCode(),
            ];
        }

        if ($this->redirect->to)
            $result['redirect'] = $this->redirect->to;

        exit(json_encode($result));
    }



    private function applyTask(string $taskName, $props)
    {
        if (!method_exists($this, $taskName))
            throw new \Exception("task is Undefined [$taskName]", 1);

        $this->checkMode($taskName, $props);

        return $this->{$taskName}(...$props);
    }


    
}
