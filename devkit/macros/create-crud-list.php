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

$editableFields = macro_get_named_argument('edit');
$target         = macro_get_named_argument('target');
$removable      = !is_null(macro_get_named_argument('removable'));
$switchable     = !is_null(macro_get_named_argument('switchable'));
$sortable       = !is_null(macro_get_named_argument('sortable'));
$searchFields   = macro_get_named_argument('search');
$headSearch     = !is_null(macro_get_named_argument('headSearch'));
$pagination     = !is_null(macro_get_named_argument('pagination'));
$sortFields     = macro_get_named_argument('sort');
$createInline   = !is_null(macro_get_named_argument('createInline'));
$menu           = macro_get_named_argument('menu');

// {{{ Check arguments

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

$moduleAuthor = null;
$moduleName = null;
if (preg_match('/XLite\\\Module\\\([a-z0-9]+)\\\([a-z0-9]+)\\\Model/iSs', $entityClass, $match)) {
    $moduleAuthor = $match[1];
    $moduleName = $match[2];
}

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

// --edit
$editableFields = $editableFields ? array_map('trim', explode(',', $editableFields)) : array();
$editableFields = $createInline ? $fields : $editableFields;
foreach ($editableFields as $field) {
    if (!in_array($field, $fields)) {
        macro_error('Field \'' .  $field . '\' marked as editable and it will not displayed as column');
    }
}

// --target
if (!$target) {
    $target = str_replace('\\', '_', $entityRelativeClass);
    $target .= 's' == substr($target, -1) ? 'es' : 's';
}

if (false !== strpos($target, ',')) {
    list($target, $targetOne) = explode(',', $target, 2);

} else {
    $targetOne = substr($target, 0, -1);
}

$targetSkinDir = $target = strtolower(preg_replace('/([a-z0-9])([A-Z])([a-z0-9])/Ss', '$1_$2$3', $target));
$targetShort = ucfirst(\Includes\Utils\Converter::convertToCamelCase($target));
$targetClass = macro_assemble_class_name('Controller\Admin\\' . $targetShort, $moduleAuthor, $moduleName);
$targetControllerPath = macro_convert_class_name_to_path($targetClass);
$list = array_merge(
    (array)@glob(LC_DIR_CLASSES . 'XLite/Controller/Admin/' . $targetShort . '.php'),
    (array)@glob(LC_DIR_CLASSES . 'XLite/Module/*/*/Controller/Admin/' . $targetShort . '.php')
);

$list = array_map('trim', $list);
$list = array_filter(
    $list,
    function($var) use ($targetControllerPath) {
        return !empty($var) && realpath($var) != $targetControllerPath;
    }
);

if ($list) {
    macro_error('Controller class \'' . $targetShort . '\' already exists (' . implode('; ', $list) . ')');
}

$targetOneSkinDir = $targetOne = strtolower(preg_replace('/([a-z0-9])([A-Z])([a-z0-9])/Ss', '$1_$2$3', $targetOne));
$targetOneShort = ucfirst(\Includes\Utils\Converter::convertToCamelCase($targetOne));
$targetOneClass = macro_assemble_class_name('Controller\Admin\\' . $targetOneShort, $moduleAuthor, $moduleName);
$targetOneControllerPath = macro_convert_class_name_to_path($targetOneClass);
$list = array_merge(
    (array)@glob(LC_DIR_CLASSES . 'XLite/Controller/Admin/' . $targetOneShort . '.php'),
    (array)@glob(LC_DIR_CLASSES . 'XLite/Module/*/*/Controller/Admin/' . $targetOneShort . '.php')
);

$list = array_map('trim', $list);
$list = array_filter(
    $list,
    function($var) use ($targetOneControllerPath) {
        return !empty($var) && realpath($var) != $targetOneControllerPath;
    }
);

if ($list) {
    macro_error('Controller class \'' . $targetOneShort . '\' already exists (' . implode('; ', $list) . ')');
}

// --search
$searchFields = $searchFields ? array_map('trim', explode(',', $searchFields)) : array();
foreach ($searchFields as $field) {
    if (!in_array($field, $keys)) {
        macro_error('Field \'' .  $field . '\' marked as searchable and it not ofund');
    }
}

// --sort
$sortFields = $sortFields ? array_map('trim', explode(',', $sortFields)) : array();
foreach ($sortFields as $field) {
    if (!in_array($field, $keys)) {
        macro_error('Field \'' .  $field . '\' marked as sortable and it not ofund');
    }
}

// }}}

// {{{ Define all variables

$unitedSearchFields = $searchFields;
$headSearchFields = $headSearch ? array_intersect($fields, $searchFields) : array();
$searchFields = $headSearch ? array_diff($searchFields, $headSearchFields) : $searchFields;

