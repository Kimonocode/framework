<?php

namespace Infra\Helpers;

class Stringify {

    /**
     * Return thisIsAnExample as this_is_an_example
     * 
     * @param string $string
     * @return string
     */
    public static function camelToSnakeCase(string $string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

}