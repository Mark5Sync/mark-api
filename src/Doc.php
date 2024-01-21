<?php

namespace markapi;

use markapi\_types\Join;
use markapi\DEV\Test;
use markapi\DEV\Tests;
use markdi\NotMark;
use ReflectionClass;
use ReflectionMethod;

#[NotMark]
abstract class Doc
{

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


    protected function __DOC__()
    {
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
                    $type = ($parameter->allowsNull() ? '?' : '') . $type->getName();
                else if ($type instanceof \ReflectionUnionType)
                    $type = array_map(fn ($tp) => "$tp", $type->getTypes());


                $resultOutput["{$typeName}Input"][$parameter->getName()] = $type;
                $resultMethods[$methodName]['input'] = true;
            }


            $tests = $method->getAttributes(Test::class);
            foreach ($tests as $test) {
                $props = ($test->newInstance())->props;

                $resultOutput["{$typeName}Output"] = $this->{$methodName}(...(array)$props);
                $resultMethods[$methodName]['output'] = true;
            }


            $tests = $method->getAttributes(Tests::class);
            foreach ($tests as $test) {
                $propsList = ($test->newInstance())->tests;

                $testResult = [];

                foreach ($propsList as $props) {
                    $props = is_array($props) ? $props : [$props];
                    $testResult[] = $this->{$methodName}(...$props);
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
}
