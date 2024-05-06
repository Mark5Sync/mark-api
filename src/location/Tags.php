<?php

namespace markapi\location;

use markapi\_markers\location;


class Tags
{
    use location;

    private $list = [];

    function add(...$tags)
    {
        foreach ($tags as $tag) {
            $this->list[$this->request->task][$tag] = true;
        }
    }


    function getTags()
    {
        return array_map(fn($items) => array_keys($items), $this->list);
    }
}
