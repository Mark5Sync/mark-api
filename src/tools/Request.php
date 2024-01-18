<?php

namespace markapi\tools;

use markdi\MarkInstance;


class Request
{
    public string $task;
    public array $props;


    function setPrefix(string $prefix = 'api')
    {
        $request_uri = $_SERVER['REQUEST_URI'];

        // Определяем регулярное выражение для извлечения значения параметра
        $pattern = "/\/$prefix\/([\w_]+)?\??/";

        // Ищем соответствие в строке запроса
        if (preg_match($pattern, $request_uri, $matches)) {
            // Извлекаем значение из найденных совпадений
            $param_value = isset($matches[1]) ? $matches[1] : null;
        }

        $this->task = $param_value ? $param_value : 'index';
        $this->props = !empty($_POST) ? $_POST : $_GET;
    }

}
