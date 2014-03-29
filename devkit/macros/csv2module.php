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
    $author    = csv2m_get_named_argument('author');
    $module    = csv2m_get_named_argument('module');
    $path      = csv2m_get_named_argument('path');
    $code      = csv2m_get_named_argument('code');
    $delimiter = csv2m_get_named_argument('delimiter') ?: ',';
    $core      = csv2m_get_named_argument('core') ?: '5.0';

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

    $errors = csv2module($path, $destPath, $author, $module, $code, $delimiter, $core);
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

function csv2module($path, &$destPath, $author, $module, $code, $delimiter = ',', $core = '5.0')
{
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
        return '$author';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return '$module';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.0';
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
        return '$module';
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
                'Name'           => $module,
                'Author'         => $author,
                'IconLink'       => null,
                'Description'    => $module,
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

    --author=<author>
        Module author

    --module=<module>
        Module name. Default - <code>Translation

    --code=<language_code>
        2-character language code

    --delimiter=<delimiter>
        Column delimiter. Default - ","

    --core=<core major version>
        X-Cart 5 core major version. Default - 5.0

HELP;

}

// }}}
