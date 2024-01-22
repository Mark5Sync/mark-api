<?php

namespace markapi;

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

    private function ownMethods()
    {
        $reflectionClass = new ReflectionClass($this);
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


    private function getInputType($title, $name, $canToBeNull)
    {
        $result = null;
        switch ($name) {
            case 'int':
            case 'float':
                $result = 1;

                break;
            case 'string':
                $result = 'string';
                break;

            default:
                $result = 'undefined';
        }

        if ($canToBeNull)
            return new Join($title, [null, $result]);

        return $result;
    }


    protected function __DOC__()
    {
        $this->request->isDebug = true;
        $reflectionClass = new ReflectionClass($this);

        $resultOutput = [];
        $resultMethods = [];
        $methods = $this->ownMethods();
        foreach ($methods as $method) {
            $methodName = $method->getName();
            $resultMethods[$methodName] = [];
            $typeName = ucwords($methodName);
            $reflectionMethod = $reflectionClass->getMethod($methodName);


            foreach ($reflectionMethod->getParameters() as $parameter) {
                $type = $parameter->getType();

                if (!$type)
                    $type = 'null';
                else if ($type instanceof \ReflectionNamedType)
                    $type = $this->getInputType($typeName, $type->getName(), $parameter->allowsNull()); // ($parameter->allowsNull() ? new Join($typeName, [null, $type->getName()]) : $type->getName());
                else if ($type instanceof \ReflectionUnionType)
                    $type = array_map(fn ($tp) => "$tp", $type->getTypes());


                $resultOutput["{$typeName}Input"][$parameter->getName()] = $type;
                $resultMethods[$methodName]['input'] = true;
            }


            $tests = $method->getAttributes(Test::class);
            foreach ($tests as $test) {
                $this->request->debugClear();
                $props = ($test->newInstance())->props;

                try {
                    $result = $this->{$methodName}(...(array)$props);
                } catch (\Throwable $th) {
                    $result = null; //new Join($typeName, ['Error' => $th->getMessage()]);
                }

                $resultOutput["{$typeName}Output"] = $result;
                $resultMethods[$methodName]['output'] = true;
            }


            $tests = $method->getAttributes(Tests::class);
            foreach ($tests as $test) {
                $propsList = ($test->newInstance())->tests;

                $testResult = [];

                foreach ($propsList as $props) {
                    $this->request->debugClear();
                    $props = is_array($props) ? $props : [$props];
                    try {
                        $testResult[] = $this->{$methodName}(...$props);
                    } catch (\Throwable $th) {
                        $testResult[] = null; //new Join($typeName, ['Error' => $th->getMessage()]);
                    }
                }

                $resultOutput["{$typeName}Output"] = new Join($typeName, $testResult);
                $resultMethods[$methodName]['output'] = true;
            }
        }


        return [
            'methods' => $resultMethods,
            'types' => $resultOutput,
        ];
    }


    protected function checkMode(string $methodName, $props)
    {
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
