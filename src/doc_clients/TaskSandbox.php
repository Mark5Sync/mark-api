<?php

namespace markapi\doc_clients;

use markapi\_markers\doc_clients;
use markapi\_markers\exec;
use markapi\_markers\location;
use markapi\_types\Join;
use markapi\DEV\Test;
use markapi\DEV\Tests;
use marksync\provider\MarkInstance;

#[MarkInstance]
class TaskSandbox
{
    use location;
    use exec;
    use doc_clients;

    private \ReflectionMethod $ref;

    public string $query;
    public $args = [
        'input' => false,
        'output' => false,
    ];

    public $input;
    public $output;
    
    public $pass = true;
    public $time = 0;
    public $docs = null;



    function __construct(private mixed $module, private string $moduleName, private string $task, private $onResult = null)
    {
        $this->query = $moduleName . ucfirst($task);
        $this->ref = new \ReflectionMethod($module, $task);
    }



    function run()
    {
        $this->checkInput();

        $time = microtime(true);
        $this->runTest();
        $this->runTests();
        $this->time = microtime(true) - $time;
    }



    private function getInputType($name, $canToBeNull)
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

            case 'bool':
                $result = true;
                break;

            default:
                $result = 'avy';
        }

        if ($canToBeNull)
            return new Join($this->query, [null, $result]);

        return $result;
    }



    private function checkInput()
    {
        foreach ($this->ref->getParameters() as $parameter) {
            $type = $parameter->getType();


            if (!$type)
                $type = 'null';
            else if ($type instanceof \ReflectionNamedType)
                $type = $this->getInputType($type->getName(), $parameter->allowsNull()); // ($parameter->allowsNull() ? new Join($typeName, [null, $type->getName()]) : $type->getName());
            else if ($type instanceof \ReflectionUnionType)
                $type = array_map(fn ($tp) => "$tp", $type->getTypes());

            if (!$this->input)
                $this->input = [];

            $this->input[$parameter->getName()] = $type;

            $this->args['input'] = true;
        }
    }



    private function runTest()
    {
        $tests = $this->ref->getAttributes(Test::class);
        foreach ($tests as $test) {
            $this->pass = false;
            $this->request->debugClear();
            $props = ($test->newInstance())->props;

            try {
                $result = $this->wrapResult(fn () => $this->module->{$this->task}(...(array)$props));
            } catch (\Throwable $th) {
                $this->catchTestMessage(var_export($props, true), $th->getMessage());
                $result = null;
            }

            $this->output = $result;
            $this->args['output'] = true;
        }
    }



    private function wrapResult(callable $callback)
    {
        $onResult = $this->onResult;

        $this->pagination->use = false;
        $result = $onResult($callback());

        return $result;
    }



    private function runTests()
    {
        $tests = $this->ref->getAttributes(Tests::class);
        foreach ($tests as $test) {
            $this->pass = false;
            $propsList = ($test->newInstance())->tests;

            $testResult = [];

            foreach ($propsList as $props) {
                $this->request->debugClear();
                $props = is_array($props) ? $props : [$props];
                try {
                    $testResult[] = $this->wrapResult(fn () => $this->module->{$this->task}(...$props));
                } catch (\Throwable $th) {
                    $this->catchTestMessage(var_export($props, true), $th->getMessage());
                    $testResult[] = null;
                }
            }

            $this->output = new Join($this->task, $testResult);
            $this->args['output'] = true;
        }
    }



    private function catchTestMessage($props, $message)
    {
        $message = <<<ERROR
        tunTests:
        {$this->task}({$props}) // {$message}
        ERROR;

        $this->request->exception(new \Exception(str_replace("\n", "", $message), 999), $this->task);
    }
}
