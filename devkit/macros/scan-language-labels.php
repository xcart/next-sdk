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

define('RESULT_CHECK_DB', 'check-db');
define('RESULT_CHECK_CODE', 'check-code');
define('RESULT_CHECK_PARTIALLY', 'check-partially');

define('OUTPUT_HUMAN_READABLE', 'hunam-readable');
define('OUTPUT_YAML', 'yaml');
define('OUTPUT_CSV', 'csv');

// Get arguments
$result   = macro_get_named_argument('result') ?: RESULT_CHECK_DB;
$language = macro_get_named_argument('language');
$output   = macro_get_named_argument('output') ?: OUTPUT_HUMAN_READABLE;
$module   = macro_get_named_argument('module');

// {{{ Check arguments

// --result
if (!in_array($result, array(RESULT_CHECK_DB, RESULT_CHECK_CODE, RESULT_CHECK_PARTIALLY))) {
    macro_error('--result has wrong value');
}

// --language
if ($language && !preg_match('/^[a-z]{2}$/Ss', $language)) {
    macro_error('--language has wrong value');

} elseif ($language && !macro_sll_get_db_languages()) {
    macro_error('--language has langauge code is not in the database');
}

// --output
if (!in_array($output, array(OUTPUT_HUMAN_READABLE, OUTPUT_YAML, OUTPUT_CSV))) {
    macro_error('--output has wrong value');
}

// }}}

switch ($result) {
    case RESULT_CHECK_DB:
        macro_sll_check_db();
        break;

    case RESULT_CHECK_CODE:
        macro_sll_check_code();
        break;

    case RESULT_CHECK_PARTIALLY:
        macro_sll_check_partially();
        break;

    default:
}

die(0);

// {{{ Result generators

function macro_sll_check_db()
{
    $labels = macro_sll_find_all();
    $db = macro_sll_get_db_labels();

    macro_sll_compare_by_code($labels, $db);
}

function macro_sll_check_code()
{
    $labels = macro_sll_find_all();
    $db = macro_sll_get_db_labels();

    macro_sll_compare_by_db($labels, $db);
}

function macro_sll_check_partially()
{
    $labels = macro_sll_find_all();
    $db = macro_sll_get_db_labels();

    macro_sll_compare_partially($labels, $db);
}

// }}}

// {{{ Processors

function macro_sll_compare_by_code(array $labels, array $db)
{
    $diff = array_diff(array_keys($labels), array_keys($db));

    if ($diff && $GLOBALS['module']) {
        $module = str_replace('\\', DIRECTORY_SEPARATOR, $GLOBALS['module']);

        foreach ($diff as $k => $name) {
            $paths = $labels[$name];
            foreach ($paths as $i => $path) {
                if ('none' == $GLOBALS['module']) {
                    if (preg_match('/skins.[a-z]+.en.modules|XLite.Module/Ss', $path)) {
                        unset($paths[$i]);
                    }

               } elseif ('only' == $GLOBALS['module']) {
                    if (!preg_match('/skins.[a-z]+.en.modules|XLite.Module/Ss', $path)) {
                        unset($paths[$i]);
                    }

                } elseif (false === strpos($path, $module)) {
                    unset($paths[$i]);
                }
            }

            if (!$paths) {
                unset($diff[$k]);
            }
        }

        $diff = array_values($diff);
    }

    if ($diff) {
        if (OUTPUT_HUMAN_READABLE == $GLOBALS['output']) {

            print 'Exists only in code (templates or classes):' . PHP_EOL;
            foreach ($diff as $name) {
                print "\t" . '\'' . $name . '\'; Usage:' . PHP_EOL
                    . "\t\t" . implode(PHP_EOL . "\t\t", $labels[$name]) . PHP_EOL;

            }
            print PHP_EOL;

        } elseif (OUTPUT_YAML == $GLOBALS['output']) {

            $data = array(
                'XLite\\Model\\LanguageLabel' => array(),
            );
            foreach ($diff as $name) {
                $data['XLite\\Model\\LanguageLabel'][] = array(
                    'name' => $name,
                    'translations' => array(
                        array(
                            'code'  => 'en',
                            'label' => $name,
                        ),
                    ),
                );
            }

            print \Symfony\Component\Yaml\Yaml::dump($data) . PHP_EOL;

        } elseif (OUTPUT_CSV == $GLOBALS['output']) {

            $data = array();
            foreach ($diff as $name) {
                $data[] = array($name, $name);
            }

            macro_print_csv($data);
            print PHP_EOL;
        }
    }
}