$type2fields = array(
    'text'     => 'XLite\View\FormField\Textarea\Simple',
    'integer'  => 'XLite\View\FormField\Input\Text\Integer',
    'uinteger' => 'XLite\View\FormField\Input\Text\Integer',
    'float'    => 'XLite\View\FormField\Input\Text\Float',
    'money'    => 'XLite\View\FormField\Input\Text\Price',
    'boolean'  => 'XLite\View\FormField\Input\Checkbox\Enabled',
);

$type2inlineFields = array(
    'integer'  => 'XLite\View\FormField\Inline\Input\Text\Integer',
    'uinteger' => 'XLite\View\FormField\Inline\Input\Text\Integer',
    'float'    => 'XLite\View\FormField\Inline\Input\Text\Float',
    'money'    => 'XLite\View\FormField\Inline\Input\Text\Price',
);

$entityHumanReadable = macro_convert_camel_to_human_readable($entityRelativeClass);

$itemsListClass = macro_assemble_class_name('View\\ItemsList\\Model\\' . $entityRelativeClass, $moduleAuthor, $moduleName);

$itemsListViewList = $moduleAuthor
    ? strtolower($moduleAuthor . '.' . $moduleName) . '.' . $target . '.list'
    : $target . '.list';

// }}}

// {{{ Build list

echo 'Build list' . PHP_EOL;

// {{{ List controller

echo "\t" . 'controller ' . $targetControllerPath . ' ... ';

$targetHumanReadableName = macro_convert_camel_to_human_readable($target);

$string = macro_get_class_repo_header($targetControllerPath)
    . <<<CODE
/**
 * $targetHumanReadableName controller
 */
