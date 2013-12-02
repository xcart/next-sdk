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
 * Decorate class by file path
 */

require_once __DIR__ . '/core.php';

// Get arguments
$language = macro_get_named_argument('language');
$delimiter = macro_get_named_argument('delimiter');

// {{{ Check arguments

// --language
if (!$language) {
    macro_error('--language is empty');

} elseif (!preg_match('/^[a-z]{2}$/iSs', $language)) {
    macro_error('--language has wrong langauge code');
}

$languageModel = \XLite\Core\Database::getRepo('XLite\Model\Language')->findOneByCode($language);
if (!$languageModel) {
    macro_error('--language has undefined langauge code');
}

// --delimiter

$delimiter = $delimiter ?: ',';

// }}}

// Generate CSV

$fp = fopen('php://output', 'wb');

foreach (\XLite\Core\Database::getRepo('XLite\Model\LanguageLabel')->findLabelsByCode($language) as $name => $label) {
    fputcsv($fp, array($name, $label), $delimiter);
}
fclose($fp);

die(0);

/**
 * Help
 */
function macro_help()
{
    return <<<HELP
Usage: get-language-csv.php --language=<language_code> [--delimiter=<delimiter>]

Example: .dev/macro/decorate.php --language=en

Arguments:

    --language=<language_code>
        Language 2-alpha code

    --delimiter=<delimiter>
        Columns delimiter. Default - ,

HELP;
}

