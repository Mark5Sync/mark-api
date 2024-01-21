<?php

namespace markapi\_types;

use JsonSerializable;

class Join implements JsonSerializable {


    function __construct(private string $typeName, private array $props)
    {
    }

    function jsonSerialize(): array {
        return [
            "{$this->typeName}__TYPE__" => $this->props
        ];
    }

}