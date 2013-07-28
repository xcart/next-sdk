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
 * Create editable entity list (CRUD)
 */

require_once __DIR__ . '/core.php';

// Get arguments
$entityClass = macro_get_named_argument('entity');
$fields      = macro_get_named_argument('fields');
$module      = macro_get_named_argument('module');
$pagination  = !is_null(macro_get_named_argument('pagination'));

// {{{ Check arguments

// --module
if (!$module) {
    macro_error('--module is empty');

} elseif (!preg_match('/^[a-z\d]+\\\\[a-z\d]+$/iSs', $module)) {
    macro_error('--module has wrong module name');
}

list($moduleAuthor, $moduleName) = explode('\\', $module, 2);

// --entity
if (!$entityClass) {
    macro_error('Entity class (--entity) argument is empty');

} elseif (\Includes\Utils\FileManager::isExists($entityClass)) {
    $entityClassPath = realpath($entityClass);
    $entityClass = str_replace(LC_DS, '\\', substr($entityClassPath, strlen(LC_DIR_CLASSES)));

} elseif (\Includes\Utils\FileManager::isExists(LC_DIR_CLASSES . $entityClass)) {
    $entityClassPath = realpath(LC_DIR_CLASSES . $entityClass);
    $entityClass = str_replace(LC_DS, '\\', $entityClass);

} elseif (\XLite\Core\Operator::isClassExists($entityClass)) {
    $entityClass = ltrim($entityClass, '\\');
    $entityClassPath = LC_DIR_CLASSES . str_replace('\\', LC_DS, $entityClass);

} else {
    macro_error('Entity class (--entity) \'' . $entityClass . '\' not found');
}

if (!is_subclass_of($entityClass, 'XLite\Model\AEntity')) {
    macro_error('Class \'' . $entityClass . '\' is not child of XLite\Model\AEntity');
}

$entityRepoClass = str_replace('\\Model\\', '\\Model\\Repo\\', $entityClass);

preg_match('/\\\Model\\\(.+)$/Ss', $entityClass, $match);
$entityRelativeClass = $match[1];
$entityShortClass = macro_get_class_short_name($entityClass);
$alias = strtolower(substr($entityShortClass, 0, 1));

// --fields
if (!$fields) {
    macro_error('Fields list (--fields) argument is empty');
}

$metaData = \XLite\Core\Database::getEM()->getClassMetadata($entityClass);
$fields = array_map('trim', explode(',', $fields));
$keys = array_keys($metaData->fieldMappings);
if (is_subclass_of($entityClass, 'XLite\Model\Base\I18n')) {
    $metaDataTranslations = \XLite\Core\Database::getEM()->getClassMetadata($metaData->associationMappings['translations']['targetEntity']);
    $keys = array_merge($keys, array_keys($metaDataTranslations->fieldMappings));
}

foreach ($fields as $field) {
    if (!in_array($field, $keys)) {
        macro_error('Field \'' .  $field . '\' can not found');
    }
}

// }}}

// {{{ Define all variables

$itemsListClass = macro_assemble_class_name('View\\ItemsList\\' . $entityRelativeClass . 's', $moduleAuthor, $moduleName);

// }}}

// {{{ Build list

echo 'Build list' . PHP_EOL;

// {{{ Item list widget

$itemsListPath = macro_convert_class_name_to_path($itemsListClass);

echo "\t" . 'items list widget ' . $itemsListPath . ' ... ';

$itemsListClassShort = macro_get_class_short_name($itemsListClass);

$targetHumanReadableName = macro_convert_camel_to_human_readable($itemsListClassShort);

$itemsListSuffix = lcfirst($itemsListClassShort);
$itemsListDir = 'modules/' . $moduleAuthor . '/' . $moduleName . '/' . $itemsListSuffix;
$itemsListCSS = $itemsListDir . '/style.css';
$itemsListJS = $itemsListDir . '/controller.js';
$itemsListJSController = $itemsListClassShort . 'ItemsList';
$itemsListCSSClass = $itemsListSuffix;

