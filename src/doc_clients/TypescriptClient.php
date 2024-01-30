<?php

namespace markapi\doc_clients;

use markapi\_markers\exec;
use markapi\_markers\location;
use markapi\_types\Join;
use markapi\DEV\Test;
use markapi\DEV\Tests;
use markdi\MarkInstance;

#[MarkInstance]
class TypescriptClient
{
    use location;
    use exec;

    private $refMethod;
    public $methodName;
    private $typeName;
    private $module;

    public $argsExists = [];

    public $output;
    public $pass = true;



    function analysis($module, $refMethod)
    {
        $this->module = is_string($module) ? new $module : $module;
        $this->refMethod = $refMethod;
        $this->methodName = $refMethod->getName();
        $resultMethods[$this->methodName] = [];
        $this->typeName = ucwords($this->methodName);

        $this->checkInput();
        $this->runTest();
        $this->tunTests();

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
            case 'array':
                $result = [];
                break;

            default:
                $result = 'avy';
        }

        if ($canToBeNull)
            return new Join($title, [null, $result]);

        return $result;
    }



    private function checkInput()
    {
        foreach ($this->refMethod->getParameters() as $parameter) {
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



    private function runTest()
    {
        $tests = $this->refMethod->getAttributes(Test::class);
        foreach ($tests as $test) {
            $this->pass = false;
            $this->request->debugClear();
            $props = ($test->newInstance())->props;

            try {
                $result = $this->module->{$this->methodName}(...(array)$props);
                // $result = $this->executor->runWithCorrectionPropsType($this, $refMethod, (array)$props);
            } catch (\Throwable $th) {
                $this->catchTestMessage($this->methodName, var_export($props, true), $th->getMessage());
                $result = null; //new Join($typeName, ['Error' => $th->getMessage()]);
            }

            $this->output["{$this->typeName}Output"] = $result;
            $this->argsExists['output'] = true;
        }
    }







    private function tunTests()
    {
        $tests = $this->refMethod->getAttributes(Tests::class);
        foreach ($tests as $test) {
            $this->pass = false;
            $propsList = ($test->newInstance())->tests;

            $testResult = [];

            foreach ($propsList as $props) {
                $this->request->debugClear();
                $props = is_array($props) ? $props : [$props];
                try {
                    $testResult[] = $this->module->{$this->methodName}(...$props);
                } catch (\Throwable $th) {
                    $this->catchTestMessage($this->methodName, var_export($props, true), $th->getMessage());
                    $testResult[] = null; //new Join($typeName, ['Error' => $th->getMessage()]);
                }
            }

            $this->output["{$this->typeName}Output"] = new Join($this->typeName, $testResult);
            $this->argsExists['output'] = true;
        }
    }



    private function catchTestMessage($method, $props, $message)
    {
        $message = <<<ERROR
        tunTests:
        {$method}({$props}) // {$message}
        ERROR;
        $this->request->exception(new \Exception(str_replace("\n", "", $message), 999));
    }
}
