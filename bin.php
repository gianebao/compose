#!/usr/bin/env php
<?php
/**
 * Requires Box [http://box-project.org] for packaging.
 */

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__);

require ROOT . DS . 'lib' . DS . 'extra.php';
require ROOT . DS . 'lib' . DS . 'command.php';

$options = $_SERVER['argv'];

// increment to ommit the bin file itself.
array_shift($options);

Command::factory(array_shift($options), $options);