class $targetShort extends \\XLite\\Controller\\Admin\\AAdmin
{

CODE;

if ($editableFields || $removable || $switchable || $sortable) {
    $string .= <<<CODE

    /**
     * Update list
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        \$list = new \\$itemsListClass;
        \$list->processQuick();
    }

CODE;
}

$string .= <<<CODE

    // {{{ Search

    /**
     * Get search condition parameter by name
     *
     * @param string \$paramName Parameter name
     *
     * @return mixed
     */
    public function getCondition(\$paramName)
    {
        \$searchParams = \$this->getConditions();

        return isset(\$searchParams[\$paramName])
            ? \$searchParams[\$paramName]
            : null;
    }

    /**
     * Save search conditions
     *
     * @return void
     */
    protected function doActionSearch()
    {
        \$cellName = \\$itemsListClass::getSessionCellName();

        \XLite\Core\Session::getInstance()->\$cellName = \$this->getSearchParams();
    }

    /**
     * Return search parameters
     *
     * @return array
     */
    protected function getSearchParams()
    {
        \$searchParams = \$this->getConditions();

        foreach (
            \\$itemsListClass::getSearchParams() as \$requestParam
        ) {
            if (isset(\XLite\Core\Request::getInstance()->\$requestParam)) {
                \$searchParams[\$requestParam] = \XLite\Core\Request::getInstance()->\$requestParam;
            }
        }

        return \$searchParams;
    }

    /**
     * Get search conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        \$cellName = \\$itemsListClass::getSessionCellName();

        \$searchParams = \XLite\Core\Session::getInstance()->\$cellName;

        if (!is_array(\$searchParams)) {
            \$searchParams = array();
        }

        return \$searchParams;
    }

    // }}}

CODE;

$string .= <<<CODE

}
CODE;

macro_file_put_contents($targetControllerPath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ List page widget

$viewListPageClass = macro_assemble_class_name('View\\' . $targetShort, $moduleAuthor, $moduleName);
$viewListPagePath = macro_convert_class_name_to_path($viewListPageClass);

echo "\t" . 'page widget ' . $viewListPagePath . ' ... ';

$viewListPageTemplate = macro_assemble_tpl_name($targetSkinDir . '/body.tpl', $moduleAuthor, $moduleName);

$string = macro_get_class_repo_header($viewListPagePath)
    . <<<CODE
/**
 * $targetHumanReadableName page view
 *
 * @ListChild (list="admin.center", zone="admin")
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
        return '$viewListPageTemplate';
    }

    /**
     * Check - search box is visible or not
     * 
     * @return boolean
     */
    protected function isSearchVisible()
    {
        return 0 < \XLite\Core\Database::getRepo('$entityClass')->count();
    }

}
CODE;

macro_file_put_contents($viewListPagePath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ List page template

$viewListPageTemplateFull = LC_DIR_SKINS . 'admin/en/' . $viewListPageTemplate;

echo "\t" . 'page template ' . $viewListPageTemplateFull . ' ... ';

$viewListTableTemplate = macro_assemble_tpl_name($targetSkinDir . '/list.tpl', $moduleAuthor, $moduleName);

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

if ($searchFields) {
    $viewListSearchTemplate = macro_assemble_tpl_name($targetSkinDir . '/search.tpl', $moduleAuthor, $moduleName);
    $string .= <<<CODE
<widget IF="isSearchVisible()" template="common/dialog.tpl" body="$viewListSearchTemplate" />

CODE;
}

$string .= <<<CODE
<widget template="common/dialog.tpl" body="$viewListTableTemplate" />
CODE;

macro_file_put_contents($viewListPageTemplateFull, $string);
echo 'done' . PHP_EOL;

// }}}

if (isset($viewListSearchTemplate)) {

    // {{{ List search template

    $viewListSearchTemplateFull = LC_DIR_SKINS . 'admin/en/' . $viewListSearchTemplate;

    echo "\t" . 'search template ' . $viewListSearchTemplateFull . ' ... ';

    $formListSearchClass = macro_assemble_class_name('View\\Form\\ItemsList\\' . $entityRelativeClass . '\\Search', $moduleAuthor, $moduleName);

    $string = <<<CODE
{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * $targetHumanReadableName list search template
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *}

<widget class="\\$formListSearchClass" name="search" />
  <ul class="search-conditions">
    {displayViewListContent(#$itemsListViewList.search.conditions#)}
  </ul>
<widget name="search" end />
CODE;

    macro_file_put_contents($viewListSearchTemplateFull, $string);
    echo 'done' . PHP_EOL;

    // }}}

    // {{{ Search form widget

    $formListSearchPath = macro_convert_class_name_to_path($formListSearchClass);

    echo "\t" . 'search form widget ' . $formListSearchPath . ' ... ';

    $formListSearchClassShort = macro_get_class_short_name($formListSearchClass);

$string = macro_get_class_repo_header($formListSearchPath)
    . <<<CODE
/**
 * $targetHumanReadableName list search form
 */
class $formListSearchClassShort extends \\XLite\\View\\Form\\AForm
{
    /**
     * Return default value for the "target" parameter
     *
     * @return string
     */
    protected function getDefaultTarget()
    {
        return '$target';
    }

    /**
     * Return default value for the "action" parameter
     *
     * @return string
     */
    protected function getDefaultAction()
    {
        return 'search';
    }
}
CODE;

    macro_file_put_contents($formListSearchPath, $string);
    echo 'done' . PHP_EOL;

    // }}}

    // {{{ Search condition templates

    $i = 0;
    foreach ($searchFields as $field) {
        $i++;

        $viewListConditionTemplate = macro_assemble_tpl_name($targetSkinDir . '/conditions/' . $field . '.tpl', $moduleAuthor, $moduleName);
        $viewListConditionTemplateFull = LC_DIR_SKINS . 'admin/en/' . $viewListConditionTemplate;

        echo "\t" . 'search condition (' . $field . ') template ' . $viewListConditionTemplateFull . ' ... ';

        $fieldHumanReadable = macro_convert_camel_to_human_readable($field);
        $weight = $i * 100;

        $string = <<<CODE
{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * $fieldHumanReadable condition
 *
 * @author    Creative Development LLC <info@cdev.ru> 
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *
 * @ListChild (list="$itemsListViewList.search.conditions", weight="$weight")
 *}

<li class="condition $field">
  <widget class="XLite\View\FormField\Input\Text" fieldName="$field" value="{getCondition(#$field#):r}" label="{t(#$fieldHumanReadable#)}" />
</li>
CODE;

        macro_file_put_contents($viewListConditionTemplateFull, $string);
        echo 'done' . PHP_EOL;

    }

	$i++;
	$weight = $i * 100;

    $viewListConditionTemplate = macro_assemble_tpl_name($targetSkinDir . '/conditions/action.search.tpl', $moduleAuthor, $moduleName);
    $viewListConditionTemplateFull = LC_DIR_SKINS . 'admin/en/' . $viewListConditionTemplate;

    echo "\t" . 'search \'Search\' button template ' . $viewListConditionTemplateFull . ' ... ';

    $string = <<<CODE
{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * Search button
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *
 * @ListChild (list="$itemsListViewList.search.conditions", weight="$weight")
 *}

<li class="action search">
  <widget class="\XLite\View\Button\Submit" label="{t(#Search#)}" />
</li>
CODE;

    macro_file_put_contents($viewListConditionTemplateFull, $string);
    echo 'done' . PHP_EOL;

    // }}}

}

// {{{ List table template

$viewListTableTemplateFull = LC_DIR_SKINS . 'admin/en/' . $viewListTableTemplate;

echo "\t" . 'table template ' . $viewListTableTemplateFull . ' ... ';

$formListTableClass = macro_assemble_class_name('View\\Form\\ItemsList\\' . $entityRelativeClass . '\\Table', $moduleAuthor, $moduleName);

$string = <<<CODE
{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * $targetHumanReadableName list table template
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *}

<widget class="$formListTableClass" name="list" />
  <widget class="$itemsListClass" />
<widget name="list" end />
CODE;

macro_file_put_contents($viewListTableTemplateFull, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Table form widget

$formListTablePath = macro_convert_class_name_to_path($formListTableClass);

echo "\t" . 'table form widget ' . $formListTablePath . ' ... ';

$formListTableClassShort = macro_get_class_short_name($formListTableClass);

$string = macro_get_class_repo_header($formListTablePath)
    . <<<CODE
/**
 * $targetHumanReadableName list table form
 */
class $formListTableClassShort extends \\XLite\\View\\Form\\ItemsList\\AItemsList
{
    /**
     * Return default value for the "target" parameter
     *
     * @return string
     */
    protected function getDefaultTarget()
    {
        return '$target';
    }

    /**
     * Return default value for the "action" parameter
     *
     * @return string
     */
    protected function getDefaultAction()
    {
        return 'update';
    }
}
CODE;

macro_file_put_contents($formListTablePath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Head-search widgets

$headSearchWidgets = array();
if ($headSearch && $headSearchFields) {
    foreach ($headSearchFields as $field) {
        $headSearchWidget = macro_assemble_class_name(
            'View\\FormField\\Search\\' . macro_get_class_short_name($entityClass) . '\\' . ucfirst(\Includes\Utils\Converter::convertToCamelCase($field)),
            $moduleAuthor,
            $moduleName
        );
        $headSearchWidgets[$field] = $headSearchWidget;
        $headSearchWidgetShort = macro_get_class_short_name($headSearchWidget);
        $path = macro_convert_class_name_to_path($headSearchWidget);

        echo "\t" . 'search head widget ' . $path . ' ... ';

        $fieldHumanReadable = macro_convert_camel_to_human_readable($field);

        $string = macro_get_class_repo_header($path)
    . <<<CODE
/**
 * $fieldHumanReadable search widget
 */
class $headSearchWidgetShort extends \\XLite\\View\\FormField\\Search\\ASearch
{
    /**
     * Define fields
     *
     * @return array
     */
    protected function defineFields()
    {
        return array(
            array(
                static::FIELD_NAME  => '$field',
                static::FIELD_CLASS => 'XLite\View\FormField\Input\Text',
            ),
        );
    }
}
CODE;

        macro_file_put_contents($path, $string);
        echo 'done' . PHP_EOL;
        
    }
}

// }}}

// {{{ Item list widget

$itemsListPath = macro_convert_class_name_to_path($itemsListClass);

echo "\t" . 'items list widget ' . $itemsListPath . ' ... ';

$itemsListClassShort = macro_get_class_short_name($itemsListClass);

$itemsListParams = array();

$string = macro_get_class_repo_header($itemsListPath)
    . <<<CODE
/**
 * $targetHumanReadableName items list
 */
class $itemsListClassShort extends \\XLite\\View\\ItemsList\\Model\\Table
{

CODE;

// Widget parameters
if ($unitedSearchFields) {

    $string .= <<<CODE
    /**
     * Widget param names
     */

CODE;
    foreach ($unitedSearchFields as $field) {
        $const = strtoupper($field);
        $fieldHumanReadable = macro_convert_camel_to_human_readable($field);

        $string .= <<<CODE
    const PARAM_SEARCH_$const = '$field';

CODE;
        $itemsListParams['PARAM_SEARCH_' . $const] = <<<CODE
new \XLite\Model\WidgetParam\String('$fieldHumanReadable', '')
CODE;
    }

    $string .= <<<CODE

CODE;

}

// Sort by modes
if ($sortFields) {
    $string .= <<<CODE

    /**
     * Sort modes
     *
     * @var   array
     */
    protected \$sortByModes = array(

CODE;

    foreach ($sortFields as $field) {
        $string .= '        \'' . $alias . '.' . $field . '\' => \'' . macro_convert_camel_to_human_readable($field) . '\',' . PHP_EOL;
    }

    $string .= <<<CODE
    );


CODE;
}

// CSS
$itemsListCSS = macro_assemble_tpl_name($targetSkinDir . '/style.css', $moduleAuthor, $moduleName);

$string .= <<<CODE
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


CODE;

// defineWidgetParams
if ($itemsListParams) {

    $string .= <<<CODE

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        \$this->widgetParams += array(

CODE;

    foreach ($itemsListParams as $const => $paramDeclaration) {
        $string .= <<<CODE
            static::$const => $paramDeclaration,

CODE;
    }

    $string .= <<<CODE
        );
    }


CODE;
}

// defineColumns

$string .= <<<CODE
    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(

CODE;

foreach ($fields as $field) {
    $fieldHumanReadable = macro_convert_camel_to_human_readable($field);
    $string .= <<<CODE
            '$field' => array(
                static::COLUMN_NAME          => \XLite\Core\Translation::lbl('$fieldHumanReadable'),

CODE;
    if ($editableFields && in_array($field, $editableFields)) {
        $metaField = isset($metaData->fieldMappings[$field]) ? $metaData->fieldMappings[$field] : array();
        $class = isset($metaField['type']) && isset($type2inlineFields[$metaField['type']])
            ? $type2inlineFields[$metaField['type']]
            : 'XLite\View\FormField\Inline\Input\Text';
        $string .= <<<CODE
                static::COLUMN_CLASS         => '$class',

CODE;
    }

    if ($sortFields && in_array($field, $sortFields)) {
        $string .= <<<CODE
                static::COLUMN_SORT          => '{$alias}.{$field}',

CODE;
    }

    if ($headSearchFields && in_array($field, $headSearchFields)) {
        $string .= <<<CODE
                static::COLUMN_SEARCH_WIDGET => '{$headSearchWidgets[$field]}',

CODE;
    }

    $string .= <<<CODE
            ),

CODE;
}

$string .= <<<CODE
        );
    }


CODE;

// defineRepositoryName
$string .= <<<CODE
    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return '$entityClass';
    }


CODE;

// getCreateURL
$string .= <<<CODE

    /**
     * Creation button position
     *
     * @return integer
     */
    protected function isCreation()
    {
        return static::CREATE_INLINE_TOP;
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return \XLite\Core\Converter::buildUrl('$targetOne');
    }


CODE;

// getOrderBy
if ($sortFields) {
    $string .= <<<CODE
    /**
     * Return 'Order by' array.
     * array(<Field to order>, <Sort direction>)
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.6
     */
    protected function getOrderBy()
    {
        return array(\$this->getSortBy(), \$this->getSortOrder());
    }


CODE;
}

// getCreateButtonLabel
$entityHumanReadableLow = lcfirst($entityHumanReadable);
$string .= <<<CODE
    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'New $entityHumanReadableLow';
    }


CODE;

if ($createInline) {
    $string .= <<<CODE
    /**
     * Inline creation mechanism position
     *
     * @return integer
     */
    protected function isInlineCreation()
    {
        return static::CREATE_INLINE_TOP;
    }


CODE;
}

// {{{ Behaviors

if ($removable || $switchable || $sortable) {

    $string .= <<<CODE

        // {{{ Behaviors


CODE;

    if ($removable) {
        $string .= <<<CODE
    /**
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return true;
    }


CODE;
    }

    if ($switchable) {
        $string .= <<<CODE
    /**
     * Mark list as switchyabvle (enable / disable)
     *
     * @return boolean
     */
    protected function isSwitchable()
    {
        return true;
    }


CODE;
    }

    if ($sortable) {
        $string .= <<<CODE
    /**
     * Mark list as sortable
     *
     * @return integer
     */
    protected function getSortableType()
    {
        return static::SORT_TYPE_INPUT;
    }


CODE;
    }

    $string .= <<<CODE


    // }}}


CODE;
}

// }}}

// Container class
$string .= <<<CODE
    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' $target';
    }


CODE;

// Panel class
if ($editableFields || $switchable || $removable) {
    $itemsListPanelClass = macro_assemble_class_name('View\\StickyPanel\\ItemsList\\' . $entityRelativeClass, $moduleAuthor, $moduleName);
    $string .= <<<CODE
    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return '$itemsListPanelClass';
    }


CODE;
}

// Pager
if ($pagination) {
    $string .= <<<CODE
    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return 'XLite\View\Pager\Admin\Model\Table';
    }


CODE;
}