$pagerClass = $pagination
    ? '\\XLite\\Module\\' . $moduleAuthor . '\\' . $moduleName . '\\View\\Pager\\' . $itemsListClassShort . '\\Customer'
    : '\\XLite\\View\\Pager\\Infinity';

$string = macro_get_class_repo_header($itemsListPath)
    . <<<CODE
/**
 * $targetHumanReadableName items list
 */
class $itemsListClassShort extends \\XLite\\View\\ItemsList\\AItemsList
{

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        \$list = parent::getCSSFiles();

        \$list[] = '$itemsListCSS';

        return \$list;
    }

    /**
     * Return name of the base widgets list
     *
     * @return string
     */
    protected function getListName()
    {
        return parent::getListName() . '.$itemsListSuffix.customer';
    }

    /**
     * Get widget templates directory
     * NOTE: do not use "\$this" pointer here (see "getBody()" and "get[CSS/JS]Files()")
     *
     * @return string
     */
    protected function getDir()
    {
        return '$itemsListDir';
    }

    /**
     * Return dir which contains the page body template
     *
     * @return string
     */
    protected function getPageBodyDir()
    {
        return 'list';
    }

    /**
     * getJSHandlerClassName
     *
     * @return string
     */
    protected function getJSHandlerClassName()
    {
        return '$itemsListJSController';
    }

    /**
     * Get a list of JavaScript files
     *
     * @return array
     */
    public function getJSFiles()
    {
        \$list = parent::getJSFiles();

        \$list[] = '$itemsListJS';

        return \$list;
    }

    /**
     * Returns a list of CSS classes (separated with a space character) to be attached to the items list
     *
     * @return string
     */
    public function getListCSSClasses()
    {
        return parent::getListCSSClasses() . ' $itemsListCSSClass';
    }

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return '$pagerClass';
    }

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell \$cnd       Search condition
     * @param boolean                \$countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getData(\XLite\Core\CommonCell \$cnd, \$countOnly = false)
    {
        return \XLite\Core\Database::getRepo('$entityClass')->search(\$cnd, \$countOnly);
    }

}
CODE;

