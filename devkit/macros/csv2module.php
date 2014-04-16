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
 * @copyright Copyright (c) 2010-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 */

if (file_exists(__DIR__ . '/core.php') && PHP_SAPI == 'cli') {
    require_once __DIR__ . '/core.php';

} else {

    // Autloader
    spl_autoload_register(
        function($class) {
            $class = ltrim($class, '\\');
            list($prefix) = explode('\\', $class, 2);

            if ('Symfony' == $prefix) {
                require_once (__DIR__ . '/lib/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
            }
        }
    );
}

// Console runner
if (PHP_SAPI == 'cli') {
    $author      = csv2m_get_named_argument('author');
    $authorName  = csv2m_get_named_argument('authorName') ?: $author;
    $module      = csv2m_get_named_argument('module');
    $moduleName  = csv2m_get_named_argument('moduleName') ?: $module;
    $description = csv2m_get_named_argument('description') ?: $moduleName;
    $path        = csv2m_get_named_argument('path');
    $code        = csv2m_get_named_argument('code');
    $delimiter   = csv2m_get_named_argument('delimiter') ?: ',';
    $core       = csv2m_get_named_argument('core') ?: '5.0';

    if (empty($author)) {
        print '--author argument is empty!' . PHP_EOL
            . PHP_EOL
            . csv2m_help();
        die(1);

    } elseif (!csv2m_validate_author($author)) {
        print '--author argument has wrong format!' . PHP_EOL
            . PHP_EOL
            . csv2m_help();
        die(1);
    }

    if (empty($code)) {
        print '--code argument is empty!' . PHP_EOL
            . PHP_EOL
            . csv2m_help();
        die(4);

    } elseif (!csv2m_validate_code($code)) {
        print '--code argument has wrong format!' . PHP_EOL
            . PHP_EOL
            . csv2m_help();
        die(4);
    }

    if (empty($module)) {
        $module = ucfirst($code) . 'Translation';

    } elseif (!csv2m_validate_module($module)) {
        print '--module argument has wrong format!' . PHP_EOL
            . PHP_EOL
            . csv2m_help();
        die(2);
    }

    if (empty($path)) {
        print '--path argument is empty!' . PHP_EOL
            . PHP_EOL
            . csv2m_help();
        die(3);

    } elseif (!file_exists($path)) {
        print 'CSV file did not exists!' . PHP_EOL
            . PHP_EOL
            . csv2m_help();
        die(3);

    } elseif (!is_readable($path)) {
        print 'CSV file did not readable!' . PHP_EOL
            . PHP_EOL
            . csv2m_help();
        die(3);
    }

    if (empty($delimiter)) {
        print '--delimiter argument is empty!' . PHP_EOL
            . PHP_EOL
            . csv2m_help();
        die(5);
    }

    $destPath = getcwd() . DIRECTORY_SEPARATOR . 'module.tar';

    $errors = csv2module($path, $destPath, $author, $module, $code, $delimiter, $core, $authorName, $moduleName, $description);
    if ($errors) {
        foreach ($errors as $error) {
            print $error . PHP_EOL;
        }
        print PHP_EOL . csv2m_help();
        die(4);

    } else {

        rename($destPath, $destPath . '.gz');

        print 'Module .phar-pack: ' . $destPath . '.gz' . PHP_EOL;
        print 'Module directory: ' . $destPath . '.directory' . PHP_EOL;

        die(0);
    }
}

// {{{ Module packer

function csv2m_validate_author($string)
{
    return (bool)preg_match('/^[0-9a-zA-Z]{3,64}$/Ss', $string);
}

function csv2m_validate_module($string)
{
    return (bool)preg_match('/^[0-9a-zA-Z]{3,64}$/Ss', $string);
}

function csv2m_validate_code($string)
{
    return (bool)preg_match('/^[a-z]{2}$/Ss', $string);
}

function csv2module(
    $path,
    &$destPath,
    $author,
    $module,
    $code,
    $delimiter = ',',
    $core = '5.0',
    $authorName = null,
    $moduleName = null,
    $description = null
) {
    $authorName = $authorName ?: $author;
    $moduleName = $moduleName ?: $module;
    $description = $description ?: $moduleName;

    $moduleNameEscape = addslashes($moduleName);
    $descriptionEscape = addslashes($description);

    $errors = array();

    $dir = $destPath . '.directory';

    if (file_exists($destPath)) {
        unlink($destPath);
    }

    if (file_exists($destPath . '.gz')) {
        unlink($destPath . '.gz');
    }

    if (file_exists($dir)) {
        delTree($dir);
    }

    mkdir($dir);
    mkdir($dir . '/classes');
    mkdir($dir . '/classes/XLite');
    mkdir($dir . '/classes/XLite/Module');
    mkdir($dir . '/classes/XLite/Module/' . $author);
    mkdir($dir . '/classes/XLite/Module/' . $author . '/' . $module);

    $dir2 = $dir . '/classes/XLite/Module/' . $author . '/' . $module;

    // Write install.yzml

    $fp = fopen($path, 'rb');

    $data = array();
    do {

        $row = fgetcsv($fp, 0, $delimiter);
        if (is_array($row) && 2 <= count($row)) {

            $data[] = array(
                'name' => $row[0],
                'translations' => array(
                    array(
                        'code'  => $code,
                        'label' => $row[1],
                    ),
                ),
            );
        }

    } while (is_array($row));

    fclose($fp);

    $data = array(
        'XLite\Model\Language' => array(
            array(
                'code'         => $code,
                'module'       => $author . '\\' . $module,
            ),
        ),
        'XLite\Model\LanguageLabel' => array_merge(
            array('directives' => array('addModel' => 'XLite\Model\LanguageLabelTranslation')),
            $data
        ),
    );

    $data = <<<YAML
# vim: set ts=2 sw=2 sts=2 et:
#
# Translation data ($code)
#
# @link      http://www.x-cart.com/


YAML
    . \Symfony\Component\Yaml\Yaml::dump($data);

    file_put_contents($dir2 . '/install.yaml', $data);

    // Write Main.php

    $namespace = 'XLite\\Module\\' . $author . '\\' . $module;

    $data = <<<PHP
<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * @link      http://www.x-cart.com/
 */

namespace $namespace;

/**
 * Russian translation module
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Language code
     */
    const LANG_CODE = '$code';

    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return '$authorName';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return '$moduleNameEscape';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '$core';
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

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return '$descriptionEscape';
    }

    /**
     * Decorator run this method at the end of cache rebuild
     *
     * @return void
     */
    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

        \$language = \XLite\Core\Database::getRepo('\XLite\Model\Language')->findOneByCode(static::LANG_CODE);

        if (isset(\$language)) {

            \$language->setAdded(true);

            if (!\$language->getEnabled()) {
                \$language->setEnabled(true);

                \XLite\Core\TopMessage::addInfo(
                    'The X language has been added and enabled successfully',
                    array('language' => \$language->getName()),
                    \$language->getCode()
                );
            }

        } else {
            \XLite\Core\TopMessage::addError('The language you want to add has not been found');
        }
    }

    /**
     * Method to call just before the module is disabled via core
     *
     * @return void
     */
    public static function callDisableEvent()
    {
        parent::callDisableEvent();

        \$language = \XLite\Core\Database::getRepo('\XLite\Model\Language')->findOneByCode(static::LANG_CODE);

        if (isset(\$language)) {
            \$language->setEnabled(false);
            \$language->setAdded(false);

            \XLite\Core\Database::getRepo('\XLite\Model\Language')->update(\$language);
            \XLite\Core\Translation::getInstance()->reset();
        }
    }

    /**
     * Method to call just before the module is uninstalled via core
     *
     * @return void
     */
    public static function callUninstallEvent()
    {
        parent::callUninstallEvent();

        \$language = \XLite\Core\Database::getRepo('\XLite\Model\Language')->findOneByCode(static::LANG_CODE);

        if (isset(\$language)) {
            \$language->setModule(null);

            \XLite\Core\Database::getRepo('\XLite\Model\Language')->update(\$language);
            \XLite\Core\Translation::getInstance()->reset();
        }
    }

    /**
     * Check if the active current language is German
     *
     * @return boolean
     */
    public static function isActiveLanguage()
    {
        return static::LANG_CODE === \XLite\Core\Session::getInstance()->getLanguage()->getCode();
    }

}
PHP;

    file_put_contents($dir2 . '/Main.php', $data);

    // Pack to .phar

    try {
        $phar = new \PharData($destPath);
        $phar->buildFromDirectory($dir);
        $phar->setMetadata(
            array(
                'RevisionDate'   => time(),
                'ActualName'     => $author . '\\' . $module,
                'VersionMinor'   => '0',
                'VersionMajor'   => $core,
                'MinCoreVersion' => $core,
                'Name'           => $moduleName,
                'Author'         => $authorName,
                'IconLink'       => null,
                'Description'    => $description,
                'Dependencies'   => array(),
                'isSystem'       => false,
            )
        );

        $hash = array(
            'classes/XLite/Module/' . $author . '/' . $module . '/Main.php' => md5_file($dir2 . '/Main.php'),
            'classes/XLite/Module/' . $author . '/' . $module . '/install.yaml' => md5_file($dir2 . '/install.yaml')
        );
        $phar->addFromString('.hash', json_encode($hash));
        $phar->compress(\Phar::GZ);
        unlink($destPath);
        rename($destPath . '.gz', $destPath);

    } catch (\UnexpectedValueException $e) {
        $errors[] = $e->getMessage();
        delTree($dir);

    } catch (\BadMethodCallException $e) {
        $errors[] = $e->getMessage();
        delTree($dir);
    }

    return $errors;
}

