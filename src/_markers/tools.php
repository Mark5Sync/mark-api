<?php
namespace markapi\_markers;
use markdi\markdi;
use markapi\tools\Request;

/**
 * @property-read Request $request

*/
trait tools {
    use markdi;

   function request(): Request { return new Request; }

}