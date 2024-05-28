<?php

namespace markapi\doc_clients;

use markapi\_markers\location;

class ApiResult
{
    use location;

    private $methods = [];
    private $types   = [];
    private $module  = [];
    private $times   = [];
    private $docs    = [];
    private $boxes   = [];


    function pushMain(string $module, string $route, string $task)
    {
        $this->boxes[$module . ucfirst($task)] = [
            'route' => $route,
            'task' => $task
        ];

        return $this;
    }


    function pushMethod(string $task, array $args)
    {
        $this->methods[$task] = $args;
        return $this;
    }

    function pushInputType(string $module, mixed $input, bool $status)
    {
        if ($status)
            $this->types["{$module}Input"]  = $input;

        return $this;
    }

    function pushOutputType(string $module, mixed $output, bool $status)
    {
        if ($status)
            $this->types["{$module}Output"]  = $output;

        return $this;
    }

    function pushTime(string $task, float $time)
    {
        $this->times[$task] = $time;
        return $this;
    }

    function pushDocs(string $task, ?string $docs)
    {
        if ($docs)
            $this->docs[$task] = $docs;
        return $this;
    }

    function pushGroup(string $task, string $group)
    {
        $this->module[$task] = $group;

        return $this;
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
