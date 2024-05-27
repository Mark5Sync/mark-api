<?php


namespace markapi\doc_clients;

use markapi\_markers\doc_clients;
use markapi\Route;
use marksync\provider\MarkInstance;

#[MarkInstance]
class ModuleTestContainer {

    use doc_clients;

    function __construct(private string $name, private Route $module)
    {
    }

    function analysis(callable $onResult)
    {
        $methods = get_class_methods($this->module);
        foreach ($methods as $method) {
            $this->createMethodTestContainer($this->module, $method)->analysis($onResult);
        } 
    }
}
