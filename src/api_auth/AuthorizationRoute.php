<?php

namespace markapi\api_auth;

use markapi\_markers\api_auth;
use markapi\DEV\Test;
use markapi\Route;


abstract class AuthorizationRoute extends Route
{
    use api_auth;

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
    function singIn(string $login, string $password)
    {
        $this->authorizationModel->where(login: $login)->selectRow(id: $authId, password_hash: $password_hash);

        if (!$authId || !$this->hash->verifyUserPasswordHash($password, $password_hash))
            throw new \Exception("Неправильный логин или пароль", 1);

        $this->session->set('authId', $authId);

        return true;
    }


    #[Test]
    function singOut()
    {
        $this->session->remove('UserId');
    }




    function add(string $login, string $password, ?array $permissions = null): int
    {
        return 4;
    }


    function changePassword(string $email, string $oldPassword, string $newPassword)
    {
    }
}
