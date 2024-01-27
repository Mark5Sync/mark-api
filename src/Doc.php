<?php

namespace markapi;

use markapi\_markers\doc_clients;
use markapi\_markers\location;
use markapi\_types\Join;
use markapi\DEV\Test;
use markapi\DEV\Tests;
use markdi\NotMark;
use ReflectionClass;
use ReflectionMethod;

#[NotMark]
abstract class Doc
{
    use location;
    use doc_clients;

    protected $modules = [];

    protected function modules(): array {
        return [];
    }

    private function ownMethods($module)
    {
        $reflectionClass = new ReflectionClass($module);
        $classMethods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        $inheritedMethods = [];
        if ($reflectionClass->getParentClass()) {
            $inheritedMethods = $reflectionClass->getParentClass()->getMethods();
            $inheritedMethods = array_map(function ($method) {
                return $method->getName();
            }, $inheritedMethods);
        }

        $ownMethods = array_filter($classMethods, function ($method) use ($inheritedMethods) {
            return !in_array($method->getName(), $inheritedMethods);
        });

        return $ownMethods;
    }





    protected function iterateModules($getOwnMethods = false)
    {
        foreach ([$this, ...$this->modules] as $module) {
            yield $module => $getOwnMethods
                ? $this->ownMethods($module)
                : [];
        }
    }


    protected function __DOC__()
    {
        $this->request->isDebug = true;

        $resultOutput = [];
        $resultMethods = [];


        foreach ($this->iterateModules(true) as $module => $methods) {
            $reflectionClass = new ReflectionClass($module);

            foreach ($methods as $method) {
                $tests = $this->typescriptClient()->analysis($reflectionClass, $method);
                if ($tests->pass)
                    continue;

                $resultOutput = [...$resultOutput, ...$tests->output];
                $resultMethods[$tests->methodName] = $tests->argsExists;
            }
        }


        return [
            'methods' => $resultMethods,
            'types' => $resultOutput,
        ];
    }


    protected function checkMode(string $methodName, $props)
    {
        if (empty($props))
            return;

        $method = new ReflectionMethod($this, $methodName);


        $tests = $method->getAttributes(Test::class);
        foreach ($tests as $test) {
            $testProps = ($test->newInstance())->props;

            if ($testProps == $props) {
                $this->request->isDebug = true;
                return;
            }
        }


        $tests = $method->getAttributes(Tests::class);
        foreach ($tests as $test) {
            $propsList = ($test->newInstance())->tests;

            $testResult = [];

            foreach ($propsList as $testProps) {
                if ($testProps == $props) {
                    $this->request->isDebug = true;
                    return;
                }
            }
        }
    }
}
