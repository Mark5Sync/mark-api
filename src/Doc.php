<?php

namespace markapi;

use ReflectionClass;
use ReflectionMethod;

abstract class Doc
{


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

                $result[$methodName]['_results'][] = [
                    'args' => empty($props) ? null : json_encode($props),
                    'result' => $this->{$methodName}(...$props)
                ];
            }
        }

        return $result;
    }
}
