<?php
// vim: set ts=4 sw=4 sts=4 et:

define('XC5_TEST', true);

define('XC5_TEST_ROOT', __DIR__);

// Detect X-Cart 5 root directory
$__dir = getcwd();
do {
    if (file_exists($__dir . DIRECTORY_SEPARATOR . 'top.inc.php')) {
        define('XC5_APP_ROOT', $__dir);
        break;

    } elseif (file_exists($__dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'top.inc.php')) {
        define('XC5_APP_ROOT', $__dir . DIRECTORY_SEPARATOR . 'src');
        break;

    } else {
        $tmp = realpath($__dir . DIRECTORY_SEPARATOR . '..');
        $__dir = $tmp == $__dir ? null : $tmp;
    }

} while ($__dir);

if (!defined('XC5_APP_ROOT')) {
	print ('Can not detect X-Cart 5 root directory!' . PHP_EOL);
	die(1);
}

require_once XC5_APP_ROOT . DIRECTORY_SEPARATOR . 'top.inc.php';

// Autoloading
spl_autoload_register(
    function ($class) {
        $class = ltrim($class, '\\');
        list($prefix) = explode('\\', $class, 2);
        if (in_array($prefix, array('XLiteUnit'))) {
            require_once (XC5_TEST_ROOT . DIRECTORY_SEPARATOR . 'unit' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');

        } elseif (in_array($prefix, array('XLiteIntegration'))) {
            require_once (XC5_TEST_ROOT . DIRECTORY_SEPARATOR . 'integration' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');

        } elseif (in_array($prefix, array('XLiteWeb'))) {
            require_once (XC5_TEST_ROOT . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');

        } elseif (in_array($prefix, array('XLiteTest'))) {
            require_once (XC5_TEST_ROOT . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
		}
    }
);

// Pre-initialzie
new \XLiteTest\TextUI\ResultPrinter;

