<?php

namespace markapi\location;

use JsonSerializable;

class Pagination implements JsonSerializable
{

    public $page = 1;
    public $size = 10;
    public $pages = 1;
    public $use = false;


    function set(int $page, int $size = 10)
    {
        $this->page = $page;
        $this->size = $size;

        $this->use = true;
    }


    public function jsonSerialize(): mixed
    {
        return [
            'page'  => $this->page,
            'size'  => $this->size,
            'pages' => $this->pages,
        ];
    }
}
