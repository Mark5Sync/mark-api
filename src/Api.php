<?php

namespace markapi;

use ReflectionClass;
use ReflectionMethod;

abstract class Api
{

    final function __construct()
    {
        header('Content-Type: application/json');
        $result = [];

        try {
            $result['data'] = $this->findTask();
        } catch (\Throwable $th) {
            $result['error'] = [
                'message' => $th->getMessage(),
                'code' => $th->getCode(),
            ];
        }

        exit(json_encode($result));
    }


    private function findTask()
    {
        if ($result = $this->checkTask($_POST))
            return $result;

        if ($result = $this->checkTask($_GET))
            return $result;

        if ($result = $this->checkTask($_POST, 'index'))
            return $result;
    }

    private function checkTask(array $propsData, ?string $task = null)
    {
        if (!$task && !isset($propsData['task']))
            return;

        $task = $task
            ? $task
            : $propsData['task'];

        $props = isset($propsData['props'])
            ? $propsData['props']
            : $propsData;

        if (isset($props['task']))
            unset($props['task']);

        return $this->applyTask($task, $props);
    }


    private function applyTask($taskName, $props)
    {
        if (!method_exists($this, $taskName))
            throw new \Exception("task is Undefined [$taskName]", 1);

        return $this->{$taskName}(...$props);
    }



    protected function __DOC__()
    {
        $reflectionClass = new ReflectionClass($this);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        $methods = array_filter($methods, function ($method) {
            return $method->getAttributes(Test::class) != [];
        });


        $result = [];
        foreach ($methods as $method) {
            $methodName = $method->getName();
            $reflectionMethod = $reflectionClass->getMethod($method->getName());

            foreach ($reflectionMethod->getParameters() as $parameter) {
                $type = $parameter->getType();

                if (!$type)
                    $type = 'null';
                else if ($type instanceof \ReflectionNamedType)
                    $type = ($parameter->allowsNull() ? '?' : '') . $type->getName();
                else if ($type instanceof \ReflectionUnionType)
                    $type = array_map(fn ($tp) => "$tp", $type->getTypes());


                $result[$methodName]['_args'][$parameter->getName()] = $type;
            }

            $tests = $method->getAttributes(Test::class);
            foreach ($tests as $test) {
                $props = ($test->newInstance())->props;
                $result[$methodName]['_result'][] = $this->{$methodName}(...$props);
            }
        }

        return $result;
    }
}
