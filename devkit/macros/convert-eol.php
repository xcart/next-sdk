<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Convert Windows / MacOS EOL symbols t oUNIX EOL symbols
 */

define('MACRO_NO_XCN_CORE', true);

require_once __DIR__ . '/core.php';

// get arguments
$path = macro_get_plain_argument(0);

if (!$path) {
    macro_error('Path is empty!');

} elseif (!file_exists($path)) {
    macro_error('Path not found!');
}

$files = array();
if (is_dir($path)) {
    $path = rtrim($path, DIRECTORY_SEPARATOR);
    $dirIterator = new \RecursiveDirectoryIterator($path . DIRECTORY_SEPARATOR);
    $iterator    = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $filePath => $fileObject) {
        if (preg_match('/\.(php|tpl|css|js|yaml)$/Ss', $filePath)) {
            $files[] = $filePath;
        }
    }
    

} else {
    $files[] = $path;
}

foreach ($files as $file) {
    print $file . ' ... ';
    $data = file_get_contents($file);
    $newData = str_replace(
        array("\r\n", "\r"),
        array("\n", "\n"),
        $data
    );

    if ($data != $newData) {
        file_put_contents($file, $newData);
        print 'done' . PHP_EOL;

    } else {
        print 'pass' . PHP_EOL;
    }
}

die(0);

/**
 * Help
 */
function macro_help()
{
    return <<<HELP
Usage: convert-eol.php file_path

Example: ../next-sdk/devkit/macros/convert-eol.php src/classes/XLite
HELP;
}

