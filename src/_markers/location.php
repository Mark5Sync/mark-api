<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\location\pagination\Pagination;
use markapi\location\Request;
use markapi\location\Tags;
use markapi\location\Redirect;

/**
 * @property-read Pagination $pagination
 * @property-read Request $request
 * @property-read Tags $tags
 * @property-read Redirect $redirect

*/
trait location {
    use provider;

   function pagination(): Pagination { return new Pagination; }
   function request(): Request { return new Request; }
   function tags(): Tags { return new Tags; }
   function redirect(): Redirect { return new Redirect; }

}