<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\location\pagination\Pagination;
use markapi\location\Redirect;
use markapi\location\Request;

/**
 * @property-read Pagination $pagination
 * @property-read Redirect $redirect
 * @property-read Request $request

*/
trait location {
    use provider;

   function pagination(): Pagination { return new Pagination; }
   function redirect(): Redirect { return new Redirect; }
   function request(): Request { return new Request; }

}