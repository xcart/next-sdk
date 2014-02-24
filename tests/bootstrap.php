<?php
// vim: set ts=4 sw=4 sts=4 et:

define('XC5_TEST', true);

define('XC5_TEST_ROOT', __DIR__);

require_once XC5_TEST_ROOT . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

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

// Pre-initialzie
new \XLiteTest\TextUI\ResultPrinter;

