<?php

namespace markapi\api_auth;

use markapi\_markers\api_tools;

class AuthorizationClient
{
    use api_tools;

    public readonly ?int $clientId;


    function __construct()
    {
        $this->clientId = $this->session->get('authId', null);
    }




    function hasPermissions(...$permissions): bool
    {
        if (!$this->clientId)
            return false;
            
        // $this->authorizationModel->where(id: $this->clientId)->selectRow(per)
        
    }
}
