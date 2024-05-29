<?php

namespace markapi;

use markapi\_markers\location;

abstract class Api extends Doc
{
    use location;

    public $prefix = 'api';


    final function __construct()
    {
        header('Content-Type: application/json');
        ini_set('display_errors', 0);

        $result = [];

        $this->request->setPrefix($this->prefix);

        $task = $this->request->task;

        try {
            $result['data'] = $this->applytask($task);
        } catch (\Throwable $th) {
            $result['error'] = [
                'message' => $th->getMessage(),
                'code' => $th->getCode(),
            ];
            $this->onError($th);
            http_response_code(400);
        }

        if ($this->redirect->to)
            $result['redirect'] = $this->redirect->to;

        if (!empty($this->request->exceptions))
            $result['exceptions'] = $this->request->exceptions;




        $strResult = json_encode($result);

        if ($strResult === false)
            $strResult = json_encode(['error' => $this->getJsonError()]);

        exit($strResult);
    }


    private function getJsonError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return 'json_encode - Ошибок нет';
            case JSON_ERROR_DEPTH:
                return 'json_encode - Достигнута максимальная глубина стека';
            case JSON_ERROR_STATE_MISMATCH:
                return 'json_encode - Некорректные разряды или несоответствие режимов';
            case JSON_ERROR_CTRL_CHAR:
                return 'json_encode - Некорректный управляющий символ';
            case JSON_ERROR_SYNTAX:
                return 'json_encode - Синтаксическая ошибка, некорректный JSON';
            case JSON_ERROR_UTF8:
                return 'json_encode - Некорректные символы UTF-8, возможно неверно закодирован';
            default:
                return 'json_encode - Неизвестная ошибка';
        }
    }


    protected function onInit(string $task)
    {
    }


    protected function onError(\Throwable $exception)
    {
    }






    protected function onResult($result)
    {
        if ($this->pagination->use)
            return [
                'content' => $result,
                'pagination' => $this->pagination,
            ];

        return $result;
    }


    private function run($class, string $task, array $props)
    {
        $this->onInit($task);

        return $class->{$task}(...$props);
    }


    private function applyTask(string $task)
    {
        if (!in_array($task, ['__doc__', '_'])) {
            $scheme = file_exists("{$this->routes}/scheme.json") ? json_decode(file_get_contents("{$this->routes}/scheme.json"), true) : [];

            if (!isset($scheme[$task])) {
                http_response_code(527);
                throw new \Exception("Задача не существует [$task]", 527);
            }

            ['route' => $route, 'task' => $task] = $scheme[$task];

            if (!$this->checkTestExists($route ? new $route : $this, $task)) {
                http_response_code(528);
                throw new \Exception("Задача не существует [$task]", 528);
            }
        }



        try {
            $result = $this->run($route ? new $route : $this, $task, $this->request->getParamsFor($this, $task));
            return $this->request->isDebug ? $result : $this->onResult($result);
        } catch (\ArgumentCountError $th) {
            http_response_code(528);
            throw new \Exception("Задача [$task] ожидает другого количества аргументов", 528);
        }

        throw new \Exception("[$task] - не определена", 1);
    }




    function _($props)
    {

        die(json_encode(['_' => 'me']));

        return 'merge';
    }
}
