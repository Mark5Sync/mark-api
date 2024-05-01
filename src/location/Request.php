<?php

namespace markapi\location;

use ReflectionMethod;

class Request
{
    public string $task;
    public array $params = [];
    public array $post = [];
    public array $get  = [];

    public bool $isDebug = false;
    public array $debugProps = [];

    public array $exceptions = [];

    function setPrefix(string $prefix = 'api')
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        $pattern = "/\/$prefix\/([\w_]+)?\??/";

        if (preg_match($pattern, $request_uri, $matches)) {
            $param_value = isset($matches[1]) ? $matches[1] : null;
        }
        
        $this->task = $param_value ? $param_value : 'index';
        $post = file_get_contents('php://input');
        if ($post)
            $this->post = json_decode($post, true);
        else
            $this->post = $_POST;

        $this->get = $_GET;
    }


    function getParamsFor($class, $method){
        $params = $this->getParams($class, $method);
        $this->params = $params;
        return $this->params;
    }


    private function getParams($class, $method): array {
        if (!empty($this->post))
            return $this->post;

        if (empty($this->get))
            return [];

        $ref = new ReflectionMethod($class, $method);
        $result = [];
        foreach ($ref->getParameters() as $methodParametrName => $methodParametr) {
            if ($methodParametr->isVariadic())
                return $this->get;

            $key = $methodParametr->name;


            $methodParametrType = $methodParametr->getType();
            if (!$methodParametrType) {
                $result[$key] = $methodParametrType[$key];
                continue;
            }

            $type = $methodParametr->getName();
            $bNull = $methodParametr->allowsNull();

            if (!isset($this->get[$key])) {
                if (!$bNull)
                    throw new \Exception("$key param not found for $method", 1);

                $result[$key] = null;
                continue;
            }

            switch ($type) {
                case 'int':
                    $result[$key] = (int)$this->get[$key];
                    break;
                case 'float':
                    $result[$key] = (float)$this->get[$key];
                    break;
                case 'bool':
                    $result[$key] = (bool)$this->get[$key];
                    break;
                default:
                    $result[$key] = $this->get[$key];
            }
        }

        return $result;
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



    function exception(\Throwable $exception, string $method) {
        $this->exceptions[] = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile() . ':' . $exception->getLine(),
            'task' => $method,
        ];
    }
}
