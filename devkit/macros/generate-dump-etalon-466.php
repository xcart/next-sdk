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
 * Generate data dump
 */

define('X_CRON', TRUE);
define('SKIP_CHECK_REQUIREMENTS.PHP', TRUE);

define('PRODUCT_IMAGE_PATH', realpath('./skin/common_files/images/logo_gray.png'));

require_once './auth.php';

x_load('backoffice','category','image');

// {{{ Normalize input

$categories = 10;
$categoryImage = true;
$depth = 2;
$products = 20;
$featuredProducts = 20;
$wholesalePrices = 0;
$attributes = 0;
$options = 0;
$optionsValues = 0;
$productImages = 1;
$orders = 1000;
$orderItems = 0;

// }}}

$sums = array(
    'categories'       => 0,
    'categoryImages'   => 0,
    'featuredProducts' => 0,
    'products'         => 0,
    'attributes'       => 0,
    'options'          => 0,
    'optionValues'     => 0,
    'productImages'    => 0,
    'wholesalePrices'  => 0,
    'orders'           => 0,
    'orderItems'       => 0,
);
$counters = array(
    'attributes'      => 0,
    'options'         => 0,
    'wholesalePrices' => 0,
    'productImages'   => 0,
    'orders'          => 0,
);


$t = microtime(true);

$provider = func_query_first_cell("SELECT id FROM $sql_tbl[customers] WHERE usertype = 'A' LIMIT 1");

// {{{ Categories

if ($GLOBALS['categories'] > 0) {
    print 'Clear categories ... ';

    db_query("DELETE FROM $sql_tbl[categories]");
    db_query("DELETE FROM $sql_tbl[categories_lng]");
    db_query("DELETE FROM $sql_tbl[categories_subcount]");
    db_query("DELETE FROM $sql_tbl[category_memberships]");
    db_query("DELETE FROM $sql_tbl[category_threshold_bestsellers]");
    db_query("DELETE FROM $sql_tbl[clean_urls] WHERE resource_type = 'C'");
    db_query("DELETE FROM $sql_tbl[clean_urls_history] WHERE resource_type = 'C'");
    db_query("DELETE FROM $sql_tbl[images_C]");
    db_query("DELETE FROM $sql_tbl[products_categories]");

    print 'done' . PHP_EOL;

    print 'Generate categories ';
    generate_categories();
    print ' done' . PHP_EOL;


    print 'Rebuild categories cache ';
    func_cat_tree_rebuild_rec();
    func_cat_tree_rebuild_category_level();
    func_data_cache_clear('get_categories_tree');
    func_data_cache_clear('get_offers_categoryid');

    if (!empty($active_modules['Flyout_Menus']) && func_fc_use_cache()) {
        func_fc_build_categories(1, false, false, false);
    }

    print ' done' . PHP_EOL;

}

// }}}

// {{{ Products

if ($GLOBALS['products'] > 0) {
    print 'Clear products ... ';
    func_delete_product (0, false, true);
    print 'done' . PHP_EOL;

    print 'Generate products ';
    foreach (func_query_column("SELECT c.categoryid FROM $sql_tbl[categories] AS c LEFT JOIN $sql_tbl[categories] AS cc ON cc.parentid = c.categoryid WHERE cc.categoryid IS NULL") as $id) {
        generate_products($id);
    }
    print ' done' . PHP_EOL;

    // Featured products
    if (
        $GLOBALS['featuredProducts'] > 0
        && isset($sql_tbl['featured_products'])
    ) {

        $list = func_query_column("SELECT productid FROM $sql_tbl[featured_products] LIMIT 1, " . $GLOBALS['featuredProducts']);
        $i = 0;
        foreach ($list as $pid) {
            if ($i < $GLOBALS['featuredProducts']) {
                db_query("REPLACE INTO $sql_tbl[featured_products] (productid, product_order, avail, categoryid) VALUES ('$pid','$i','Y', '0')");
                $GLOBALS['sums']['featuredProducts']++;
                $i++;
            }
        }
    }

    print 'Rebuild products cache ';

    XCRangeProductIdsAdmin::dropCache();

    if (!empty($active_modules['Flyout_Menus']) && func_fc_use_cache()) {
        func_fc_build_categories(1, false, false, false);
    }
    print ' done' . PHP_EOL;

}

// }}}

// {{{ Orders

if ($GLOBALS['orders'] > 0) {
    $_tmp = $GLOBALS['orders'];
    print 'Clear orders ... ';
    $orders = func_query("SELECT orderid FROM $sql_tbl[orders]");
    @func_delete_order($orders);
    print 'done' . PHP_EOL;

    $orders = $_tmp;
    print 'Generate orders ';
    generate_orders();
    print ' done' . PHP_EOL;
}

// }}}

