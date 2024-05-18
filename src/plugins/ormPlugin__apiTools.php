<?php

namespace markapi\plugins;

use markapi\_markers\location;

/** 
 * mark orm plugin
*/
trait ormPlugin__apiTools
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


    function useTest()
    {
        $this->where(
            test: $this->request->isDebug
                    ? 1
                    : null
        );

        return $this;
    }
}