// Search
$string .= <<<CODE

    // {{{ Search

    /**
     * Return search parameters.
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array(

CODE;

    foreach ($unitedSearchFields as $field) {
        $const = strtoupper($field);
        $string .= <<<CODE
            \\$entityRepoClass::SEARCH_$const => static::PARAM_SEARCH_$const,
CODE;

    }

    $string .= <<<CODE
        );
    }


CODE;

if ($unitedSearchFields) {
    $string .= <<<CODE
    /**
     * Define so called "request" parameters
     *
     * @return void
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();

CODE;
    foreach ($unitedSearchFields as $field) {
        $const = strtoupper($field);
        $string .= <<<CODE
        \$this->requestParams[] = static::PARAM_SEARCH_$const;
CODE;
    }
    $string .= <<<CODE
    }


CODE;
}

$string .= <<<CODE
    /**
     * Return params list to use for search
     * TODO refactor
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        \$result = parent::getSearchCondition();


CODE;

if ($sortFields) {
    $tmp = '{\\' . $entityRepoClass . '::SEARCH_ORDERBY}';
    $string .= <<<CODE
        \$result->$tmp = \$this->getOrderBy();

CODE;
}

$string .= <<<CODE

        foreach (static::getSearchParams() as \$modelParam => \$requestParam) {
            \$paramValue = \$this->getParam(\$requestParam);

            if ('' !== \$paramValue && 0 !== \$paramValue) {
                \$result->\$modelParam = \$paramValue;
            }
        }

        return \$result;
    }

    // }}}

}
CODE;

macro_file_put_contents($itemsListPath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Items list CSS

$itemsListCSSFull = LC_DIR_SKINS . 'admin/en/' . $itemsListCSS;
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

// {{{ Sticky panel

if (isset($itemsListPanelClass)) {

    $itemsListPanelPath = macro_convert_class_name_to_path($itemsListPanelClass);

    echo "\t" . 'sticky panel widget ' . $itemsListPanelPath . ' ... ';

    $itemsListPanelClassShort = macro_get_class_short_name($itemsListPanelClass);

    $string = macro_get_class_repo_header($itemsListPanelPath)
    . <<<CODE
/**
 * $targetHumanReadableName items list's sticky panel
 */
