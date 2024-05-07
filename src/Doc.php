<?php

namespace markapi;

use markapi\_markers\doc_clients;
use markapi\_markers\exec;
use markapi\_markers\location;
use markapi\DEV\Test;
use markapi\DEV\Tests;
use marksync\provider\NotMark;
use ReflectionClass;
use ReflectionMethod;

#[NotMark]
abstract class Doc
{
    use location;
    use doc_clients;
    use exec;

    protected $modules = [];

    protected function _modules(): array
    {
        return [];
    }


    private function ownMethods($reflectionModule)
    {
        $classMethods = $reflectionModule->getMethods(ReflectionMethod::IS_PUBLIC);
        $classMethods = $this->methodHasTest($classMethods);

        $inheritedMethods = [];
        if ($reflectionModule->getParentClass()) {
            $inheritedMethods = $reflectionModule->getParentClass()->getMethods();
            $inheritedMethods = array_map(function ($method) {
                return $method->getName();
            }, $inheritedMethods);
        }

        $ownMethods = array_filter($classMethods, function ($method) use ($inheritedMethods) {
            return !in_array($method->getName(), $inheritedMethods);
        });

        return $ownMethods;
    }





    private function methodHasTest(array $refMethods)
    {
        $filter = array_filter($refMethods, function ($method) {
            if (!empty($method->getAttributes(Test::class)))
                return true;

            if (!empty($method->getAttributes(Tests::class)))
                return true;
        });

        return $filter;
    }





    protected function iterateModules($useReflection = false)
    {
        foreach ([$this, ...$this->modules] as $module) {
            if (!$useReflection)
                yield $module => [];

            $reflectionModule = new ReflectionClass($module);
            yield $module => $this->ownMethods($reflectionModule);
        }
    }


    protected function onResult($result)
    {
        return $result;
    }

    protected function verifyToken(string $token): bool
    {
        return true;
    }


    public function __DOC__(string $token = 'no-token')
    {
        $this->request->isDebug = true;

        if (!$this->verifyToken($token)) {
            http_response_code(401);
            throw new \Exception("Invalid token", 401);
        }


        $resultOutput = [];
        $resultMethods = [];
        $times = [];
        $docs = [];




        foreach ($this->iterateModules(true) as $module => $refMethods) {

            /** @var \ReflectionMethod $refMethod */
            foreach ($refMethods as $refMethod) {
                $tests = $this->typescriptClient()->analysis($module, $refMethod, function ($result) {
                    return $this->onResult($result);
                });

                if ($tests->pass)
                    continue;


                $resultOutput = [...$resultOutput, ...$tests->output];
                $resultMethods[$tests->methodName] = $tests->argsExists;
                $times[$tests->methodName] = $tests->time;

                if ($doc = $refMethod->getDocComment())
                    $docs[$tests->methodName] = trim(str_replace(['*', '/'], '', $doc), "\n");
            }
        }





        return [
            'methods' => $resultMethods,
            'types' => $resultOutput,
            'module' => $this->getTraitsMethods(array_keys($resultMethods)),
            'times' => $times,
            'docs' => $docs,
            'tags' => $this->tags->getTags(),
        ];
    }


    private function getTraitsMethods($methods)
    {
        $classReflection = new ReflectionClass($this);
        $traits = $classReflection->getTraits();

        $result = [];

        foreach ($traits as $trait) {
            foreach ($methods as $method) {
                if ($trait->hasMethod($method)) {
                    $fileName = end(explode('\\', $trait->name));
                    $result[$method] = $fileName;
                }
            }
        }


        return $result;
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


    protected function testExists(string $task)
    {
        if (!method_exists($this, $task))
            return false;

        $method = new ReflectionMethod($this, $task);
        if (!empty($method->getAttributes(Test::class)))
            return true;

        if (!empty($method->getAttributes(Tests::class)))
            return true;

        return false;
    }
}
