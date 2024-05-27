<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\location\Request;
use markapi\location\pagination\Pagination;
use markapi\location\Redirect;
use markapi\location\Tags;

/**
 * @property-read Request $request
 * @property-read Pagination $pagination
 * @property-read Redirect $redirect
 * @property-read Tags $tags

*/
trait location {
    use provider;

   function createRequest(): Request { return new Request; }
   function createPagination(): Pagination { return new Pagination; }
   function createRedirect(): Redirect { return new Redirect; }
   function createTags(): Tags { return new Tags; }

}