class $itemsListPanelClassShort extends \\XLite\\View\\StickyPanel\\ItemsListForm
{
}
CODE;

    macro_file_put_contents($itemsListPanelPath, $string);
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

	foreach ($unitedSearchFields as $field) {
		$const = strtoupper($field);
		$consts .= <<<CODE
    const SEARCH_$const = '$field';

CODE;
	    $handlingSearchParams .= <<<CODE
            static::SEARCH_$const,

CODE;
		$fieldUpper = ucfirst($field);

		$prepareMethods .= <<<CODE
    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder \$queryBuilder Query builder to prepare
     * @param array|string               \$value        Condition data
     * @param boolean                    \$countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCnd$fieldUpper(\Doctrine\ORM\QueryBuilder \$queryBuilder, \$value, \$countOnly)
    {
        if (\$value) {
            \$queryBuilder->andWhere('$alias.$field = :$field')
                ->setParameter('$field', \$value);
        }
    }
CODE;
}

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

		if ($sortFields) {
			$consts .= <<<CODE
    const SEARCH_ORDERBY = 'orderBy';

CODE;
		    $handlingSearchParams .= <<<CODE
            static::SEARCH_ORDERBY,

CODE;
		    $prepareMethods .= <<<CODE
    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder \$queryBuilder Query builder to prepare
     * @param array|string               \$value        Condition data
     * @param boolean                    \$countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndOrderBy(\Doctrine\ORM\QueryBuilder \$queryBuilder, \$value, \$countOnly)
    {
        if (!\$countOnly) {
            if (is_string(\$value)) {
                list(\$sort, \$order) = explode(' ', \$value, 2);

            } else {
                list(\$sort, \$order) = \$value;
            }

            if (\$sort) {
                \$queryBuilder->addOrderBy(\$sort, \$order);
            }
        }
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

// {{{ Menu item

if ($menu) {

    $menuClass = macro_assemble_class_name('View\\Menu\\Admin\\TopMenu', $moduleAuthor, $moduleName);
    $menuPath = macro_convert_class_name_to_path($menuClass);

    echo 'Build menu item' . PHP_EOL
		. "\t" . 'menu collection ' . $menuPath . ' ... ';

    $menuShortClass = macro_get_class_short_name($menuClass);

    $string = macro_get_class_repo_header($menuPath)
    . <<<CODE
/**
 * Top menu widget
 */
class TopMenu extends \\XLite\\View\\Menu\\Admin\\TopMenu implements \\XLite\\Base\\IDecorator
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

// }}}

// {{{ Build one

echo 'Build entity' . PHP_EOL;

// {{{ Controller

echo "\t" . 'controller ' . $targetOneControllerPath . ' ... ';

$oneViewModelClass = macro_assemble_class_name('View\\Model\\' . $entityRelativeClass, $moduleAuthor, $moduleName);
$targetOneHumanReadableName = macro_convert_camel_to_human_readable($targetOne);

$string = macro_get_class_repo_header($targetOneControllerPath)
    . <<<CODE
/**
 * $targetOneHumanReadableName controller
 */
class $targetOneShort extends \\XLite\\Controller\\Admin\\AAdmin
{
    /**
     * Controller parameters
     *
     * @var array
     */
    protected \$params = array('target', 'id');

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        \$id = intval(\XLite\Core\Request::getInstance()->id);
        \$model = \$id
            ? \XLite\Core\Database::getRepo('$entityClass')->find(\$id)
            : null;

        return (\$model && \$model->getId())
            ? \$model->getName()
            : \XLite\Core\Translation::getInstance()->lbl('$entityShortClass');
    }

    /**
     * Update model
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        if (\$this->getModelForm()->performAction('modify')) {
            \$this->setReturnUrl(\XLite\Core\Converter::buildURL('$target'));
        }
    }

    /**
     * Get model form class
     * 
     * @return string
     */
    protected function getModelFormClass()
    {
        return '$oneViewModelClass';
    }

}
CODE;

macro_file_put_contents($targetOneControllerPath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ One page widget

$viewOnePageClass = macro_assemble_class_name('View\\' . $targetOneShort, $moduleAuthor, $moduleName);
$viewOnePagePath = macro_convert_class_name_to_path($viewOnePageClass);

echo "\t" . 'page widget ' . $viewListPagePath . ' ... ';

$viewOnePageTemplate = macro_assemble_tpl_name($targetOneSkinDir . '/body.tpl', $moduleAuthor, $moduleName);

$string = macro_get_class_repo_header($viewOnePagePath)
    . <<<CODE
/**
 * $targetOneHumanReadableName page view
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class $targetOneShort extends \\XLite\\View\\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), array('$targetOne'));
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return '$viewOnePageTemplate';
    }

}
CODE;

macro_file_put_contents($viewOnePagePath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ List page template

$viewOnePageTemplateFull = LC_DIR_SKINS . 'admin/en/' . $viewOnePageTemplate;

echo "\t" . 'page template ' . $viewOnePageTemplateFull . ' ... ';

$string = <<<CODE
{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * $targetOneHumanReadableName page template
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *}
<widget class="$oneViewModelClass" useBodyTemplate="1" />
CODE;

macro_file_put_contents($viewOnePageTemplateFull, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ One model view

$oneViewModelPath = macro_convert_class_name_to_path($oneViewModelClass);

echo "\t" . 'one entity view model ' . $oneViewModelPath . ' ... ';

$oneViewModelClassShort = macro_get_class_short_name($oneViewModelClass);
$formOneClass = macro_assemble_class_name('View\\Form\\Model\\' . $entityRelativeClass, $moduleAuthor, $moduleName);

$targetOneHumanReadableNameLow = strtolower($targetOneHumanReadableName);

$string = macro_get_class_repo_header($oneViewModelPath)
    . <<<CODE
/**
 * $targetOneHumanReadableName view model
 */
class $oneViewModelClassShort extends \\XLite\\View\\Model\\AModel
{
    /**
     * Shema default
     *
     * @var array
     */
    protected \$schemaDefault = array(

CODE;

$keys = $metaData->fieldMappings;
if (isset($metaDataTranslations)) {
    foreach ($metaDataTranslations->fieldMappings as $key => $field) {
        if (!in_array($key, array('label_id', 'id', 'code'))) {
            $keys[$key] = $field;
        }
    }
}

foreach ($keys as $key => $field) {
    if ('id' != $key) {

        $name = macro_convert_camel_to_human_readable($key);

        $class = isset($type2fields[$field['type']]) ? $type2fields[$field['type']] : 'XLite\View\FormField\Input\Text';
        $string .= <<<CODE
        '$key' => array(
            self::SCHEMA_CLASS    => '$class',
            self::SCHEMA_LABEL    => '$name',
            self::SCHEMA_REQUIRED => false,
        ),

CODE;
    }
}

$string .= <<<CODE
    );

    /**
     * Return current model ID
     *
     * @return integer
     */
    public function getModelId()
    {
        return \XLite\Core\Request::getInstance()->id;
    }

    /**
     * This object will be used if another one is not pased
     *
     * @return \\$entityClass
     */
    protected function getDefaultModelObject()
    {
        \$model = \$this->getModelId()
            ? \XLite\Core\Database::getRepo('$entityClass')->find(\$this->getModelId())
            : null;

        return \$model ?: new \\$entityClass;
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\\$formOneClass';
    }

    /**
     * Return list of the "Button" widgets
     *
     * @return array
     */
    protected function getFormButtons()
    {
        \$result = parent::getFormButtons();

        \$label = \$this->getModelObject()->getId() ? 'Update' : 'Create';

        \$result['submit'] = new \XLite\View\Button\Submit(
            array(
                \XLite\View\Button\AButton::PARAM_LABEL => \$label,
                \XLite\View\Button\AButton::PARAM_STYLE => 'action',
            )
        );

        return \$result;
    }

    /**
     * Add top message
     *
     * @return void
     */
    protected function addDataSavedTopMessage()
    {
        if ('create' != \$this->currentAction) {
            \XLite\Core\TopMessage::addInfo('The $targetOneHumanReadableNameLow has been updated');

        } else {
            \XLite\Core\TopMessage::addInfo('The $targetOneHumanReadableNameLow has been added');
        }
    }

}
CODE;

macro_file_put_contents($oneViewModelPath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ View model form widget

$formOnePath = macro_convert_class_name_to_path($formOneClass);

echo "\t" . 'form ' . $formOnePath . ' ... ';

$formOneClassShort = macro_get_class_short_name($formOneClass);
$oneFormCSS = macro_assemble_tpl_name($targetOneSkinDir . '/style.css', $moduleAuthor, $moduleName);

$className = strtolower(str_replace('_', '-', $targetOne));

$string = macro_get_class_repo_header($formOnePath)
    . <<<CODE
/**
 * $targetHumanReadableName list search form
 */
class $formOneClassShort extends \\XLite\\View\\Form\\AForm
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        \$list = parent::getCSSFiles();

        \$list[] = '$oneFormCSS';

        return \$list;
    }

    /**
     * Return default value for the "target" parameter
     *
     * @return string
     */
    protected function getDefaultTarget()
    {
        return '$targetOne';
    }

    /**
     * Return default value for the "action" parameter
     *
     * @return string
     */
    protected function getDefaultAction()
    {
        return 'update';
    }

    /**
     * Get default class name
     *
     * @return string
     */
    protected function getDefaultClassName()
    {
        return trim(parent::getDefaultClassName() . ' validationEngine $className');
    }

    /**
     * Return list of the form default parameters
     *
     * @return array
     */
    protected function getDefaultParams()
    {
        return array(
            'id' => \XLite\Core\Request::getInstance()->id,
        );
    }

}
CODE;

macro_file_put_contents($formOnePath, $string);
echo 'done' . PHP_EOL;

// }}}

// {{{ Model page CSS

$oneFormCSSFull = LC_DIR_SKINS . 'admin/en/' . $oneFormCSS;
echo "\t" . 'styles file ' . $oneFormCSSFull . ' ... ';

$string = <<<CODE
/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * $targetOneHumanReadableName view model styles
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 */

CODE;

macro_file_put_contents($oneFormCSSFull, $string);
echo 'done' . PHP_EOL;

// }}}

// }}}

die(0);

// {{{ Help

function macro_help()
{
    $script = __FILE__;

    return <<<HELP
Usage: $script --entity=XLite\Model\Entity --fields=fld1,fld2,...,fldn [--edit=fld1,fld2,...,fldn] [--target=target_name] [--removable] [--switchable] [--sortable] [--search=fld1,fld2,...,fldn] [--pagintation] [--sort=fld1,fld2,...,fldn] [--createInline] [--menu=section]

    --entity=class_name
        Entity class (XLite\Model\Product) or full path to class repository file (src/class/XLite/Model/Product.php) or relative path to class repository file (XLite/Model/Product.php)

    --fields=fld1,fld2,...,fldn
        Fields / columns list

    --edit=fld1,fld2,...,fldn
        Editable fields list. Default - no, all columns is noneditable.

    --target=target_name
        List controller short name (target). Default - class short name + 's' suffix (\XLite\Model\Product -> products)

    --removable
        Entity will remove from list. Defualt - no

    --switchable
        Entity will swicth (enabled / disabled) from list. Defualt - no

    --sortable
        Entiyt will change position from list. Defualt - no

    --search=fld1,fld2,...,fldn
        Search fields list. Default - without any search.

    --headSearch
        Use head search. Default - no.

    --pagintation
        Use pagination. Default - no.

    --sort=fld1,fld2,...,fldn
        Sort fields list. Default - no sort.

    --createInline
        Create entity inline. Default - no

    --menu=section
        Create menu section item. Default - no

Example: .dev/macro/$script --entity=XLite\\Module\\Developer\\Example\\Model\\Message --fields=subject --edit=subject --removable --search=subject --headSearch --pagintation --sort=subject --menu=catalog

HELP;
}

// }}}

