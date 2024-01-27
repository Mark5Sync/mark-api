<?php

namespace markapi;

use markapi\_markers\location;
use ReflectionClass;
use ReflectionMethod;

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
        foreach ($this->iterateModules() as $module => $_) {
            if (!$this->existsMethodInModule($module, $taskName))
                continue;

            try {
                return $this->runWithCorrectionPropsType($module, $taskName, $props);
                // return  (is_string($module) ? new $module: $module)->{$taskName}(...$props);
            } catch (\ArgumentCountError $th) {
                throw new \Exception("Задача ожидает другого количества аргументов", 888);
            }
        }

        throw new \Exception("task is Undefined [$taskName]", 1);
    }


    private function runWithCorrectionPropsType($module, $method, $props)
    {
        $reflection = new ReflectionMethod($module, $method);
        $correctionProps = [];
        foreach ($reflection->getParameters() as $parameter) {
            $info = $parameter->getType();
            $type = $info->getName();
            $key = $parameter->name;
            $bNull = $parameter->allowsNull();

            if (!isset($props[$key])) {
                if (!$bNull)
                    throw new \Exception("$key not found", 1);

                $correctionProps[$key] = null;
                continue;
            }


            switch ($type) {
                case 'int':
                    $correctionProps[$key] = (int)$props[$key];
                    break;
                case 'float':
                    $correctionProps[$key] = (float)$props[$key];
                    break;
                case 'bool':
                    $correctionProps[$key] = (bool)$props[$key];
                    break;
                default:
                    $correctionProps[$key] = $props[$key];

            }
        }
        return (is_string($module) ? new $module: $module)->{$method}(...$correctionProps);
    }


    private function existsMethodInModule($module, $method)
    {
        $reflection = new ReflectionClass($module);
        return $reflection->hasMethod($method);
    }
}
