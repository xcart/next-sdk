<?php
// vim: set ts=4 sw=4 sts=4 et:

define('OFFERS_DONT_SHOW_NEW', 1);
define('STORE_NAVIGATION_SCRIPT', 'Y');

require './top.inc.php';
require './init.php';

set_time_limit(0);

// Default X-Cart 5 import column delimiter
define('XCN_EXPORT_DELIMITER', ',');

// Authorization key
define('XCN_EXPORT_KEY', 'testsuperkey');

// {{{ Authorization

if (8 > strlen(constant('XCN_EXPORT_KEY'))) {
    print ('Key too short.' . PHP_EOL);
    die(1);

} elseif ($_GET['key'] != constant('XCN_EXPORT_KEY')) {
    print ('Access denied.' . PHP_EOL);
    die(2);
}

// }}}

// {{{ Export

if (php_sapi_name() != 'cli') {
    header('Content-Type: text/csv;charset=UTF-8');
    header('Content-Disposition: attachment; filename="products.csv"; modification-date="' . date('r') . ';');
}

xcne_print_output(xcne_get_headers());
$stm = xcne_get_products_query();
do {
    $row = xcne_assemble_csv_row($stm);
    if ($row) {
        xcne_print_output($row);
    }

} while ($row);

die(0);

// }}}

// {{{ Functions

// {{{ CSV formatters and output

/**
 * Print output row
 *
 * @param array $data Row
 *
 * @return void
 */
function xcne_print_output($data)
{
    if (function_exists('fputcsv')) {
        $fp = fopen('php://output', 'w');
        fputcsv($fp, $data, XCN_EXPORT_DELIMITER);
        fclose($fp);

    } else {
        print array_map('xcne_format_cell', $data) . PHP_EOL;
    }
}

/**
 * Convert charset
 *
 * @param string $string String
 *
 * @return string
 */
function xcne_convert_charset($string)
{
    static $charset;

    if (4.5 > xcne_version() && function_exists('iconv')) {
        if (empty($_GET['charset'])) {
            $charset = 'iso-8859-1';
        }

        $tmp = iconv($charset, 'UTF-8', $string);
        if (false !== $tmp) {
            $string = $tmp;
        }
    }

    return $string;
}

/**
 * Get headers list
 *
 * @return array
 */
function xcne_get_headers()
{
    return array(
        'sku',
        'name_en',
        'description_en',
        'briefDescription_en',
        'metaTags_en',
        'metaDesc_en',
        'metaTitle_en',
        'price',
        'enabled',
        'weight',
        'freeShipping',
        'cleanURL',
        'arrivalDate',
        'openGraphMeta',
        'useCustomOpenGraphMeta',
        'categories',
        'images',
        'stockLevel',
        'lowLimitEnabled',
        'lowLimitLevel',
        'productClass',
    );
}

/**
 * Format cell
 *
 * @param string $value Cell value
 *
 * @return string
 */
function xcne_format_cell($value)
{
    $value = xcne_convert_charset($value);

    $value = preg_replace("/\r\n|\n|\r/Ss", ' ', $value);
    $value = '"' . str_replace('"', '""', $value) . '"';
    if (substr($value, -2) == '\"' && preg_match('/[^\\\](\\\+)"$/Ss', $value, $preg) && strlen($preg[1]) % 2 != 0) {
        $value = substr($value, 0, -2) . "\\" . substr($value, -2);
    }

    return $value;
}

/**
 * Assemble CSV row
 *
 * @param resource $stm MySQL resource
 *
 * @return array
 */