function macro_sll_compare_by_db(array $labels, array $db)
{
    $diff = array_diff(array_keys($db), array_keys($labels));
    if ($diff) {
        if (OUTPUT_HUMAN_READABLE == $GLOBALS['output']) {
            print 'Exists only in DB:' . PHP_EOL;
            foreach ($diff as $name) {
                print "\t" . '\'' . $name . '\'; Has translation to languages: ' . implode(', ', array_keys($db[$name])) . PHP_EOL;
            }
            print PHP_EOL;

        } elseif (OUTPUT_YAML == $GLOBALS['output']) {

            $data = array(
                'XLite\\Model\\LanguageLabel' => array(),
            );
            foreach ($diff as $name) {
                $data['XLite\\Model\\LanguageLabel'][] = array(
                    'name' => $name,
                );
            }

            print \Symfony\Component\Yaml\Yaml::dump($data) . PHP_EOL;

        } elseif (OUTPUT_CSV == $GLOBALS['output']) {

            $data = array();
            foreach ($diff as $name) {
                $data[] = array($name);
            }

            macro_print_csv($data);
            print PHP_EOL;
        }
    }
}

function macro_sll_compare_partially(array $labels, array $db)
{
    $codes = macro_sll_get_db_languages();

    $diff = array();
    foreach ($db as $label => $translations) {
        $tmp = array_diff($codes, array_keys($translations));
        if ($tmp) {
            $diff[$label] = $tmp;
        }
    }

    if ($diff) {
        if (OUTPUT_HUMAN_READABLE == $GLOBALS['output']) {
            print 'Partyally translations:' . PHP_EOL;
            foreach ($diff as $name => $list) {
                print "\t" . '\'' . $name . '\'; Mission translations: ' . implode(', ', $list) . PHP_EOL;

            }
            print PHP_EOL;

        } elseif (OUTPUT_YAML == $GLOBALS['output']) {

            $codes = array();
            foreach ($diff as $name => $list) {
                $codes += $list;
            }

            foreach ($codes as $code) {
                if (1 < count($codes)) {
                    print '# Language code: ' . $code . PHP_EOL;
                }
                $data = array(
                    'XLite\\Model\\LanguageLabel' => array(),
                );
                foreach ($diff as $name => $list) {
                    if (in_array($code, $list)) {
                        $label = $name;
                        if ($code != 'en' && !empty($db[$name]['en'])) {
                            $label = $db[$name]['en'];
                        }
                        $data['XLite\\Model\\LanguageLabel'][] = array(
                            'name' => $name,
                            'translations' => array(
                                array(
                                    'code'  => $code,
                                    'label' => $label,
                                ),
                            ),
                        );
                    }
                }

                print \Symfony\Component\Yaml\Yaml::dump($data) . PHP_EOL;
            }

        } elseif (OUTPUT_CSV == $GLOBALS['output']) {

            $data = array();
            $codes = array();
            foreach ($diff as $name => $list) {
                $codes += $list;
            }

            foreach ($codes as $code) {
                if (1 < count($codes)) {
                    $data[] = array('Language code: ' . $code);
                }
                foreach ($diff as $name => $list) {
                    if (in_array($code, $list)) {
                        $label = $name;
                        if ($code != 'en' && !empty($db[$name]['en'])) {
                            $label = $db[$name]['en'];
                        }
                        $data[] = array($name, $label);
                    }
                }
            }

            macro_print_csv($data);
            print PHP_EOL;
        }
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
        new \Includes\Utils\FileFilter(LC_DIR_CLASSES, '/\.php$/Ss', \RecursiveIteratorIterator::CHILD_FIRST),
        '/(?:(?:static::|self::|\$this->)t|.XLite.Core.Translation::getInstance\(\)->translate|.XLite.Core.Translation::lbl|.XLite.Core.TopMessage::add(?:Info|Error|Warning))\(\s*\'(.+)\'(?:,|\s*\))/USs'
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

                    // Preprocess
                    $label = preg_replace('/\'\s+\.\s+\'/Ss', '', $label);

                    // Store
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

function macro_sll_get_db_languages()
{
    $codes = array();

    $list = \XLite\Core\Database::getRepo('XLite\Model\LanguageLabelTranslation')->createPureQueryBuilder()
        ->groupBy('l.code')
        ->getArrayResult();
    foreach ($list as $cell) {
        $codes[] = $cell['code'];
    }

    if ($GLOBALS['language']) {
        $codes = array_intersect($codes, array($GLOBALS['language']));
    }

    return $codes;
}

// }}}

// {{{ Help

function macro_help()
{
    $script = __FILE__;

    return <<<HELP
Usage: $script [--result=<mode>] [--language=<language_code>] [--output=<output_mode>]

    --result=<mode>
        Result mode. Variants:
            check-db        - check DB (default)
            check-code      - check only code
            check-partially - check DB (partyally)

    --language=<language_code>
        Language code

    --output=<output_mode>
        Output mode. Variants:
            human-readable - human redable (default)
            yaml           - as YAML
            csv            - as CSV

Example: .dev/macro/$script

HELP;
}

// }}}