macro_file_put_contents($itemsListPath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Pager

$pagerPath = macro_convert_class_name_to_path($pagerClass);
$pagerClassShort = macro_get_class_short_name($pagerClass);

echo "\t" . 'pager class ' . $pagerPath . ' ... ';

$string = macro_get_class_repo_header($pagerPath)
    . <<<CODE
/**
 * $targetHumanReadableName pager
 */
class $pagerClassShort extends \\XLite\\View\\Pager\\Customer\\ACustomer
{
    /**
     * Get items per page default
     *
     * @return integer
     */
    protected function getItemsPerPageDefault()
    {
        return 50;
    }
}
CODE;

macro_file_put_contents($pagerPath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Items list CSS

$itemsListCSSFull = LC_DIR_SKINS . 'default/en/' . $itemsListCSS;
echo "\t" . 'styles file ' . $itemsListCSSFull . ' ... ';

$string = <<<CODE
/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * $targetHumanReadableName list styles
 *  
 * @author    Creative Development LLC <info@cdev.ru> 
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 */

CODE;

macro_file_put_contents($itemsListCSSFull, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Items list JS

$itemsListJSFull = LC_DIR_SKINS . 'default/en/' . $itemsListJS;
echo "\t" . 'js controller file ' . $itemsListJSFull . ' ... ';

$string = <<<CODE
/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * $targetHumanReadableName controller
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 */

function {$itemsListJSController}Controller(base)
{
  {$itemsListJSController}Controller.superclass.constructor.apply(this, arguments);
}

extend({$itemsListJSController}Controller, ListsController);

{$itemsListJSController}Controller.prototype.name = '{$itemsListJSController}Controller';

{$itemsListJSController}Controller.prototype.getListView = function()
{
  return new {$itemsListJSController}View(this.base);
}

function {$itemsListJSController}View(base)
{
  {$itemsListJSController}View.superclass.constructor.apply(this, arguments);
}

extend({$itemsListJSController}View, ListView);

{$itemsListJSController}View.prototype.postprocess = function(isSuccess, initial)
{
  {$itemsListJSController}View.superclass.postprocess.apply(this, arguments);

  if (isSuccess) {
    // Some routines
  }
}

core.autoload({$itemsListJSController}Controller);
CODE;

macro_file_put_contents($itemsListJSFull, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Items list template

$itemsListTplPath = LC_DIR_SKINS . 'default/en/' . $itemsListDir . '/list/body.tpl';

echo "\t" . 'template ' . $itemsListTplPath . ' ... ';

$string = <<<CODE
{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * $targetHumanReadableName main template
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *}

<div class="$itemsListCSSClass-list">

  <ul class="list" IF="getPageData()">
    <li FOREACH="getPageData(),model" class="$itemsListCSSClass-cell">
      <list name="row" type="inherited" model="{model}" />
    </li>
  </ul>

</div>
CODE;

macro_file_put_contents($itemsListTplPath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Items list parts

$i = 0;
foreach ($fields as $field) {

    $i += 100;
    $itemsListPartTplPath = LC_DIR_SKINS . 'default/en/' . $itemsListDir . '/list/parts/cell.' . $field . '.tpl';

    echo "\t" . 'field template ' . $itemsListTplPath . ' ... ';

    $string = <<<CODE
{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * $targetHumanReadableName :: $field cell
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *
 * @ListChild (list="itemsList.$itemsListSuffix.customer.row", weight="$i")
 *}

{model.$field}

CODE;

    macro_file_put_contents($itemsListPartTplPath, $string);
    echo 'done' . PHP_EOL;
}

// }}}

// {{{ Repository class

$entityRepoPath = macro_convert_class_name_to_path($entityRepoClass);

echo 'Build entity repository' . PHP_EOL
	. "\t" .'class ' . $entityRepoPath . ' ... ';

if (!\Includes\Utils\FileManager::isExists($entityRepoPath)) {

    // Create repository

    $entityRepoClassShort = macro_get_class_short_name($entityRepoClass);

    $entityRepoParentClass = is_subclass_of($entityClass, 'XLite\Model\Base\I18n')
        ? '\XLite\Model\Repo\Base\I18n'
        : '\XLite\Model\Repo\ARepo';

    $string = macro_get_class_repo_header($entityRepoPath)
    . <<<CODE
/**
 * $targetHumanReadableName repository
 */
class $entityRepoClassShort extends $entityRepoParentClass
{
}
CODE;

    macro_file_put_contents($entityRepoPath, $string);
    echo 'created ... ';
}

// Add search functional
$string = file_get_contents($entityRepoPath);

if (!preg_match('/function +search *\(/Ss', $string)) {

	$string = preg_replace('/}[[^}]*$/Ss', '', $string);
	$string = preg_replace('/\/\/ \{\{\{ Search.+\/\/ \}\}\}/Ss', '', $string);
	$string = trim($string) . PHP_EOL;

	$string .= <<<CODE

    // {{{ Search

CODE;

	$consts = '';
	$handlingSearchParams = '';
	$prepareMethods = '';


    if ($pagination) {
		$consts .= <<<CODE
    const SEARCH_LIMIT = 'limit';

CODE;
	    $handlingSearchParams .= <<<CODE
            static::SEARCH_LIMIT,

CODE;
	    $prepareMethods .= <<<CODE
    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder \$queryBuilder Query builder to prepare
     * @param array                      \$value        Condition data
     *
     * @return void
     */
    protected function prepareCndLimit(\Doctrine\ORM\QueryBuilder \$queryBuilder, array \$value)
    {
        call_user_func_array(array(\$this, 'assignFrame'), array_merge(array(\$queryBuilder), \$value));
    }

CODE;
	}

	$handlingSearchParams = rtrim($handlingSearchParams);

	$string .= <<<CODE

$consts
    /**
     * Common search
     *
     * @param \XLite\Core\CommonCell \$cnd       Search condition
     * @param boolean                \$countOnly Return items list or only its size OPTIONAL
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function search(\XLite\Core\CommonCell \$cnd, \$countOnly = false)
    {
        \$queryBuilder = \$this->createQueryBuilder('$alias');
        \$this->currentSearchCnd = \$cnd;

        foreach (\$this->currentSearchCnd as \$key => \$value) {
            \$this->callSearchConditionHandler(\$value, \$key, \$queryBuilder, \$countOnly);
        }

        return \$countOnly
            ? \$this->searchCount(\$queryBuilder)
            : \$this->searchResult(\$queryBuilder);
    }

    /**
     * Search count only routine.
     *
     * @param \Doctrine\ORM\QueryBuilder \$qb Query builder routine
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function searchCount(\Doctrine\ORM\QueryBuilder \$qb)
    {
        \$qb->select('COUNT(DISTINCT $alias.id)');

        return intval(\$qb->getSingleScalarResult());
    }

    /**
     * Search result routine.
     *
     * @param \Doctrine\ORM\QueryBuilder \$qb Query builder routine
     *
     * @return \Doctrine\ORM\PersistentCollection|integer
     */
    public function searchResult(\Doctrine\ORM\QueryBuilder \$qb)
    {
        return \$qb->getResult();
    }

    /**
     * Call corresponded method to handle a search condition
     *
     * @param mixed                      \$value        Condition data
     * @param string                     \$key          Condition name
     * @param \Doctrine\ORM\QueryBuilder \$queryBuilder Query builder to prepare
     * @param boolean                    \$countOnly    Count only flag
     *
     * @return void
     */
    protected function callSearchConditionHandler(\$value, \$key, \Doctrine\ORM\QueryBuilder \$queryBuilder, \$countOnly)
    {
        if (\$this->isSearchParamHasHandler(\$key)) {
            \$this->{'prepareCnd' . ucfirst(\$key)}(\$queryBuilder, \$value, \$countOnly);
        }
    }

    /**
     * Check if param can be used for search
     *
     * @param string \$param Name of param to check
     *
     * @return boolean
     */
    protected function isSearchParamHasHandler(\$param)
    {
        return in_array(\$param, \$this->getHandlingSearchParams());
    }

    /**
     * Return list of handling search params
     *
     * @return array
     */
    protected function getHandlingSearchParams()
    {
        return array(
$handlingSearchParams
        );
    }

$prepareMethods
    // }}}

	}
CODE;

	macro_file_put_contents($entityRepoPath, $string);
	echo 'done' . PHP_EOL;

} else {
	echo 'ignored (method \'search\' already exists)' . PHP_EOL;
}

// }}}

// }}}

die(0);

// {{{ Help

function macro_help()
{
    $script = __FILE__;

    return <<<HELP
Usage: $script --entity=XLite\Model\Entity --fields=fld1,fld2,...,fldn [--pagintation] [--module=Developer\Example]

    --entity=class_name
        Entity class (XLite\Model\Product) or full path to class repository file (src/class/XLite/Model/Product.php) or relative path to class repository file (XLite/Model/Product.php)

    --fields=fld1,fld2,...,fldn
        Fields / columns list

    --pagintation
        Use pagination. Default - no.

    --module=Developer\Example
        Module full name (author short name + module short name)

Example: .dev/macro/$script --entity=XLite\\Module\\Developer\\Example\\Model\\Message --fields=subject --pagintation --sort=subject --module=Developer\\Example

HELP;
}

// }}}

