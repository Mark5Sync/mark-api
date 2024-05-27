<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\dump\RoutesDump;

/**
 * @property-read RoutesDump $routesDump

*/
trait dump {
    use provider;

   function createRoutesDump(): RoutesDump { return new RoutesDump; }

}