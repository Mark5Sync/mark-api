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

        try {
            $result['data'] = $this->applytask($task);
        } catch (\Throwable $th) {
            $result['error'] = [
                'message' => $th->getMessage(),
                'code' => $th->getCode(),
            ];
            $this->onError($th);
        }

        if ($this->redirect->to)
            $result['redirect'] = $this->redirect->to;

        if (!empty($this->request->exceptions))
            $result['exceptions'] = $this->request->exceptions;

        exit(json_encode($result));
    }


    protected function onInit(string $task)
    {
    }


    protected function onError(\Throwable $exception)
    {
    }



    private function run(string $task, array $props){
        $this->onInit($task);
        return $this->{$task}(...$props);
    }


    private function applyTask(string $task)
    {
        try {
            if (method_exists($this, $task))
                return $this->run($task, $this->request->getParamsFor($this, $task));
        } catch (\ArgumentCountError $th) {
            throw new \Exception("Задача [$task] ожидает другого количества аргументов", 888);
        }

        throw new \Exception("[$task] - не определена", 1);
    }
}
