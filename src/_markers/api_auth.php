<?php
namespace markapi\_markers;
use marksync\provider\provider;
use markapi\api_auth\AuthorizationClient;
use markapi\api_auth\model\AuthorizationModel;

/**
 * @property-read AuthorizationClient $authorizationClient
 * @property-read AuthorizationModel $authorizationModel

*/
trait api_auth {
    use provider;

   function createAuthorizationClient(): AuthorizationClient { return new AuthorizationClient; }
   function createAuthorizationModel(): AuthorizationModel { return new AuthorizationModel; }

}