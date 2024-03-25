<?php

namespace markapi\location\pagination;

use markapi\_markers\location;

/** 
 * mark orm plugin
*/
trait usePagination
{
    use location;

    function usePagination()
    {
        $this->pagination->use = true;

        $this->page(
            $this->pagination->page,
            $this->pagination->size,
            $this->pagination->pages,
        );

        return $this;
    }
}
