<?php

namespace markapi\authorization;

use markapi\_markers\authorization as _markersAuthorization;
use markapi\DEV\Test;
use markapi\Route;


abstract class Authorization extends Route
{
    use _markersAuthorization;
    protected ?int $userId = null;


    function __construct()
    {
        $this->userId = $this->session->get('UserId', null);
    }


    private function emailIsValid(string $email)
    {
        return !!filter_var($email, FILTER_VALIDATE_EMAIL);
    }


    #[Test('picus.2022@mail.ru')]
    function validateEmail(string $email)
    {
        if (!$this->emailIsValid($email))
            throw new \Exception("Невалидный Email ($email)", 1);

        $this->authorizationModel->where(login: $email)->selectRow(id: $id);

        return !!$id;
    }


    #[Test('picus.2022@mail.ru', '111')]
    function singIn(string $email, string $password)
    {
        $this->authorizationModel->where(login: $email)->selectRow(id: $userId, password_hash: $password_hash);

        if (!$userId || !$this->hash->verifyUserPasswordHash($password, $password_hash))
            throw new \Exception("Неправильный логин или пароль", 1);

        $this->session->set('UserId', $userId);
    }


    #[Test]
    function singOut()
    {
        $this->session->remove('UserId');
    }
}