print 'Statistics:' . PHP_EOL;
foreach ($sums as $k => $v) {
    print "\t" . $k . ': ' . $v . PHP_EOL;
}
print 'Duration: ' . gmdate('H:i:s', $t - microtime(true)) . PHP_EOL;

die(0);

// {{{ Functions

function generate_categories($parent = 0, $depth = 1)
{
    global $sql_tbl, $active_modules;

    $list = array();
    for ($i = 0; $i < $GLOBALS['categories']; $i++) {
        $id = @func_insert_category($parent, $i, 'Test category #' . $depth . ' - ' . $i);
        func_array2update(
            'categories',
            array(
                'category' => 'Test category #' . $depth . ' - ' . $i,
            ),
            'categoryid = ' . $id
        );
        XCProducts_CategoriesChange::repairIntegrity($id);

        $int_cat_data = array(
            'categoryid'  => $id,
            'code'        => 'en',
            'category'    => 'Test category #' . $depth . ' - ' . $i,
            'description' => '',
        );
        func_array2insert('categories_lng', $int_cat_data);

        $path = func_get_category_path($id);

        func_reflect_category_availability_changes($id);

        if (!empty($path)) {
            func_recalc_subcat_count($path);
        }


        print '.';
        $GLOBALS['sums']['categories']++;

        $list[] = $id;
    }

    if ($list) {
        // Images
        if ($GLOBALS['categoryImage']) {
            foreach ($list as $id) {
                $file_upload_data = array(
                    'C' => array(
                        'file_path'  => PRODUCT_IMAGE_PATH,
                        'source'     => 'S',
                        'filename'   => basename(PRODUCT_IMAGE_PATH),
                        'image_type' => 'image/png',
                        'image_x' => 86,
                        'image_y' => 27,
                    ),
                );
                if (func_save_image($file_upload_data, 'C', $id)) {
                    $GLOBALS['sums']['categoryImages']++;
                }
            }
        }

        if ($depth < $GLOBALS['depth']) {
            foreach ($list as $id) {
                generate_categories($id, $depth + 1);
            }
        }
    }
}

function generate_products($id)
{
    global $sql_tbl, $provider, $active_modules;

    $list = array();
    for ($i = 0; $i < $GLOBALS['products']; $i++) {
        $pid = func_array2insert(
            'products',
            array(
                'productcode'      => 'SKU' . $id . '_' . $i,
                'provider'         => $provider,
                'add_date'         => XC_TIME,
                'meta_description' => '',
                'meta_keywords'    => '',
                'title_tag'        => '',
                'avail'            => 100,
            )
        );
        func_array2insert(
            'pricing',
            array(
                'productid' => $pid,
                'quantity'  => 1,
                'price'     => rand(1, 100),
            )
        );

        XCProductSalesStats::insertNewRow($pid);

        func_array2insert(
            'products_lng_en',
            array(
                'productid' => $pid,
                'product'   => 'Test product #' . $id . ' - ' . $i,
                'descr'     => '',
                'fulldescr' => '',
                'keywords'  => ''
            )
        );

        func_array2insert(
            'products_categories',
            array(
                'categoryid' => $id,
                'productid'  => $pid,
                'main'       => 'Y'
            )
        );

        XCProducts_CategoriesChange::repairIntegrity($id, $pid);

        if (
            !empty($active_modules['Recommended_Products'])
            || !empty($active_modules['Add_to_cart_popup'])
        ) {
            func_refresh_product_rnd_keys($pid);
        }

        func_build_quick_flags($pid);
        func_build_quick_prices($pid);

        $list[] = $pid;

        print '.';
        $GLOBALS['sums']['products']++;
    }

    // Images
    foreach ($list as $pid) {
        $GLOBALS['counters']['productImages']++;
        $limit = floor($GLOBALS['counters']['productImages'] / 100) + 1;
        for ($i = 0; $i < $limit; $i++) {
            if ($i == 0) {
                $type = 'T';

            } elseif ($i == 1) {
                $type = 'P';

            } elseif ($i > 1) {
                $type = 'D';
            }
            $file_upload_data = array(
                $type => array(
                    'file_path'  => PRODUCT_IMAGE_PATH,
                    'source'     => 'S',
                    'filename'   => basename(PRODUCT_IMAGE_PATH),
                    'image_type' => 'image/png',
                    'image_x' => 86,
                    'image_y' => 27,
                ),
            );
            if (func_save_image($file_upload_data, $type, $pid)) {
                $GLOBALS['sums']['productImages']++;
            }
       }
    }

    // Featured products
    if (
        $GLOBALS['featuredProducts'] > 0
        && isset($sql_tbl['featured_products'])
    ) {

        $i = 0;
        foreach ($list as $pid) {
            if ($i < $GLOBALS['featuredProducts']) {
                db_query("REPLACE INTO $sql_tbl[featured_products] (productid, product_order, avail, categoryid) VALUES ('$pid','$i','Y', '$id')");
                $GLOBALS['sums']['featuredProducts']++;
                $i++;
            }
        }
    }

    // Wholesale prices
    foreach ($list as $pid) {
        $q1 = 1;
        $q2 = 10;
        $GLOBALS['counters']['wholesalePrices']++;
        $limit = floor($GLOBALS['counters']['wholesalePrices'] / 100) + 2;
        for ($i = 0; $i < $limit; $i++) {
            $last = $i == $limit - 1;
            func_array2insert(
                'pricing',
                array(
                    'quantity'   => $q2,
                    'productid'  => $pid,
                    'price'      => rand(1, 100),
                )
            );
            $GLOBALS['sums']['wholesalePrices']++;
            $q1 = $q2 + 1;
            $q2 += 10;
        }
    }

    // Attributes
    foreach ($list as $pid) {
        $GLOBALS['counters']['attributes']++;
        $limit = floor($GLOBALS['counters']['attributes'] / 100);
        for ($i = 0; $i < $limit; $i++) {
            $sname = 'efield' . $i;
            $fid = func_query_first_cell("SELECT fieldid FROM $sql_tbl[extra_fields] WHERE service_name = '" . $sname . "' LIMIT 1");
            if (!$fid) {
                $fid = func_array2insert(
                    'extra_fields',
                    array(
                        'provider'     => $provider,
                        'field'        => 'Test attribute ' . $pid . '-' . $i,
                        'orderby'      => $i,
                        'service_name' => $sname,
                    )
                );
            }

            func_array2insert(
                'extra_field_values',
                array(
                    'productid' => $pid,
                    'fieldid'   => $fid,
                    'value'     => 'value ' . $i,
                )
            );

            $GLOBALS['sums']['attributes']++;
        }
    }

    // Options
    foreach ($list as $pid) {
        $GLOBALS['counters']['options']++;
        $limit = floor($GLOBALS['counters']['options'] / 100);
        for ($i = 0; $i < $limit; $i++) {
            $clid = func_array2insert(
                'classes',
                array(
                    'productid' => $pid,
                    'class'     => 'Test option ' . $pid . '-' . $i,
                    'classtext' => 'Test option ' . $pid . '-' . $i,
                    'orderby'   => $i,
                )
            );
            for ($n = 0; $n < $limit + 1; $n++) {
                func_array2insert(
                    'class_options',
                    array(
                        'classid'        => $clid,
                        'option_name'    => 'value ' . $i . '-' . $n,
                        'orderby'        => $i,
                        'price_modifier' => $n > 0 ? round(rand(0, 50) / 10, 1) : 0
                    )
                );
                $GLOBALS['sums']['optionValues']++;
            }

            $GLOBALS['sums']['options']++;
        }
    }
}

