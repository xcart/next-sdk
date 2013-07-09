#!/usr/bin/env php
<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * LiteCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@litecommerce.com so we can send you a copy immediately.
 *
 * PHP version 5.3.0
 *
 * @category  LiteCommerce
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 * @see       ____file_see____
 * @since     1.0.18
 */

/**
 * Rebuild classes cache
 */

require_once __DIR__ . '/core.php';

chdir(XCN_MACROS_ROOT);

// {{{ Initialize cache rebuild

print ('Initialize cache rebuild ... ');

\Includes\Decorator\Utils\CacheManager::cleanupCacheIndicators();
\Includes\Utils\FileManager::unlinkRecursive(LC_DIR_LOG);
\Includes\Utils\FileManager::unlinkRecursive(LC_DIR_CACHE_IMAGES);

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
}

print ('done' . PHP_EOL);

// }}}

// {{{ Rebuild cache

print ('Rebuild cache ');

do {
    list($result, $output) = macro_exec('php cart.php');
    $output = implode(PHP_EOL, $output);
    if ($result) {
        print ('failed (cache rebuild failed)' . PHP_EOL);
        var_export($output);
        die(9);
    }
    preg_match('/step \d+ of (\d+)/Ss', $output, $match);
    $limit = $match[1];

    print('.');

} while (!preg_match('/step ' . $limit . ' of ' . $limit . '/Ss', $output));

print (' done' . PHP_EOL);

// }}}

// {{{ Correct directory permissions

print ('Correct permissions ... ');

foreach (array('var', 'images') as $subdir) {
    chmod($subdir, 0777);
    $dirIterator = new RecursiveDirectoryIterator($subdir . DIRECTORY_SEPARATOR);
    $iterator    = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $filePath => $fileObject) {
        if (is_dir($filePath)) {
            chmod($filePath, 0777);

        } else {
            chmod($filePath, 0666);
        }
    }
}

print (' done' . PHP_EOL);

// }}}

die(0);
