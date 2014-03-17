#!/usr/bin/env php
<?php

@date_default_timezone_set(@date_default_timezone_get());
ini_set('memory_limit', '256M');
error_reporting(E_ALL | E_STRICT);

set_include_path(
	'/usr/local/share/pear'
    . PATH_SEPARATOR . '/usr/share/php'
	. PATH_SEPARATOR . get_include_path()
);

define('IS_INTERNAL_PROJECT', true);

$dir = getcwd();
do {
    if (file_exists($dir . DIRECTORY_SEPARATOR . 'top.inc.php')) {
        define('XCN_MACROS_ROOT', $dir);
        break;

    } elseif (file_exists($dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'top.inc.php')) {
        define('XCN_MACROS_ROOT', $dir . DIRECTORY_SEPARATOR . 'src');
        break;

    } else {
        $tmp = realpath($dir . DIRECTORY_SEPARATOR . '..');
        $dir = $tmp == $dir ? null : $tmp;
    }

} while ($dir);

if (!defined('XCN_MACROS_ROOT')) {
    print 'top.inc.php not found!' . PHP_EOL;
    die(1);
}

spl_autoload_register(
	function ($class) {
	    if (strpos($class, 'XLite_') === 0) {
		    $file = __DIR__ . '/sniffs/' . str_replace('_', '/', $class) . '.php';

	        if (file_exists($file)) {
		        require_once $file;
			}
	    }
	}
);

require_once 'PHP/CodeSniffer/CLI.php';

$phpcs = new PHP_CodeSniffer_CLI();
$phpcs->checkRequirements();

$numErrors = $phpcs->process();
exit($numErrors === 0 ? 0 : 1);