// }}}

// {{{ Service

/**
 * Get script argument by name
 *
 * @param string $name Name
 *
 * @return string
 */
function csv2m_get_named_argument($name)
{
    $data = getopt('', array($name . '::'));

    return isset($data[$name]) ? $data[$name] : null;
}

function delTree ($dir)
{ 
    $files = array_diff(scandir($dir), array('.', '..')); 
    foreach ($files as $file) { 
        if (is_dir($dir . '/' . $file)) {
            delTree($dir . '/' . $file);

        } else {
            unlink($dir . '/' . $file);
        }
    }

    return rmdir($dir); 
}

function macro_help()
{
    return csv2m_help();
}

function csv2m_help()
{
    return <<<HELP
Usage: php csv2module.php --path=<path> --author=<author> --code=<language_code> [--module=<module>] [--delimiter=<delimiter>] [-core=<core major version>]

Example: php csv2module.php --path=example.csv --author=JohnSmith --module=EsTranslation --code=es --delimiter=, --core=5.0

Options:
    --path=<path>
        Path to CSV file

    --author=<dev id>
        Developer ID

    --authorName=<author>
        Module author name (human readable)

    --module=<module>
        Module service name. Default - <code>Translation

    --moduleName=<module name>
        Module human readable name. Default - <code>Translation

    --description=<description>
        Module description. Default - <code>Translation

    --code=<language_code>
        2-character language code

    --delimiter=<delimiter>
        Column delimiter. Default - ","

    --core=<core major version>
        X-Cart 5 core major version. Default - 5.0

HELP;

}

