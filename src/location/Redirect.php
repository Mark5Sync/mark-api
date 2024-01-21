<?php

namespace markapi\location;

class Redirect
{
    public ?string $to = null;

    function to(string $path)
    {
        $this->to = $path;
    }

}