function xcne_assemble_csv_row($stm)
{
    global $sql_tbl, $current_location, $config;

    $row = array();

    $data = db_fetch_array($stm);
    if ($data) {

        // Price
        $tmp = 4 < xcne_version()
            ? func_query_first('SELECT p.price FROM ' . $sql_tbl['pricing'] . ' AS p INNER JOIN ' . $sql_tbl['quick_prices'] . ' AS qp ON p.priceid = qp.priceid WHERE qp.productid = ' . $data['productid'])
            : func_query_first('SELECT MIN(price) AS price FROM ' . $sql_tbl['pricing'] . ' WHERE productid = ' . $data['productid'] . ' AND quantity = 1 AND membership = "" AND variantid = 0');
        if ($tmp) {
            $data['price'] = $tmp['price'];

        } else {
            xcne_error('Product price did not found');
        }

        // Clean URL
        $data['clean_url'] = '';
        if (isset($sql_tbl['clean_urls'])) {
            $tmp = func_query_first('SELECT clean_url FROM ' . $sql_tbl['clean_urls'] . ' WHERE resource_type = "P" AND resource_id = ' . $data['productid']);
            if ($tmp) {
                $data['clean_url'] = str_replace('.', '' ,$tmp['clean_url']);
            }
        }

        // Multilanguage data
        if (!isset($data['product'])) {
            if (isset($sql_tbl['products_lng_current'])) {
                $lng = func_query_first('SELECT * FROM ' . $sql_tbl['products_lng_current'] . ' WHERE productid = ' . $data['productid']);
                $data['product'] = $lng['product'];
                $data['descr'] = $lng['descr'];
                $data['fulldescr'] = $lng['fulldescr'];
                $data['keywords'] = $lng['keywords'];

            } else {
                xcne_error('Product name did not found');
            }
        }

        // Categories
        xcne_load('category');
        $data['categories'] = array();
        $tmp = func_query('SELECT c.categoryid FROM ' . $sql_tbl['categories'] . ' AS c INNER JOIN ' . $sql_tbl['products_categories'] . ' AS pc ON c.categoryid = pc.categoryid WHERE pc.productid = ' . $data['productid']);
        foreach ($tmp as $c) {
            $data['categories'][] = xcne_get_category_path($c['categoryid']);
        }
        $data['categories'] = implode(' && ', $data['categories']);

        // Images
        $data['images'] = array();
        if (isset($sql_tbl['images_P'])) {
            $tmp = func_query_first('SELECT * FROM ' . $sql_tbl['images_T'] . ' WHERE id = ' . $data['productid']);
            if ($tmp) {
                $data['images'][] = str_replace(';', '&#59;', $current_location . '/image.php?type=T&id=' . $data['productid']);
            }
            $tmp = func_query_first('SELECT * FROM ' . $sql_tbl['images_P'] . ' WHERE id = ' . $data['productid']);
            if ($tmp) {
                $data['images'][] = str_replace(';', '&#59;', $current_location . '/image.php?type=P&id=' . $data['productid']);
            }
            $tmp = func_query('SELECT imageid FROM ' . $sql_tbl['images_D'] . ' WHERE id = ' . $data['productid']);
            if ($tmp) {
                foreach ($tmp as $image) {
                    $data['images'][] = str_replace(';', '&#59;', $current_location . '/image.php?type=D&id=' . $image['imageid']);
                }
            }

        } elseif (isset($sql_tbl['thumbnails'])) {
            $tmp = func_query_first('SELECT * FROM ' . $sql_tbl['thumbnails'] . ' WHERE productid = ' . $data['productid']);
            if ($tmp) {
                $data['images'][] = str_replace(';', '&#59;', $current_location . '/image.php?productid=' . $data['productid']);
            }
        }
        $data['images'] = implode('&&', $data['images']);

        $row = array(
            $data['productcode'], // sku
            $data['product'],     // name
            $data['descr'],       // description
            $data['fulldescr'],   // briefDescription
            isset($data['meta_keywords']) ? $data['meta_keywords'] : '',       // metaTags
            isset($data['meta_description']) ? $data['meta_description'] : '', // metaDesc
            '',                                        // metaTitle
            $data['price'],                            // price
            'Y' == $data['forsale'] ? 'Y' : 'N',       // enabled
            $data['weight'],                           // weight
            'Y' == $data['free_shipping'] ? 'Y' : 'N', // freeShipping
            $data['clean_url'],                        // cleanURL
            '',                                        // arrivalDate
            '',                                        // ogMeta
            'N',                                       // useCustomOG
            $data['categories'],                       // categories
            $data['images'],                           // images
            $data['avail'],                            // stockLevel
            $data['low_avail_limit'] > 0 ? 'Y' : 'N',  // lowLimitEnabled
            $data['low_avail_limit'],                  // lowLimitAmount
            '',                                        // classes
        );
    }

    return $row;
}

/**
 * Get category path
 *
 * @param integer $cid Category id
 *
 * @return string
 */
function xcne_get_category_path($cid)
{
    global $sql_tbl;

    xcne_load('category');

    $path = '';

    if (function_exists('func_get_category_path')) {
        $path = str_replace(';', '&#59;', func_get_category_path($cid, 'category', true, '/'));

    } else {
        $catpath = func_query_first('SELECT categoryid_path FROM ' . $sql_tbl['categories'] . ' WHERE categoryid = ' . $cid);
        $catpath = explode('/', $catpath['categoryid_path']);

        $path = array();
        foreach ($catpath as $cid2) {
            $cat = func_query_first('SELECT category FROM ' . $sql_tbl['categories'] . ' WHERE categoryid = ' . $cid2);
            $path[] = str_replace(
                array('/', ';'),
                array('&#47;', '&#59;'),
                $cat['category']
            );
        }

        $path = implode(' >>> ', $path);
    }

    return $path;
}

// }}}

// {{{ Data

/**
 * Get products query as resource
 *
 * @return resource
 */
function xcne_get_products_query()
{
    global $sql_tbl;

    return db_query('SELECT * FROM ' . $sql_tbl['products']);
}

// }}}

// {{{ Service

/**
 * Get X-Cart version
 *
 * @return float
 */
function xcne_version()
{
    static $version;

    if (!isset($version)) {
        global $sql_tbl;

        $result = func_query_first("SELECT value FROM $sql_tbl[config] WHERE name='version'");
        $version = preg_replace('/^(\d+\.\d+)\..+$/Ss', '\1', $result['value']);
    }

    return $version;
}

/**
 * Error reporter
 *
 * @param string $message Message
 *
 * @return void
 */
function xcne_error($message)
{
    trigger_error($message, E_USER_ERROR);
}

/**
 * Load X-Cart subsystem
 *
 * @param string $subsystem Subsystem code
 *
 * @return void
 */
function xcne_load($subsystem)
{
    if (function_exists('x_load')) {
        x_load($subsystem);
    }
}

// }}}

// }}}
