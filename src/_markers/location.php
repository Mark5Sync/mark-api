<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\location\Request;
use markapi\location\pagination\Pagination;
use markapi\location\Tags;
use markapi\location\Redirect;

/**
 * @property-read Request $request
 * @property-read Pagination $pagination
 * @property-read Tags $tags
 * @property-read Redirect $redirect

*/
trait location {
    use provider;

   function createRequest(): Request { return new Request; }
   function createPagination(): Pagination { return new Pagination; }
   function createTags(): Tags { return new Tags; }
   function createRedirect(): Redirect { return new Redirect; }

}