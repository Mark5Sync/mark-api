<?php

namespace markapi\location;

class Redirect
{
    public ?string $to = null;
    public array $exceptions = [];

    function to(string $path)
    {
        $this->to = $path;
    }


}
