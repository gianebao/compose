<?php

class Extra
{
    public static function red($string)
    {
        return "\033[0;31m" . $string . "\033[0m";
    }
    
    public static function green($string)
    {
        return "\033[0;32m" . $string . "\033[0m";
    }
    
    public static function yellow($string)
    {
        return "\033[1;33m" . $string . "\033[0m";
    }
    
    public static function msg($string, array $values = array())
    {
        echo strtr($string, $values) . "\n";
    }
    
    public static function fatal($message, array $values = array())
    {
        exit("[Fatal!] " . strtr($message, $values) . "\n");
    }
}