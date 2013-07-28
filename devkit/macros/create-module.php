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
 * Create module manifest class
 */

require_once __DIR__ . '/core.php';

// Get arguments
$module      = macro_get_named_argument('module');
$hasSettings = !is_null(macro_get_named_argument('hasSettings'));
$version     = macro_get_named_argument('version');

// {{{ Check arguments

// --module
if (!$module) {
    macro_error('--module is empty');

} elseif (!preg_match('/^[a-z\d]+\\\\[a-z\d]+$/iSs', $module)) {
    macro_error('--module has wrong module name');
}

list($moduleAuthor, $moduleName) = explode('\\', $module, 2);

// }}}

// {{{ Build manifest

$mainClass = macro_assemble_class_name('Main', $moduleAuthor, $moduleName);
$mainPath = macro_convert_class_name_to_path($mainClass);

echo 'Build manifest class' . $mainPath . ' ... ';

$mainClassShort = macro_get_class_short_name($mainClass);

$targetHumanReadableName = macro_convert_camel_to_human_readable($mainClassShort);
$author = macro_convert_camel_to_human_readable($moduleAuthor);
$name = macro_convert_camel_to_human_readable($moduleName);

$version = $version ?: preg_replace('/^(\d+\.\d+)\.\d+.*/Ss', '$1', \XLite\Core\Config::getInstance()->Version->version);

$string = macro_get_class_repo_header($mainPath)
    . <<<CODE
/**
 * $targetHumanReadableName module
 */
abstract class $mainClassShort extends \\XLite\\Module\\AModule
{
    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return '$author';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return '$name';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return '';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '$version';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '0';
    }

CODE;

if ($hasSettings) {
	$string .= <<<CODE

    /**
     * Determines if we need to show settings form link
     *
     * @return boolean
     */
    public static function showSettingsForm()
    {
        return true;
    }

CODE;
}

$string .= <<<CODE

}
CODE;

macro_file_put_contents($mainPath, $string);
echo 'done' . PHP_EOL;

// }}}

die(0);

// {{{ Help

function macro_help()
{
    $script = __FILE__;

    return <<<HELP
Usage: $script --module=Developer\Example [--hasSettings] [--version=1.2]

    --module=Developer\Example
        Module full name (author short name + module short name)

    --hasSettings
        Module has settings poage or not. Default - no.

    --version=1.2
        Module major version. Default - current

Example: .dev/macro/$script --module=Developer\Example

HELP;
}

// }}}

