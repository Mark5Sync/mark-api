<?php
namespace markapi\_markers;
use markdi\markdi;
use markapi\location\Redirect;

/**
 * @property-read Redirect $redirect

*/
trait location {
    use markdi;

   function redirect(): Redirect { return new Redirect; }

}