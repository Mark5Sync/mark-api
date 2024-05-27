<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\authorization\Session;

/**
 * @property-read Session $session

*/
trait authorization {
    use provider;

   function createSession(): Session { return new Session; }

}