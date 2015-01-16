<?php

class Command
{
    const _FN_PREFIX = 'action_';
    const _TEXT_NOT_REQUIRED = '--NOT REQUIRED--';
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
    
    private static function _put_config($config)
    {
        file_put_contents(Command::$_config, json_encode($config, JSON_PRETTY_PRINT));
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
            $result = $callback($config['repositories'][$i], $config, $i);
            
            if (false === $result)
            {
                break;
            }
        }
        
        return $config;
    }
    
    /**
     * Returns the exact item in the resources
     */
    private static function _find($filter, $fn)
    {
        $result_count = 0;
        
        $results = Command::_each_config(function (& $item, & $config, $i) use ($filter, $fn, & $result_count)
        {
            if ($filter === $item['package']['name'])
            {
                $result_count ++;
                $fn($item, $config, $i);
                
                return false;
            }
        });
        
        return !empty($result_count) ? $results: false;
    }
    
    public static function action_help()
    {
        require ROOT . DS . 'readme.md';
        echo "\n";
    }
    
    private static function _increment($val, $steps = 1)
    {
        $val = $steps + (int) $val;
        
        return 0 > $val ? 0: $val;
    }
    
    private static function _bump_config($filter, $level = '--fix', $steps = 1)
    {
        $config = Command::_find($filter, function (& $item) use ($level, $steps) {
            $version = explode('.', $item['package']['version']);
            
            switch ($level)
            {
                case '--major':
                    $version[0] = Command::_increment($version[0], $steps);
                    $version[1] = $steps = 0;
                    
                case '--minor':
                    $version[1] = Command::_increment($version[1], $steps);
                    $version[2] = $steps = 0;
                
                case '--fix':
                    $version[2] = Command::_increment($version[2], $steps);
                    break;
                
                default:
                    Extra::fatal('Unable to interpret version level. Accepts --major, --minor or --fix(default).');
            }
            
            $is_update_branch = $item['package']['version'] == $item['package']['source']['reference'];
            Extra::msg('Updated ' . Extra::yellow("[ Package: :package ]\n")
                . Extra::green('Version') . " : :version_old -> :version_new\n"
                . Extra::green('Source.Reference') . " : :branch_old -> :branch_new", array(
                    ':package'         => $item['package']['name'],
                    ':version_old'     => $item['package']['version'],
                    ':version_new'     => $item['package']['version'] = implode('.', $version),
                    ':branch_old'      => $item['package']['source']['reference'],
                    ':branch_new'      => $item['package']['source']['reference']
                        = $is_update_branch ? $item['package']['version']: $item['package']['source']['reference']
            ));
            
            if (!$is_update_branch)
            {
                Extra::msg("\n" . Extra::red('[Warning!]') . ' The source reference was not updated'
                    . ' since a different branch name was used. Make sure to ' . Extra::yellow('`git tag`')
                    . ' the final version and use `set-branch` to the proper version number.');
            }
        });
        
        if (false === $config)
        {
            Extra::fatal(
                'Cannot find package ":name".',
                array(':name' => $filter)
            );
        }
        
        Command::_put_config($config);
    }
    
    public static function action_up($filter, $level = '--fix')
    {
        Command::_bump_config($filter, $level);
    }
    
    public static function action_down($filter, $level = '--fix')
    {
        Command::_bump_config($filter, $level, -1);
    }
    
    public static function action_install($name, $repo, $branch)
    {
        $config = Command::_get_config();
        
        $find = Command::_find($name, function (& $item) {});
        
        if (false !== $find)
        {
            Extra::fatal(
                'Package ":name" already exists.',
                array(':name' => $name)
            );
        }
        
        if (empty($config['repositories']))
        {
            $config['repositories'] = array();
        }
        
        $version = preg_match('/^\d+\.\d+\.\d+$/', $branch) ? $branch: '0.0.1';
        
        array_push($config['repositories'], array(
            'type' => 'package',
            'package' => array(
                'name'    => $name,
                'version' => $version,
                'autoload' => array(
                    'psr-4' => array ('' => '')
                ),
                'source' => array(
                    'type' => 'git',
                    'url' => $repo,
                    'reference' => $branch
                )
            )
        ));
        
        if (empty($config['repositories']))
        {
            $config['require'] = array();
        }
        
        $config['require'][$name] = $version;
        Command::_put_config($config);
    }
    
    public static function action_remove($name)
    {
        $config = Command::_find($name, function (& $item, & $config, $i) {
            unset($config['repositories'][$i]);
            $config['repositories'] = array_merge($config['repositories']);
            return false;
        });
        
        if (false === $config)
        {
            Extra::fatal(
                'Cannot find package ":name".',
                array(':name' => $name)
            );
        }
        
        if (isset($config['require'][$name]))
        {
            unset($config['require'][$name]);
        }
        
        Command::_put_config($config);
    }
    
    public static function action_set_branch($filter, $new_branch = null)
    {
        $config = Command::_find($filter, function (& $item) use ($new_branch) {
            
            if (empty($new_branch))
            {
                $new_branch = $item['package']['version'];
            }
            
            Extra::msg('Updated ' . Extra::yellow("[ Package: :package ]\n")
                . Extra::green('Source.Reference') . " : :branch_old -> :branch_new", array(
                    ':package'         => $item['package']['name'],
                    ':branch_old'      => $item['package']['source']['reference'],
                    ':branch_new'      => $item['package']['source']['reference'] = $new_branch
            ));
        });
        
        if (false === $config)
        {
            Extra::fatal(
                'Cannot find package ":name".',
                array(':name' => $filter)
            );
        }
        
        Command::_put_config($config);
    }
    
    public static function action_set_require($name, $require)
    {
        $config = Command::_get_config();
        
        $find = Command::_find($name, function (& $item) {});
        
        if (false === $find)
        {
            Extra::fatal(
                'Cannot find package ":name".',
                array(':name' => $name)
            );
        }
        
        if (empty($config['repositories']))
        {
            $config['require'] = array();
        }
        
        Extra::msg('Updated ' . Extra::yellow("[ Package: :package ]\n")
            . Extra::green('Require') . " : :require_old -> :require_new", array(
                ':package'         => $name,
                ':require_old'      => empty($config['require'][$name])
                    ? Extra::red(Command::_TEXT_NOT_REQUIRED)
                    : $config['require'][$name],
                ':require_new'      => $config['require'][$name] = $require
        ));
        
        Command::_put_config($config);
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
                        : Extra::red(Command::_TEXT_NOT_REQUIRED)
                ));
            }
        });
        
        if (empty($result_count))
        {
            Extra::msg("No results found using filter \":filter\". Display options available are [ --full, --simple ]", array(':filter' => $filter));
        }
    }
}