<?php

namespace markapi\location;

class Request
{
    public string $task;
    public array  $props;
    public bool $isDebug = false;
    public array $debugProps = [];

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

        $post = file_get_contents('php://input');
        if ($post)
            $post = json_decode($post, true);

        $this->task = $param_value ? $param_value : 'index';
        $this->props = !empty($post)
            ? $post
            : (!empty($_POST)
                ? $_POST
                : $_GET
            );
    }


    function debugClear(){
        $this->debugProps = [];
    }

    function debugWrite(string $key, $value){
        $this->debugProps[$key] = $value;
    }

    function debugRead(string $key){
        if (!isset($this->debugProps[$key]))
            return null;

        return $this->debugProps[$key];
    }
}
