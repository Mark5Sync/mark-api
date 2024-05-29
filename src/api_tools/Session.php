<?php

namespace markapi\api_tools;


class Session
{

    function __construct()
    {
        try {
            session_start([
                'name' => '__flcrs__',
                'gc_maxlifetime' => 2592000,
                'cookie_lifetime' => 2592000,
            ]);
        } catch (\Exception $ex) {
        }
    }

    function set(string $key, string $value)
    {
        $_SESSION[$key] = $value;
    }

    function get(string $key, string $defaul = null)
    {
        if (!isset($_SESSION[$key]))
            return $defaul;

        return $_SESSION[$key];
    }

    function remove(string $key)
    {
        if (isset($_SESSION[$key]))
            unset($_SESSION[$key]);
    }
}
