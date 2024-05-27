<?php

namespace markapi\doc_clients;

class ApiResult
{

    private $methods = [];
    private $types   = [];
    private $module  = [];
    private $times   = [];
    private $docs    = [];


    function pushMethod()
    {

    }

    function pushType()
    {

    }

    function pushModule(string $module, string $task)
    {
        $this->module[$module][] = $task;
    }

    function pushTime(string $task, float $time)
    {
        $this->times[$task] = $time;
    }

    function pushDocs(string $task, string $docs)
    {
        $this->docs[$task] = $docs;
    }




    function getResult()
    {
        return [
            'methods' => $this->methods,
            'types' => $this->types,
            'module' => $this->module,
            'times' => $this->times,
            'docs' => $this->docs,
            'tags' => $this->tags->getTags(),
        ];
    }
}
