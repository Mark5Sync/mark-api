<?php
namespace markapi\_markers;
use markdi\markdi;
use markapi\tools\Doc;
use markapi\tools\Request;

/**
 * @property-read Doc $doc
 * @property-read Request $request

*/
trait tools {
    use markdi;

   function doc(): Doc { return new Doc; }
   function request(): Request { return new Request; }

}