function csv2m_get_languages()
{
    return unserialize('a:184:{s:2:"aa";a:2:{s:3:"int";a:1:{i:0;s:4:"Afar";}s:6:"native";a:1:{i:0;s:6:"Afaraf";}}s:2:"ab";a:2:{s:3:"int";a:1:{i:0;s:9:"Abkhazian";}s:6:"native";a:1:{i:0;s:9:"Abkhazian";}}s:2:"af";a:2:{s:3:"int";a:1:{i:0;s:9:"Afrikaans";}s:6:"native";a:1:{i:0;s:9:"Afrikaans";}}s:2:"ak";a:2:{s:3:"int";a:1:{i:0;s:4:"Akan";}s:6:"native";a:1:{i:0;s:4:"Akan";}}s:2:"sq";a:2:{s:3:"int";a:1:{i:0;s:8:"Albanian";}s:6:"native";a:1:{i:0;s:5:"Shqip";}}s:2:"am";a:2:{s:3:"int";a:1:{i:0;s:7:"Amharic";}s:6:"native";a:1:{i:0;s:12:"አማርኛ";}}s:2:"ar";a:2:{s:3:"int";a:1:{i:0;s:6:"Arabic";}s:6:"native";a:1:{i:0;s:14:"العربية";}}s:2:"an";a:2:{s:3:"int";a:1:{i:0;s:9:"Aragonese";}s:6:"native";a:1:{i:0;s:9:"Aragonés";}}s:2:"hy";a:2:{s:3:"int";a:1:{i:0;s:8:"Armenian";}s:6:"native";a:1:{i:0;s:14:"Հայերեն";}}s:2:"as";a:2:{s:3:"int";a:1:{i:0;s:8:"Assamese";}s:6:"native";a:1:{i:0;s:21:"অসমীয়া";}}s:2:"av";a:2:{s:3:"int";a:1:{i:0;s:6:"Avaric";}s:6:"native";a:2:{i:0;s:17:"авар мацӀ";i:1;s:25:"магӀарул мацӀ";}}s:2:"ae";a:2:{s:3:"int";a:1:{i:0;s:7:"Avestan";}s:6:"native";a:1:{i:0;s:6:"Avesta";}}s:2:"ay";a:2:{s:3:"int";a:1:{i:0;s:6:"Aymara";}s:6:"native";a:1:{i:0;s:9:"Aymar Aru";}}s:2:"az";a:2:{s:3:"int";a:1:{i:0;s:11:"Azerbaijani";}s:6:"native";a:1:{i:0;s:16:"Azərbaycan Dili";}}s:2:"ba";a:2:{s:3:"int";a:1:{i:0;s:7:"Bashkir";}s:6:"native";a:1:{i:0;s:23:"башҡорт теле";}}s:2:"bm";a:2:{s:3:"int";a:1:{i:0;s:7:"Bambara";}s:6:"native";a:1:{i:0;s:10:"Bamanankan";}}s:2:"eu";a:2:{s:3:"int";a:1:{i:0;s:6:"Basque";}s:6:"native";a:2:{i:0;s:7:"Euskara";i:1;s:7:"Euskera";}}s:2:"be";a:2:{s:3:"int";a:1:{i:0;s:10:"Belarusian";}s:6:"native";a:1:{i:0;s:20:"Беларуская";}}s:2:"bn";a:2:{s:3:"int";a:1:{i:0;s:7:"Bengali";}s:6:"native";a:1:{i:0;s:15:"বাংলা";}}s:2:"bh";a:2:{s:3:"int";a:1:{i:0;s:16:"Bihari Languages";}s:6:"native";a:1:{i:0;s:16:"Bihari Languages";}}s:2:"bi";a:2:{s:3:"int";a:1:{i:0;s:7:"Bislama";}s:6:"native";a:1:{i:0;s:7:"Bislama";}}s:2:"bs";a:2:{s:3:"int";a:1:{i:0;s:7:"Bosnian";}s:6:"native";a:1:{i:0;s:14:"Bosanski Jezik";}}s:2:"br";a:2:{s:3:"int";a:1:{i:0;s:6:"Breton";}s:6:"native";a:1:{i:0;s:9:"Brezhoneg";}}s:2:"bg";a:2:{s:3:"int";a:1:{i:0;s:9:"Bulgarian";}s:6:"native";a:1:{i:0;s:27:"български език";}}s:2:"my";a:2:{s:3:"int";a:1:{i:0;s:7:"Burmese";}s:6:"native";a:1:{i:0;s:15:"ဗမာစာ";}}s:2:"ca";a:2:{s:3:"int";a:2:{i:0;s:7:"Catalan";i:1;s:9:"Valencian";}s:6:"native";a:1:{i:0;s:7:"Català";}}s:2:"ch";a:2:{s:3:"int";a:1:{i:0;s:8:"Chamorro";}s:6:"native";a:1:{i:0;s:7:"Chamoru";}}s:2:"ce";a:2:{s:3:"int";a:1:{i:0;s:7:"Chechen";}s:6:"native";a:1:{i:0;s:23:"нохчийн мотт";}}s:2:"zh";a:2:{s:3:"int";a:1:{i:0;s:7:"Chinese";}s:6:"native";a:3:{i:0;s:19:"中文 (Zhōngwén)";i:1;s:6:"汉语";i:2;s:6:"漢語";}}s:2:"cu";a:2:{s:3:"int";a:5:{i:0;s:13:"Church Slavic";i:1;s:15:"Church Slavonic";i:2;s:13:"Old Bulgarian";i:3;s:19:"Old Church Slavonic";i:4;s:12:"Old Slavonic";}s:6:"native";a:5:{i:0;s:13:"Church Slavic";i:1;s:15:"Church Slavonic";i:2;s:13:"Old Bulgarian";i:3;s:19:"Old Church Slavonic";i:4;s:12:"Old Slavonic";}}s:2:"cv";a:2:{s:3:"int";a:1:{i:0;s:7:"Chuvash";}s:6:"native";a:1:{i:0;s:21:"чӑваш чӗлхи";}}s:2:"kw";a:2:{s:3:"int";a:1:{i:0;s:7:"Cornish";}s:6:"native";a:1:{i:0;s:8:"Kernewek";}}s:2:"co";a:2:{s:3:"int";a:1:{i:0;s:8:"Corsican";}s:6:"native";a:2:{i:0;s:5:"Corsu";i:1;s:12:"Lingua Corsa";}}s:2:"cr";a:2:{s:3:"int";a:1:{i:0;s:4:"Cree";}s:6:"native";a:1:{i:0;s:21:"ᓀᐦᐃᔭᐍᐏᐣ";}}s:2:"cs";a:2:{s:3:"int";a:1:{i:0;s:5:"Czech";}s:6:"native";a:2:{i:0;s:6:"česky";i:1;s:9:"čeština";}}s:2:"da";a:2:{s:3:"int";a:1:{i:0;s:6:"Danish";}s:6:"native";a:1:{i:0;s:5:"Dansk";}}s:2:"dv";a:2:{s:3:"int";a:3:{i:0;s:7:"Dhivehi";i:1;s:6:"Divehi";i:2;s:9:"Maldivian";}s:6:"native";a:3:{i:0;s:7:"Dhivehi";i:1;s:6:"Divehi";i:2;s:9:"Maldivian";}}s:2:"nl";a:2:{s:3:"int";a:2:{i:0;s:5:"Dutch";i:1;s:7:"Flemish";}s:6:"native";a:2:{i:0;s:5:"Dutch";i:1;s:7:"Flemish";}}s:2:"dz";a:2:{s:3:"int";a:1:{i:0;s:8:"Dzongkha";}s:6:"native";a:1:{i:0;s:8:"Dzongkha";}}s:2:"en";a:2:{s:3:"int";a:1:{i:0;s:7:"English";}s:6:"native";a:1:{i:0;s:7:"English";}}s:2:"eo";a:2:{s:3:"int";a:1:{i:0;s:9:"Esperanto";}s:6:"native";a:1:{i:0;s:9:"Esperanto";}}s:2:"et";a:2:{s:3:"int";a:1:{i:0;s:8:"Estonian";}s:6:"native";a:2:{i:0;s:5:"Eesti";i:1;s:10:"Eesti Keel";}}s:2:"ee";a:2:{s:3:"int";a:1:{i:0;s:3:"Ewe";}s:6:"native";a:1:{i:0;s:7:"Eʋegbe";}}s:2:"fo";a:2:{s:3:"int";a:1:{i:0;s:7:"Faroese";}s:6:"native";a:1:{i:0;s:9:"Føroyskt";}}s:2:"fj";a:2:{s:3:"int";a:1:{i:0;s:6:"Fijian";}s:6:"native";a:1:{i:0;s:13:"Vosa Vakaviti";}}s:2:"fi";a:2:{s:3:"int";a:1:{i:0;s:7:"Finnish";}s:6:"native";a:2:{i:0;s:12:"Suomen Kieli";i:1;s:5:"Suomi";}}s:2:"fr";a:2:{s:3:"int";a:1:{i:0;s:6:"French";}s:6:"native";a:2:{i:0;s:9:"Français";i:1;s:17:"Langue Française";}}s:2:"fy";a:2:{s:3:"int";a:1:{i:0;s:15:"Western Frisian";}s:6:"native";a:1:{i:0;s:5:"Frysk";}}s:2:"ff";a:2:{s:3:"int";a:1:{i:0;s:5:"Fulah";}s:6:"native";a:1:{i:0;s:5:"Fulah";}}s:2:"ka";a:2:{s:3:"int";a:1:{i:0;s:8:"Georgian";}s:6:"native";a:1:{i:0;s:21:"ქართული";}}s:2:"de";a:2:{s:3:"int";a:1:{i:0;s:6:"German";}s:6:"native";a:1:{i:0;s:7:"Deutsch";}}s:2:"gd";a:2:{s:3:"int";a:2:{i:0;s:6:"Gaelic";i:1;s:15:"Scottish Gaelic";}s:6:"native";a:2:{i:0;s:6:"Gaelic";i:1;s:15:"Scottish Gaelic";}}s:2:"ga";a:2:{s:3:"int";a:1:{i:0;s:5:"Irish";}s:6:"native";a:1:{i:0;s:7:"Gaeilge";}}s:2:"gl";a:2:{s:3:"int";a:1:{i:0;s:8:"Galician";}s:6:"native";a:1:{i:0;s:6:"Galego";}}s:2:"gv";a:2:{s:3:"int";a:1:{i:0;s:4:"Manx";}s:6:"native";a:2:{i:0;s:5:"Gaelg";i:1;s:6:"Gailck";}}s:2:"el";a:2:{s:3:"int";a:1:{i:0;s:20:"Greek Modern (1453-)";}s:6:"native";a:1:{i:0;s:20:"Greek Modern (1453-)";}}s:2:"gn";a:2:{s:3:"int";a:1:{i:0;s:7:"Guarani";}s:6:"native";a:1:{i:0;s:7:"Guarani";}}s:2:"gu";a:2:{s:3:"int";a:1:{i:0;s:8:"Gujarati";}s:6:"native";a:1:{i:0;s:21:"ગુજરાતી";}}s:2:"ht";a:2:{s:3:"int";a:2:{i:0;s:7:"Haitian";i:1;s:14:"Haitian Creole";}s:6:"native";a:1:{i:0;s:15:"Kreyòl Ayisyen";}}s:2:"ha";a:2:{s:3:"int";a:1:{i:0;s:5:"Hausa";}s:6:"native";a:2:{i:0;s:5:"Hausa";i:1;s:12:"هَوُسَ";}}s:2:"he";a:2:{s:3:"int";a:1:{i:0;s:6:"Hebrew";}s:6:"native";a:1:{i:0;s:6:"Hebrew";}}s:2:"hz";a:2:{s:3:"int";a:1:{i:0;s:6:"Herero";}s:6:"native";a:1:{i:0;s:10:"Otjiherero";}}s:2:"hi";a:2:{s:3:"int";a:1:{i:0;s:5:"Hindi";}s:6:"native";a:2:{i:0;s:15:"हिंदी";i:1;s:18:"हिन्दी";}}s:2:"ho";a:2:{s:3:"int";a:1:{i:0;s:9:"Hiri Motu";}s:6:"native";a:1:{i:0;s:9:"Hiri Motu";}}s:2:"hr";a:2:{s:3:"int";a:1:{i:0;s:8:"Croatian";}s:6:"native";a:1:{i:0;s:8:"Hrvatski";}}s:2:"hu";a:2:{s:3:"int";a:1:{i:0;s:9:"Hungarian";}s:6:"native";a:1:{i:0;s:6:"Magyar";}}s:2:"ig";a:2:{s:3:"int";a:1:{i:0;s:4:"Igbo";}s:6:"native";a:1:{i:0;s:14:"Asụsụ Igbo";}}s:2:"is";a:2:{s:3:"int";a:1:{i:0;s:9:"Icelandic";}s:6:"native";a:1:{i:0;s:9:"Íslenska";}}s:2:"io";a:2:{s:3:"int";a:1:{i:0;s:3:"Ido";}s:6:"native";a:1:{i:0;s:3:"Ido";}}s:2:"ii";a:2:{s:3:"int";a:2:{i:0;s:5:"Nuosu";i:1;s:10:"Sichuan Yi";}s:6:"native";a:2:{i:0;s:5:"Nuosu";i:1;s:10:"Sichuan Yi";}}s:2:"iu";a:2:{s:3:"int";a:1:{i:0;s:9:"Inuktitut";}s:6:"native";a:1:{i:0;s:18:"ᐃᓄᒃᑎᑐᑦ";}}s:2:"ie";a:2:{s:3:"int";a:2:{i:0;s:11:"Interlingue";i:1;s:10:"Occidental";}s:6:"native";a:2:{i:0;s:11:"Interlingue";i:1;s:10:"Occidental";}}s:2:"ia";a:2:{s:3:"int";a:1:{i:0;s:58:"Interlingua (International Auxiliary Language Association)";}s:6:"native";a:1:{i:0;s:58:"Interlingua (International Auxiliary Language Association)";}}s:2:"id";a:2:{s:3:"int";a:1:{i:0;s:10:"Indonesian";}s:6:"native";a:1:{i:0;s:16:"Bahasa Indonesia";}}s:2:"ik";a:2:{s:3:"int";a:1:{i:0;s:7:"Inupiaq";}s:6:"native";a:2:{i:0;s:8:"Iñupiaq";i:1;s:10:"Iñupiatun";}}s:2:"it";a:2:{s:3:"int";a:1:{i:0;s:7:"Italian";}s:6:"native";a:1:{i:0;s:8:"Italiano";}}s:2:"jv";a:2:{s:3:"int";a:1:{i:0;s:8:"Javanese";}s:6:"native";a:1:{i:0;s:9:"Basa Jawa";}}s:2:"ja";a:2:{s:3:"int";a:1:{i:0;s:8:"Japanese";}s:6:"native";a:1:{i:0;s:42:"日本語 (にほんご／にっぽんご)";}}s:2:"kl";a:2:{s:3:"int";a:2:{i:0;s:11:"Greenlandic";i:1;s:11:"Kalaallisut";}s:6:"native";a:2:{i:0;s:11:"Greenlandic";i:1;s:11:"Kalaallisut";}}s:2:"kn";a:2:{s:3:"int";a:1:{i:0;s:7:"Kannada";}s:6:"native";a:1:{i:0;s:15:"ಕನ್ನಡ";}}s:2:"ks";a:2:{s:3:"int";a:1:{i:0;s:8:"Kashmiri";}s:6:"native";a:2:{i:0;s:15:"كشميري‎";i:1;s:21:"कश्मीरी";}}s:2:"kr";a:2:{s:3:"int";a:1:{i:0;s:6:"Kanuri";}s:6:"native";a:1:{i:0;s:6:"Kanuri";}}s:2:"kk";a:2:{s:3:"int";a:1:{i:0;s:6:"Kazakh";}s:6:"native";a:1:{i:0;s:19:"Қазақ тілі";}}s:2:"km";a:2:{s:3:"int";a:1:{i:0;s:13:"Central Khmer";}s:6:"native";a:1:{i:0;s:13:"Central Khmer";}}s:2:"ki";a:2:{s:3:"int";a:2:{i:0;s:6:"Gikuyu";i:1;s:6:"Kikuyu";}s:6:"native";a:2:{i:0;s:6:"Gikuyu";i:1;s:6:"Kikuyu";}}s:2:"rw";a:2:{s:3:"int";a:1:{i:0;s:11:"Kinyarwanda";}s:6:"native";a:1:{i:0;s:12:"Ikinyarwanda";}}s:2:"ky";a:2:{s:3:"int";a:2:{i:0;s:7:"Kirghiz";i:1;s:6:"Kyrgyz";}s:6:"native";a:2:{i:0;s:7:"Kirghiz";i:1;s:6:"Kyrgyz";}}s:2:"kv";a:2:{s:3:"int";a:1:{i:0;s:4:"Komi";}s:6:"native";a:1:{i:0;s:15:"коми кыв";}}s:2:"kg";a:2:{s:3:"int";a:1:{i:0;s:5:"Kongo";}s:6:"native";a:1:{i:0;s:7:"KiKongo";}}s:2:"ko";a:2:{s:3:"int";a:1:{i:0;s:6:"Korean";}s:6:"native";a:2:{i:0;s:21:"조선말 (朝鮮語)";i:1;s:21:"한국어 (韓國語)";}}s:2:"kj";a:2:{s:3:"int";a:2:{i:0;s:8:"Kuanyama";i:1;s:8:"Kwanyama";}s:6:"native";a:2:{i:0;s:8:"Kuanyama";i:1;s:8:"Kwanyama";}}s:2:"ku";a:2:{s:3:"int";a:1:{i:0;s:7:"Kurdish";}s:6:"native";a:2:{i:0;s:6:"Kurdî";i:1;s:13:"كوردی‎";}}s:2:"lo";a:2:{s:3:"int";a:1:{i:0;s:3:"Lao";}s:6:"native";a:1:{i:0;s:21:"ພາສາລາວ";}}s:2:"la";a:2:{s:3:"int";a:1:{i:0;s:5:"Latin";}s:6:"native";a:2:{i:0;s:6:"Latine";i:1;s:13:"Lingua Latina";}}s:2:"lv";a:2:{s:3:"int";a:1:{i:0;s:7:"Latvian";}s:6:"native";a:1:{i:0;s:16:"Latviešu Valoda";}}s:2:"li";a:2:{s:3:"int";a:3:{i:0;s:9:"Limburgan";i:1;s:9:"Limburger";i:2;s:10:"Limburgish";}s:6:"native";a:3:{i:0;s:9:"Limburgan";i:1;s:9:"Limburger";i:2;s:10:"Limburgish";}}s:2:"ln";a:2:{s:3:"int";a:1:{i:0;s:7:"Lingala";}s:6:"native";a:1:{i:0;s:8:"Lingála";}}s:2:"lt";a:2:{s:3:"int";a:1:{i:0;s:10:"Lithuanian";}s:6:"native";a:1:{i:0;s:15:"Lietuvių Kalba";}}s:2:"lb";a:2:{s:3:"int";a:2:{i:0;s:13:"Letzeburgesch";i:1;s:13:"Luxembourgish";}s:6:"native";a:2:{i:0;s:13:"Letzeburgesch";i:1;s:13:"Luxembourgish";}}s:2:"lu";a:2:{s:3:"int";a:1:{i:0;s:12:"Luba-Katanga";}s:6:"native";a:1:{i:0;s:0:"";}}s:2:"lg";a:2:{s:3:"int";a:1:{i:0;s:5:"Ganda";}s:6:"native";a:1:{i:0;s:5:"Ganda";}}s:2:"mk";a:2:{s:3:"int";a:1:{i:0;s:10:"Macedonian";}s:6:"native";a:1:{i:0;s:31:"македонски јазик";}}s:2:"mh";a:2:{s:3:"int";a:1:{i:0;s:11:"Marshallese";}s:6:"native";a:1:{i:0;s:14:"Kajin M̧ajeļ";}}s:2:"ml";a:2:{s:3:"int";a:1:{i:0;s:9:"Malayalam";}s:6:"native";a:1:{i:0;s:18:"മലയാളം";}}s:2:"mi";a:2:{s:3:"int";a:1:{i:0;s:5:"Maori";}s:6:"native";a:1:{i:0;s:5:"Maori";}}s:2:"mr";a:2:{s:3:"int";a:1:{i:0;s:7:"Marathi";}s:6:"native";a:1:{i:0;s:7:"Marathi";}}s:2:"ms";a:2:{s:3:"int";a:1:{i:0;s:5:"Malay";}s:6:"native";a:2:{i:0;s:13:"Bahasa Melayu";i:1;s:22:"بهاس ملايو‎";}}s:2:"mg";a:2:{s:3:"int";a:1:{i:0;s:8:"Malagasy";}s:6:"native";a:1:{i:0;s:15:"Malagasy Fiteny";}}s:2:"mt";a:2:{s:3:"int";a:1:{i:0;s:7:"Maltese";}s:6:"native";a:1:{i:0;s:5:"Malti";}}s:2:"mn";a:2:{s:3:"int";a:1:{i:0;s:9:"Mongolian";}s:6:"native";a:1:{i:0;s:12:"монгол";}}s:2:"na";a:2:{s:3:"int";a:1:{i:0;s:5:"Nauru";}s:6:"native";a:1:{i:0;s:16:"Ekakairũ Naoero";}}s:2:"nv";a:2:{s:3:"int";a:2:{i:0;s:6:"Navaho";i:1;s:6:"Navajo";}s:6:"native";a:2:{i:0;s:6:"Navaho";i:1;s:6:"Navajo";}}s:2:"nr";a:2:{s:3:"int";a:2:{i:0;s:7:"Ndebele";i:1;s:5:"South";}s:6:"native";a:2:{i:0;s:7:"Ndebele";i:1;s:5:"South";}}s:2:"nd";a:2:{s:3:"int";a:2:{i:0;s:7:"Ndebele";i:1;s:5:"North";}s:6:"native";a:2:{i:0;s:7:"Ndebele";i:1;s:5:"North";}}s:2:"ng";a:2:{s:3:"int";a:1:{i:0;s:6:"Ndonga";}s:6:"native";a:1:{i:0;s:6:"Owambo";}}s:2:"ne";a:2:{s:3:"int";a:1:{i:0;s:6:"Nepali";}s:6:"native";a:1:{i:0;s:18:"नेपाली";}}s:2:"nn";a:2:{s:3:"int";a:2:{i:0;s:9:"Norwegian";i:1;s:7:"Nynorsk";}s:6:"native";a:2:{i:0;s:9:"Norwegian";i:1;s:7:"Nynorsk";}}s:2:"nb";a:2:{s:3:"int";a:2:{i:0;s:7:"Bokmål";i:1;s:9:"Norwegian";}s:6:"native";a:2:{i:0;s:7:"Bokmål";i:1;s:9:"Norwegian";}}s:2:"no";a:2:{s:3:"int";a:1:{i:0;s:9:"Norwegian";}s:6:"native";a:1:{i:0;s:5:"Norsk";}}s:2:"ny";a:2:{s:3:"int";a:3:{i:0;s:5:"Chewa";i:1;s:8:"Chichewa";i:2;s:6:"Nyanja";}s:6:"native";a:2:{i:0;s:9:"ChiCheŵa";i:1;s:9:"Chinyanja";}}s:2:"oc";a:2:{s:3:"int";a:2:{i:0;s:19:"Occitan (post 1500)";i:1;s:10:"Provençal";}s:6:"native";a:2:{i:0;s:19:"Occitan (post 1500)";i:1;s:10:"Provençal";}}s:2:"oj";a:2:{s:3:"int";a:1:{i:0;s:6:"Ojibwa";}s:6:"native";a:1:{i:0;s:6:"Ojibwa";}}s:2:"or";a:2:{s:3:"int";a:1:{i:0;s:5:"Oriya";}s:6:"native";a:1:{i:0;s:15:"ଓଡ଼ିଆ";}}s:2:"om";a:2:{s:3:"int";a:1:{i:0;s:5:"Oromo";}s:6:"native";a:1:{i:0;s:12:"Afaan Oromoo";}}s:2:"os";a:2:{s:3:"int";a:2:{i:0;s:8:"Ossetian";i:1;s:7:"Ossetic";}s:6:"native";a:2:{i:0;s:8:"Ossetian";i:1;s:7:"Ossetic";}}s:2:"pa";a:2:{s:3:"int";a:2:{i:0;s:7:"Panjabi";i:1;s:7:"Punjabi";}s:6:"native";a:2:{i:0;s:7:"Panjabi";i:1;s:7:"Punjabi";}}s:2:"fa";a:2:{s:3:"int";a:1:{i:0;s:7:"Persian";}s:6:"native";a:1:{i:0;s:10:"فارسی";}}s:2:"pi";a:2:{s:3:"int";a:1:{i:0;s:4:"Pali";}s:6:"native";a:1:{i:0;s:4:"Pali";}}s:2:"pl";a:2:{s:3:"int";a:1:{i:0;s:6:"Polish";}s:6:"native";a:1:{i:0;s:6:"Polski";}}s:2:"pt";a:2:{s:3:"int";a:1:{i:0;s:10:"Portuguese";}s:6:"native";a:1:{i:0;s:10:"Português";}}s:2:"ps";a:2:{s:3:"int";a:2:{i:0;s:6:"Pashto";i:1;s:6:"Pushto";}s:6:"native";a:2:{i:0;s:6:"Pashto";i:1;s:6:"Pushto";}}s:2:"qu";a:2:{s:3:"int";a:1:{i:0;s:7:"Quechua";}s:6:"native";a:2:{i:0;s:6:"Kichwa";i:1;s:9:"Runa Simi";}}s:2:"rm";a:2:{s:3:"int";a:1:{i:0;s:7:"Romansh";}s:6:"native";a:1:{i:0;s:18:"Rumantsch Grischun";}}s:2:"ro";a:2:{s:3:"int";a:3:{i:0;s:9:"Moldavian";i:1;s:8:"Moldovan";i:2;s:8:"Romanian";}s:6:"native";a:3:{i:0;s:9:"Moldavian";i:1;s:8:"Moldovan";i:2;s:8:"Romanian";}}s:2:"rn";a:2:{s:3:"int";a:1:{i:0;s:5:"Rundi";}s:6:"native";a:1:{i:0;s:5:"Rundi";}}s:2:"ru";a:2:{s:3:"int";a:1:{i:0;s:7:"Russian";}s:6:"native";a:1:{i:0;s:23:"русский язык";}}s:2:"sg";a:2:{s:3:"int";a:1:{i:0;s:5:"Sango";}s:6:"native";a:1:{i:0;s:19:"Yângâ Tî Sängö";}}s:2:"sa";a:2:{s:3:"int";a:1:{i:0;s:8:"Sanskrit";}s:6:"native";a:1:{i:0;s:8:"Sanskrit";}}s:2:"si";a:2:{s:3:"int";a:2:{i:0;s:7:"Sinhala";i:1;s:9:"Sinhalese";}s:6:"native";a:2:{i:0;s:7:"Sinhala";i:1;s:9:"Sinhalese";}}s:2:"sk";a:2:{s:3:"int";a:1:{i:0;s:6:"Slovak";}s:6:"native";a:1:{i:0;s:11:"Slovenčina";}}s:2:"sl";a:2:{s:3:"int";a:1:{i:0;s:9:"Slovenian";}s:6:"native";a:1:{i:0;s:9:"Slovenian";}}s:2:"se";a:2:{s:3:"int";a:1:{i:0;s:13:"Northern Sami";}s:6:"native";a:1:{i:0;s:16:"Davvisámegiella";}}s:2:"sm";a:2:{s:3:"int";a:1:{i:0;s:6:"Samoan";}s:6:"native";a:1:{i:0;s:16:"Gagana Faa Samoa";}}s:2:"sn";a:2:{s:3:"int";a:1:{i:0;s:5:"Shona";}s:6:"native";a:1:{i:0;s:8:"ChiShona";}}s:2:"sd";a:2:{s:3:"int";a:1:{i:0;s:6:"Sindhi";}s:6:"native";a:2:{i:0;s:24:"سنڌي، سندھی‎";i:1;s:18:"सिन्धी";}}s:2:"so";a:2:{s:3:"int";a:1:{i:0;s:6:"Somali";}s:6:"native";a:2:{i:0;s:11:"Af Soomaali";i:1;s:10:"Soomaaliga";}}s:2:"st";a:2:{s:3:"int";a:2:{i:0;s:5:"Sotho";i:1;s:8:"Southern";}s:6:"native";a:2:{i:0;s:5:"Sotho";i:1;s:8:"Southern";}}s:2:"es";a:2:{s:3:"int";a:2:{i:0;s:9:"Castilian";i:1;s:7:"Spanish";}s:6:"native";a:2:{i:0;s:10:"Castellano";i:1;s:8:"Español";}}s:2:"sc";a:2:{s:3:"int";a:1:{i:0;s:9:"Sardinian";}s:6:"native";a:1:{i:0;s:5:"Sardu";}}s:2:"sr";a:2:{s:3:"int";a:1:{i:0;s:7:"Serbian";}s:6:"native";a:1:{i:0;s:23:"српски језик";}}s:2:"ss";a:2:{s:3:"int";a:1:{i:0;s:5:"Swati";}s:6:"native";a:1:{i:0;s:7:"SiSwati";}}s:2:"su";a:2:{s:3:"int";a:1:{i:0;s:9:"Sundanese";}s:6:"native";a:1:{i:0;s:10:"Basa Sunda";}}s:2:"sw";a:2:{s:3:"int";a:1:{i:0;s:7:"Swahili";}s:6:"native";a:1:{i:0;s:9:"Kiswahili";}}s:2:"sv";a:2:{s:3:"int";a:1:{i:0;s:7:"Swedish";}s:6:"native";a:1:{i:0;s:7:"Svenska";}}s:2:"ty";a:2:{s:3:"int";a:1:{i:0;s:8:"Tahitian";}s:6:"native";a:1:{i:0;s:10:"Reo Tahiti";}}s:2:"ta";a:2:{s:3:"int";a:1:{i:0;s:5:"Tamil";}s:6:"native";a:1:{i:0;s:15:"தமிழ்";}}s:2:"tt";a:2:{s:3:"int";a:1:{i:0;s:5:"Tatar";}s:6:"native";a:3:{i:0;s:8:"Tatarça";i:1;s:14:"татарча";i:2;s:17:"تاتارچا‎";}}s:2:"te";a:2:{s:3:"int";a:1:{i:0;s:6:"Telugu";}s:6:"native";a:1:{i:0;s:18:"తెలుగు";}}s:2:"tg";a:2:{s:3:"int";a:1:{i:0;s:5:"Tajik";}s:6:"native";a:3:{i:0;s:8:"Toğikī";i:1;s:12:"тоҷикӣ";i:2;s:15:"تاجیکی‎";}}s:2:"tl";a:2:{s:3:"int";a:1:{i:0;s:7:"Tagalog";}s:6:"native";a:2:{i:0;s:14:"Wikang Tagalog";i:1;s:34:"ᜏᜒᜃᜅ᜔ ᜆᜄᜎᜓᜄ᜔";}}s:2:"th";a:2:{s:3:"int";a:1:{i:0;s:4:"Thai";}s:6:"native";a:1:{i:0;s:9:"ไทย";}}s:2:"bo";a:2:{s:3:"int";a:1:{i:0;s:7:"Tibetan";}s:6:"native";a:1:{i:0;s:7:"Tibetan";}}s:2:"ti";a:2:{s:3:"int";a:1:{i:0;s:8:"Tigrinya";}s:6:"native";a:1:{i:0;s:12:"ትግርኛ";}}s:2:"to";a:2:{s:3:"int";a:1:{i:0;s:21:"Tonga (Tonga Islands)";}s:6:"native";a:1:{i:0;s:10:"Faka Tonga";}}s:2:"tn";a:2:{s:3:"int";a:1:{i:0;s:6:"Tswana";}s:6:"native";a:1:{i:0;s:8:"Setswana";}}s:2:"ts";a:2:{s:3:"int";a:1:{i:0;s:6:"Tsonga";}s:6:"native";a:1:{i:0;s:8:"Xitsonga";}}s:2:"tk";a:2:{s:3:"int";a:1:{i:0;s:7:"Turkmen";}s:6:"native";a:2:{i:0;s:8:"Türkmen";i:1;s:14:"Түркмен";}}s:2:"tr";a:2:{s:3:"int";a:1:{i:0;s:7:"Turkish";}s:6:"native";a:1:{i:0;s:8:"Türkçe";}}s:2:"tw";a:2:{s:3:"int";a:1:{i:0;s:3:"Twi";}s:6:"native";a:1:{i:0;s:3:"Twi";}}s:2:"ug";a:2:{s:3:"int";a:2:{i:0;s:6:"Uighur";i:1;s:6:"Uyghur";}s:6:"native";a:2:{i:0;s:6:"Uighur";i:1;s:6:"Uyghur";}}s:2:"uk";a:2:{s:3:"int";a:1:{i:0;s:9:"Ukrainian";}s:6:"native";a:1:{i:0;s:20:"українська";}}s:2:"ur";a:2:{s:3:"int";a:1:{i:0;s:4:"Urdu";}s:6:"native";a:1:{i:0;s:8:"اردو";}}s:2:"uz";a:2:{s:3:"int";a:1:{i:0;s:5:"Uzbek";}s:6:"native";a:3:{i:0;s:4:"Zbek";i:1;s:10:"Ўзбек";i:2;s:15:"أۇزبېك‎";}}s:2:"ve";a:2:{s:3:"int";a:1:{i:0;s:5:"Venda";}s:6:"native";a:1:{i:0;s:11:"Tshivenḓa";}}s:2:"vi";a:2:{s:3:"int";a:1:{i:0;s:10:"Vietnamese";}s:6:"native";a:1:{i:0;s:14:"Tiếng Việt";}}s:2:"vo";a:2:{s:3:"int";a:1:{i:0;s:8:"Volapük";}s:6:"native";a:1:{i:0;s:8:"Volapük";}}s:2:"cy";a:2:{s:3:"int";a:1:{i:0;s:5:"Welsh";}s:6:"native";a:1:{i:0;s:7:"Cymraeg";}}s:2:"wa";a:2:{s:3:"int";a:1:{i:0;s:7:"Walloon";}s:6:"native";a:1:{i:0;s:5:"Walon";}}s:2:"wo";a:2:{s:3:"int";a:1:{i:0;s:5:"Wolof";}s:6:"native";a:1:{i:0;s:6:"Wollof";}}s:2:"xh";a:2:{s:3:"int";a:1:{i:0;s:5:"Xhosa";}s:6:"native";a:1:{i:0;s:8:"IsiXhosa";}}s:2:"yi";a:2:{s:3:"int";a:1:{i:0;s:7:"Yiddish";}s:6:"native";a:1:{i:0;s:12:"ייִדיש";}}s:2:"yo";a:2:{s:3:"int";a:1:{i:0;s:6:"Yoruba";}s:6:"native";a:1:{i:0;s:8:"Yorùbá";}}s:2:"za";a:2:{s:3:"int";a:2:{i:0;s:6:"Chuang";i:1;s:6:"Zhuang";}s:6:"native";a:2:{i:0;s:6:"Chuang";i:1;s:6:"Zhuang";}}s:2:"zu";a:2:{s:3:"int";a:1:{i:0;s:4:"Zulu";}s:6:"native";a:1:{i:0;s:4:"Zulu";}}}');
}


// }}}
