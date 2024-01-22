<?php
namespace markapi\_markers;
use markdi\markdi;
use markapi\location\Request;
use markapi\location\Redirect;

/**
 * @property-read Request $request
 * @property-read Redirect $redirect

*/
trait location {
    use markdi;

   function request(): Request { return new Request; }
   function redirect(): Redirect { return new Redirect; }

}