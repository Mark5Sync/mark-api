<?php
namespace markapi\_markers;

use marksync\provider\provider;
use markapi\location\Request;
use markapi\location\Redirect;

/**
 * @property-read Request $request
 * @property-read Redirect $redirect

*/
trait location {
    use provider;

   function request(): Request { return new Request; }
   function redirect(): Redirect { return new Redirect; }

}