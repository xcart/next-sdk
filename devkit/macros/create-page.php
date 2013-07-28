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

// Get arguments
$module    = macro_get_named_argument('module');
$target    = macro_get_named_argument('target');
$interface = macro_get_named_argument('interface');
$menu      = macro_get_named_argument('menu');

// {{{ Check arguments

// --module
if (!$module) {
    macro_error('--module is empty');

} elseif (!preg_match('/^[a-z\d]+\\\\[a-z\d]+$/iSs', $module)) {
    macro_error('--module has wrong module name');
}

list($moduleAuthor, $moduleName) = explode('\\', $module, 2);

// --target
if (!$target) {
    macro_error('--target is empty');
}

// }}}

// {{{ Controller

$targetClass = macro_assemble_class_name(
    'Controller\\' . ('admin' == $interface ? 'Admin' : 'Customer') . '\\' . ucfirst(\Includes\Utils\Converter::convertToCamelCase($target)),
    $moduleAuthor,
    $moduleName
);
$targetControllerPath = macro_convert_class_name_to_path($targetClass);
$targetControllerParent = 'admin' == $interface ? '\\XLite\\Controller\\Admin\\AAdmin' : '\\XLite\\Controller\\Customer\\ACustomer';
$targetShort = macro_get_class_short_name($targetClass);

echo 'Build controller ' . $targetControllerPath . ' ... ';

$targetHumanReadableName = macro_convert_camel_to_human_readable($target);

$string = macro_get_class_repo_header($targetControllerPath)
    . <<<CODE
/**
 * $targetHumanReadableName controller
 */
class $targetShort extends $targetControllerParent
{
}
CODE;

macro_file_put_contents($targetControllerPath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Page widget

$pageClass = macro_assemble_class_name('View\\Page\\' . ('admin' == $interface ? 'Admin' : 'Customer') . '\\' . $targetShort, $moduleAuthor, $moduleName);
$pagePath = macro_convert_class_name_to_path($pageClass);

echo 'Build page widget ' . $pagePath . ' ... ';

$pageTemplate = macro_assemble_tpl_name('page/' . $target . '/body.tpl', $moduleAuthor, $moduleName);

$listTag = 'admin' == $interface ? 'list="admin.center", zone="admin"' : 'list="center"';

$string = macro_get_class_repo_header($pagePath)
    . <<<CODE
/**
 * $targetHumanReadableName page view
 *
 * @ListChild ($listTag)
 */
class $targetShort extends \\XLite\\View\\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), array('$target'));
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return '$pageTemplate';
    }

}
CODE;

macro_file_put_contents($pagePath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Page template

$pageTemplateFull = LC_DIR_SKINS . ('admin' == $interface ? 'admin' : 'default') . '/en/' . $pageTemplate;

echo 'Build page template ' . $pageTemplateFull . ' ... ';

$string = <<<CODE
{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * $targetHumanReadableName page template
 *  
 * @author    Creative Development LLC <info@cdev.ru> 
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *}



CODE;

macro_file_put_contents($pageTemplateFull, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Menu item

if ($menu) {

    $menuClass = macro_assemble_class_name(
        ('admin' == $interface ? 'View\\Menu\\Admin\\TopMenu' : 'View\\Menu\\Customer\\Top'),
        $moduleAuthor,
        $moduleName
    );
    $menuPath = macro_convert_class_name_to_path($menuClass);

    echo 'Build menu item ' . $menuPath . ' ... ';

    $menuShortClass = macro_get_class_short_name($menuClass);

    $menuParent = 'admin' == $interface ? '\\XLiteView\\Menu\\Admin\\TopMenu' : '\\XLite\\View\\Menu\\Customer\\Top';

    $string = macro_get_class_repo_header($menuPath)
    . <<<CODE
/**
 * Top menu widget
 */
class TopMenu extends $menuParent implements \\XLite\\Base\\IDecorator
{
    /**
     * Define items
     *
     * @return array
     */
    protected function defineItems()
    {
        \$list = parent::defineItems();

		if (isset(\$list['$menu'])) {
			\$list['$menu'][static::ITEM_CHILDREN]['$target'] = array(
				static::ITEM_TITLE  => '$targetHumanReadableName',
                static::ITEM_TARGET => '$target',
            );
		}

		return \$list;
    }
}
CODE;

    macro_file_put_contents($menuPath, $string);
    echo 'done' . PHP_EOL;

}

// }}}

die(0);

// {{{ Help

function macro_help()
{
    $script = __FILE__;

    return <<<HELP
Usage: $script --target=target_name --module=Developer\Example [--interface=admin] [--menu=section]

    --target=target_name
        Controller short name (target)

    --module=Developer\Example
        Module full name (author short name + module short name)

    --interface=admin
        Interface code (admin or customer). Defualt - customer

    --menu=section
        Create menu section item. Default - no

Example: .dev/macro/$script --target=example --module=Developer\\Example

HELP;
}

// }}}

