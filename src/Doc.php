<?php

namespace markapi;

use Composer\ClassMapGenerator\ClassMapGenerator;
use markapi\_markers\doc_clients;
use markapi\_markers\exec;
use markapi\_markers\location;
use markapi\DEV\Test;
use markapi\DEV\Tests;
use markapi\doc_clients\TaskSandbox;
use marksync\provider\NotMark;
use ReflectionClass;
use ReflectionMethod;

#[NotMark]
abstract class Doc
{
    use location;
    use doc_clients;
    use exec;

    protected $routes = './';
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





    // protected function iterateModules($useReflection = false)
    // {
    //     foreach ([$this, ...$this->modules] as $module) {
    //         if (!$useReflection)
    //             yield $module => [];

    //         $reflectionModule = new ReflectionClass($module);
    //         yield $module => $this->ownMethods($reflectionModule);
    //     }
    // }


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

        $this->findRoutes($this->routes);

        return $this->apiResult->getResult();
    }


    function findRoutes($folder)
    {
        $map = ClassMapGenerator::createMap($folder);


        foreach ($map as $class => $path) {
            try {
                $reflection = new \ReflectionClass($class);
            } catch (\ReflectionException $th) {
                echo "\n\n{$th->getMessage()}\n\n";
            } catch (\Throwable $th) {
                echo "\n\n -- {$th->getMessage()}\n\n";
            }

            if (!$reflection->isSubclassOf(Route::class))
                continue;

            $module = new $class;
            $moduleName = $reflection->getShortName();

            foreach ($this->getTaskNameList($class) as $taskName) {
                $task = new TaskSandbox($module, $moduleName, $taskName, fn ($result) => $this->onResult($result));

                $task->run();

                $this->apiResult
                    ->pushMethod($task->query, $task->args)
                    ->pushGroup($task->query, $moduleName)

                    ->pushInputType($task->query,  $task->input,  $task->args['input'] )
                    ->pushOutputType($task->query, $task->output, $task->args['output'])

                    ->pushMain($task->query, $class, $taskName)
                    ->pushTime($task->query, $task->time)
                    ->pushDocs($task->query, $task->docs);
            }
        }
    }


    private function getTaskNameList($class)
    {
        $methods = get_class_methods($class);

        $tasks = [];
        foreach ($methods as $method) {
            if ($this->checkTestExists($class, $method))
                $tasks[] = $method;
        }

        return $tasks;
    }



    private function checkTestExists($class, $method)
    {
        $method = new ReflectionMethod($class, $method);
        if (!empty($method->getAttributes(Test::class)))
            return true;

        if (!empty($method->getAttributes(Tests::class)))
            return true;

        return false;
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
}
