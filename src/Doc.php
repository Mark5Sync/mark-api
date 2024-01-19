<?php

namespace markapi;

use markdi\NotMark;
use ReflectionClass;
use ReflectionMethod;

#[NotMark]
abstract class Doc
{


    protected function __DOC__()
    {
        $reflectionClass = new ReflectionClass($this);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        $methods = array_filter($methods, function ($method) {
            return $method->getAttributes(Test::class) != [];
        });


        $resultOutput = [];
        $resultMethods = [];
        foreach ($methods as $method) {
            $methodName = $method->getName();
            $resultMethods[$methodName] = [];
            $typeName = ucwords($methodName);
            $reflectionMethod = $reflectionClass->getMethod($method->getName());


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

                $resultOutput["{$typeName}Output"] = $this->{$methodName}(...$props);
                $resultMethods[$methodName]['output'] = true;
            }
        }

        return [
            'methods' => $resultMethods,
            'types' => $resultOutput,
        ];
    }
}
