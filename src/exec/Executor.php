<?php

namespace markapi\exec;

class Executor
{

    private function convertProps($reflectionMethod, $props)
    {
        $result = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $key = $parameter->name;

            if ($parameter->isVariadic())
                return $props;


            $info = $parameter->getType();
            if (!$info) {
                $result[$key] = $props[$key];
                continue;
            }

            $type = $info->getName();
            $bNull = $parameter->allowsNull();

            if (!isset($props[$key])) {
                if (!$bNull)
                    throw new \Exception("$key not found", 1);

                $result[$key] = null;
                continue;
            }


            switch ($type) {
                case 'int':
                    $result[$key] = (int)$props[$key];
                    break;
                case 'float':
                    $result[$key] = (float)$props[$key];
                    break;
                case 'bool':
                    $result[$key] = (bool)$props[$key];
                    break;
                default:
                    $result[$key] = $props[$key];
            }
        }

        return $result;
    }

    function runWithCorrectionPropsType($module, $method, $props)
    {
        $correctionProps = $this->convertProps(new \ReflectionMethod($module, $method), $props);
        return (is_string($module) ? new $module : $module)->{$method}(...$correctionProps);
    }
}
