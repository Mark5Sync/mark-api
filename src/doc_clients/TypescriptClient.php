<?php

namespace markapi\doc_clients;

use markapi\_markers\location;
use markapi\_types\Join;
use markapi\DEV\Test;
use markapi\DEV\Tests;
use markdi\MarkInstance;

#[MarkInstance]
class TypescriptClient
{
    use location;

    private $reflectionClass;
    private $method;
    private $typeName;

    public $argsExists = [];

    public $methodName;
    public $output;
    public $pass = true;

    function analysis($reflectionClass, $method)
    {
        $this->methodName = $method->getName();
        $resultMethods[$this->methodName] = [];
        $this->typeName = ucwords($this->methodName);


        $this->checkInput($reflectionClass, $method);
        $this->runTest($method);
        $this->tunTests($method);

        return $this;
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

    function checkInput($reflectionClass, $methodName)
    {
        $reflectionMethod = $reflectionClass->getMethod($methodName);

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type)
                $type = 'null';
            else if ($type instanceof \ReflectionNamedType)
                $type = $this->getInputType($this->typeName, $type->getName(), $parameter->allowsNull()); // ($parameter->allowsNull() ? new Join($typeName, [null, $type->getName()]) : $type->getName());
            else if ($type instanceof \ReflectionUnionType)
                $type = array_map(fn ($tp) => "$tp", $type->getTypes());


            $this->output["{$this->typeName}Input"][$parameter->getName()] = $type;

            $this->argsExists['input'] = true;
        }
    }



    private function runTest($methodName)
    {
        $tests = $this->method->getAttributes(Test::class);
        foreach ($tests as $test) {
            $this->pass = false;
            $this->request->debugClear();
            $props = ($test->newInstance())->props;

            try {
                $result = $this->{$methodName}(...(array)$props);
            } catch (\Throwable $th) {
                $result = null; //new Join($typeName, ['Error' => $th->getMessage()]);
            }

            $this->output["{$this->typeName}Output"] = $result;
            $argsExists['output'] = true;
        }
    }


    private function tunTests($methodName)
    {
        $tests = $this->method->getAttributes(Tests::class);
        foreach ($tests as $test) {
            $this->pass = false;
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

            $this->output["{$this->typeName}Output"] = new Join($this->typeName, $testResult);
            $this->argsExists['output'] = true;
        }
    }
}