function generate_orders()
{
    global $sql_tbl, $provider;

    if ($GLOBALS['orders'] > 0) {
        $userid = func_query_first_cell("SELECT id FROM $sql_tbl[customers] WHERE usertype = 'C' LIMIT 1");
        if (!$userid) {
            $userid = func_array2insert(
                'customers',
                array(
                    'login'    => 'bit-bucket+customer@x-cart.com',
                    'username' => 'bit-bucket-customer',
                    'usertype' => 'C',
                    'password' => addslashes(text_crypt(text_hash('master'))),
                    'change_password'      => 'N',
                    'change_password_date' => XC_TIME,
                    'email'    => 'bit-bucket+customer@x-cart.com',
                )
            );
        }

        $productCount = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[products]");

        for ($i = 0; $i < $GLOBALS['orders']; $i++) {
            $oid = func_array2insert(
                'orders',
                array(
                    'userid' => $userid,
                    'giftcert_ids' => '',
                    'taxes_applied' => '',
                )
            );

            $GLOBALS['counters']['orders']++;
            $limit = floor($GLOBALS['counters']['orders'] / 100) + 1;
            $total = 0;
            for ($n = 0; $n < $limit; $n++) {
                $pid = func_query_first_cell("SELECT productid FROM $sql_tbl[products] LIMIT " . rand(0, $productCount - 1) . ", 1");
                $price = rand(1, 100);
                $amount = 1;
                $total += $price * $amount;
                $oiid = func_array2insert(
                    'order_details',
                    array(
                        'orderid'     => $oid,
                        'productid'   => $pid,
                        'price'       => $price,
                        'amount'      => $amount,
                        'provider'    => $provider,
                        'productcode' => 'SKU' . $pid,
                        'product'     => 'Test product ' . $pid,
                        'product_options' => '',
                        'extra_data'  => '',
                    )
                );
                $GLOBALS['sums']['orderItems']++;
            }

            func_array2update(
                'orders',
                array(
                    'subtotal' => $total,
                    'total'    => $total,
                ),
                "orderid = $oid"
            );

            print '.';
            $GLOBALS['sums']['orders']++;
        }
    }
}

// }}}
