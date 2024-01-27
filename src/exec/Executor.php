<?php

namespace markapi\exec;

class Executor {

    function runWithCorrectionPropsType($module, $method, $props)
    {
        $reflection = new \ReflectionMethod($module, $method);
        $correctionProps = [];
        foreach ($reflection->getParameters() as $parameter) {
            $info = $parameter->getType();
            $type = $info->getName();
            $key = $parameter->name;
            $bNull = $parameter->allowsNull();

            if (!isset($props[$key])) {
                if (!$bNull)
                    throw new \Exception("$key not found", 1);

                $correctionProps[$key] = null;
                continue;
            }


            switch ($type) {
                case 'int':
                    $correctionProps[$key] = (int)$props[$key];
                    break;
                case 'float':
                    $correctionProps[$key] = (float)$props[$key];
                    break;
                case 'bool':
                    $correctionProps[$key] = (bool)$props[$key];
                    break;
                default:
                    $correctionProps[$key] = $props[$key];

            }
        }
        return (is_string($module) ? new $module: $module)->{$method}(...$correctionProps);
    }

}