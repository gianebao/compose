<?php

class Command
{
    const _FN_PREFIX = 'action_';
    private static $_config = 'composer.json';
    
    public static function factory($execute, $params)
    {
        $execute = strtolower($execute);
        
        if (0 === strpos($execute, '_') || !method_exists('Command', Command::_to_method($execute)))
        {
            Extra::fatal(
                'Command `:method` does not exist. Use `help` to display options.',
                array(':method' => $execute)
            );
        }
        
        call_user_func_array(array('Command', Command::_to_method($execute)), $params);
    }
    
    private static function _to_method($execute)
    {
        return Command::_FN_PREFIX . trim(str_replace('-', '_', $execute), '_');
    }
    
    private static function _get_config()
    {
        $config = Command::$_config;
        
        if (!file_exists($config))
        {
            Extra::fatal(
                '`:file` file cannot be found.',
                array(':file' => $config)
            );
        }
        
        $config = json_decode(file_get_contents($config), true);
        
        if (empty($config))
        {
            Extra::fatal(
                '`:file` is not initialized or is an invalid json.'
                . ' Use `composer.phar [ init | validate ]` to initialize or validate the config file.',
                array(':file' => $config)
            );
        }
        
        return $config;
    }
    
    private static function _each_config($callback)
    {
        $config = Command::_get_config();
        
        if (empty($config['repositories']))
        {
            return false;
        }
        
        for ($i = 0, $count = count($config['repositories']); $i < $count; $i ++)
        {
            $result = $callback($config['repositories'][$i], $config);
        }
    }
    
    private static function _put_config($config)
    {
        
    }
    
    public static function action_help()
    {
        require ROOT . DS . 'readme.md';
        echo "\n";
    }
    
    public static function action_up()
    {
        
    }
    
    public static function action_down()
    {
        
    }
    
    public static function action_install()
    {
        
    }
    
    public static function action_set_branch()
    {
        
    }
    
    public static function action_remove()
    {
        
    }
    
    public static function action_list($mode = '--simple', $filter = null)
    {
        switch ($mode)
        {
            case '--full':
                $msg = Extra::yellow("[ Package: :package ]\n")
                    . Extra::green('Version') . " : :version\n"
                    . Extra::green('Require') . " : :require\n"
                    . Extra::green('Source.URL') . " : :repo\n"
                    . Extra::green('Source.Reference') . " : :branch\n";
                    
                break;
            
            default:
                $filter = $mode;
            
            case '--simple':
                $msg = '[' . Extra::yellow(":package") . '] ' . Extra::green(':version');
        }
        
        $result_count = 0;
        
        Command::_each_config(function ($item, $config) use ($msg, $filter, & $result_count)
        {
            $item = $item['package'];
            
            if (empty($filter) || false !== strpos($item['name'], $filter))
            {
                $result_count ++;
                Extra::msg($msg, array(
                    ':package' => $item['name'],
                    ':version' => $item['version'],
                    ':repo'    => $item['source']['url'],
                    ':branch'  => $item['source']['reference'],
                    ':require' => !empty($config['require'][$item['name']])
                        ? $config['require'][$item['name']]
                        : Extra::red('--NOT REQUIRED--')
                ));
            }
        });
        
        if (empty($result_count))
        {
            Extra::msg("No results found using filter \":filter\". Display options available are [ --full, --simple ]", array(':filter' => $filter));
        }
    }
}