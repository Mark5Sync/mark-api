<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\api_tools\Session;

/**
 * @property-read Session $session

*/
trait api_tools {
    use provider;

   function createSession(): Session { return new Session; }

}