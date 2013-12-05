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
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 */

/**
 * Create page
 */

require_once __DIR__ . '/core.php';

define('RESULT_CHECK', 'check');
define('RESULT_CHECK_DB', 'check-db');
define('RESULT_CHECK_CODE', 'check-code');

// Get arguments
$result   = macro_get_named_argument('result') ?: RESULT_CHECK;
$language = macro_get_named_argument('language');

// {{{ Check arguments

// --result
if ($result && !in_array($result, array(RESULT_CHECK, RESULT_CHECK_DB, RESULT_CHECK_CODE))) {
    macro_error('--result has wrong value');
}

// --language
if ($language && preg_match('/^[a-z]{2}$/Ss', $language)) {
    macro_error('--language has wrong value');
}

// }}}


switch ($result) {
    case RESULT_CHECK:
        macro_sll_check();
        break;

    case RESULT_CHECK_DB:
        macro_sll_check_db();
        break;

    case RESULT_CHECK_CODE:
        macro_sll_check_code();
        break;

    default:
}

die(0);

// {{{ Result generators

function macro_sll_check()
{
    $labels = macro_sll_find_all();
    $db = macro_sll_get_db_labels();

    $diff = array_diff(array_keys($labels), array_keys($db));
    if ($diff) {
        print 'Exists only in code (templates or classes):' . PHP_EOL;
        foreach ($diff as $name) {
            print "\t" . '\'' . $name . '\'; Usage:' . PHP_EOL
                . "\t\t" . implode(PHP_EOL . "\t\t", $labels[$name]) . PHP_EOL;
            
        }
        print PHP_EOL;
    }

    $diff = array_diff(array_keys($db), array_keys($labels));
    if ($diff) {
        print 'Exists only in DB:' . PHP_EOL;
        foreach ($diff as $name) {
            print "\t" . '\'' . $name . '\'; Has translation to languages: ' . implode(', ', array_keys($db[$name])) . PHP_EOL;
        }
        print PHP_EOL;
    }
}

function macro_sll_check_db()
{
    $labels = macro_sll_find_all();
    $db = macro_sll_get_db_labels();

    $diff = array_diff(array_keys($labels), array_keys($db));
    if ($diff) {
        print 'Exists only in code (templates or classes):' . PHP_EOL;
        foreach ($diff as $name) {
            print "\t" . '\'' . $name . '\'; Usage:' . PHP_EOL
                . "\t\t" . implode(PHP_EOL . "\t\t", $labels[$name]) . PHP_EOL;

        }
        print PHP_EOL;
    }
}

function macro_sll_check_code()
{
    $labels = macro_sll_find_all();
    $db = macro_sll_get_db_labels();

    $diff = array_diff(array_keys($db), array_keys($labels));
    if ($diff) {
        print 'Exists only in DB:' . PHP_EOL;
        foreach ($diff as $name) {
            print "\t" . '\'' . $name . '\'; Has translation to languages: ' . implode(', ', array_keys($db[$name])) . PHP_EOL;
        }
        print PHP_EOL;
    }
}

// }}}

// {{{ Routines

function macro_sll_find_all()
{
    $labels = array();

    // Scan templates
    macro_sll_find_all_by_iterator(
        $labels,
        new \Includes\Utils\FileFilter(LC_DIR_SKINS, '/\.tpl$/Ss', \RecursiveIteratorIterator::CHILD_FIRST),
        '/[\{\^\,]t\(#([^#]+)#(?:\)|,_ARRAY_)/Ss'
    );

    // Scan JS
    macro_sll_find_all_by_iterator(
        $labels,
        new \Includes\Utils\FileFilter(LC_DIR_SKINS, '/\.js$/Ss', \RecursiveIteratorIterator::CHILD_FIRST),
        '/core\.t\(\'([^\']+)\'/Ss'
    );

    // Scan classes
    macro_sll_find_all_by_iterator(
        $labels,
        new \Includes\Utils\FileFilter(LC_DIR_CLASSES, '/\.js$/Ss', \RecursiveIteratorIterator::CHILD_FIRST),
        '/(?:(?:static::|self::|\$this->)t|.XLite.Core.Translation::getInstance\(\)->translate|.XLite.Core.Translation::lbl)(\s*\'([^\']+)\'/Ss'
    );

    return $labels;
}

function macro_sll_find_all_by_iterator(array &$labels, \Includes\Utils\FileFilter $filter, $patterns, $length = null)
{
    if (!isset($length)) {
        $length = strlen(LC_DIR_ROOT);
    }
    if (!is_array($patterns)) {
        $patterns = array($patterns);
    }

    // :KLUDGE: fix for some stupid FSs
    foreach (iterator_to_array($filter->getIterator(), false) as $file) {
        $body = file_get_contents($file->getRealPath());
        $path = substr($file->getRealPath(), $length);

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $body, $matches)) {
                foreach ($matches[1] as $label) {
                    if (!isset($labels[$label])) {
                        $labels[$label] = array($path);

                    } elseif (!in_array($path, $labels[$label])) {
                        $labels[$label][] = $path;
                    }
                }
            }
        }
    }
}

function macro_sll_get_db_labels()
{
    $labels = array();

    $list = \XLite\Core\Database::getRepo('XLite\Model\LanguageLabel')->createPureQueryBuilder()
        ->linkInner('l.translations')
        ->addSelect('translations')
        ->getArrayResult();
    foreach ($list as $cell) {
        $labels[$cell['name']] = array();
        foreach ($cell['translations'] as $translation) {
            $labels[$cell['name']][$translation['code']] = $translation['label'];
        }
    }

    return $labels;
}

// }}}

// {{{ Help

function macro_help()
{
    $script = __FILE__;

    return <<<HELP
Usage: $script --result=<mode> [--language=<language_code>]

    --result=<mode>
        Result mode. Variants:
            check      - check DB and code (default)
            check-db   - check DB
            check-code - check only code

    --language=<language_code>
        Language code

Example: .dev/macro/$script

HELP;
}

// }}}

