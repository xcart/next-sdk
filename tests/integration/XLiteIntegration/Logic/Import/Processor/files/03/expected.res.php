<?php

// Expected result for test on import products from current directory

$expected = array (
  'warnings' => 
  array (
    0 => 
    array (
      'code' => 'PRODUCT-PRICE-FMT',
      'arguments' => 
      array (
        'column' => 'price',
        'value' => 'xxx',
        'header' => 'price',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    1 => 
    array (
      'code' => 'GLOBAL-MEMBERSHIP-FMT',
      'arguments' => 
      array (
        'column' => 'memberships',
        'value' => 'ccc',
        'header' => 'memberships',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    2 => 
    array (
      'code' => 'GLOBAL-PRODUCT-CLASS-FMT',
      'arguments' => 
      array (
        'column' => 'productClass',
        'value' => 'vvv',
        'header' => 'productClass',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    3 => 
    array (
      'code' => 'GLOBAL-TAX-CLASS-FMT',
      'arguments' => 
      array (
        'column' => 'taxClass',
        'value' => 'yyy',
        'header' => 'taxClass',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    4 => 
    array (
      'code' => 'PRODUCT-ENABLED-FMT',
      'arguments' => 
      array (
        'column' => 'enabled',
        'value' => 'aaa',
        'header' => 'enabled',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    5 => 
    array (
      'code' => 'PRODUCT-WEIGHT-FMT',
      'arguments' => 
      array (
        'column' => 'weight',
        'value' => 'zzz',
        'header' => 'weight',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    6 => 
    array (
      'code' => 'PRODUCT-FREE-SHIP-FMT',
      'arguments' => 
      array (
        'column' => 'freeShipping',
        'value' => 'bbb',
        'header' => 'freeShipping',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    7 => 
    array (
      'code' => 'PRODUCT-ARRIVAL-DATE-FMT',
      'arguments' => 
      array (
        'column' => 'arrivalDate',
        'value' => 'sss',
        'header' => 'arrivalDate',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    8 => 
    array (
      'code' => 'PRODUCT-DATE-FMT',
      'arguments' => 
      array (
        'column' => 'date',
        'value' => 'ddd',
        'header' => 'date',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    9 => 
    array (
      'code' => 'PRODUCT-UPDATE-DATE-FMT',
      'arguments' => 
      array (
        'column' => 'updateDate',
        'value' => '111',
        'header' => 'updateDate',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    10 => 
    array (
      'code' => 'PRODUCT-INV-TRACKING-FMT',
      'arguments' => 
      array (
        'column' => 'inventoryTrackingEnabled',
        'value' => 'ccc',
        'header' => 'inventoryTrackingEnabled',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    11 => 
    array (
      'code' => 'PRODUCT-STOCK-LEVEL-FMT',
      'arguments' => 
      array (
        'column' => 'stockLevel',
        'value' => 'aaa',
        'header' => 'stockLevel',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    12 => 
    array (
      'code' => 'PRODUCT-LOW-LIMIT-NOTIF-FMT',
      'arguments' => 
      array (
        'column' => 'lowLimitEnabled',
        'value' => 'eee',
        'header' => 'lowLimitEnabled',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    13 => 
    array (
      'code' => 'PRODUCT-LOW-LIMIT-LEVEL-FMT',
      'arguments' => 
      array (
        'column' => 'lowLimitLevel',
        'value' => 'iii',
        'header' => 'lowLimitLevel',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    14 => 
    array (
      'code' => 'PRODUCT-USE-SEP-BOX-FMT',
      'arguments' => 
      array (
        'column' => 'useSeparateBox',
        'value' => 'ppp',
        'header' => 'useSeparateBox',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    15 => 
    array (
      'code' => 'PRODUCT-BOX-WIDTH-FMT',
      'arguments' => 
      array (
        'column' => 'boxWidth',
        'value' => 'aaa',
        'header' => 'boxWidth',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    16 => 
    array (
      'code' => 'PRODUCT-BOX-LENGTH-FMT',
      'arguments' => 
      array (
        'column' => 'boxLength',
        'value' => 'bbb',
        'header' => 'boxLength',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    17 => 
    array (
      'code' => 'PRODUCT-BOX-HEIGHT-FMT',
      'arguments' => 
      array (
        'column' => 'boxHeight',
        'value' => 'ccc',
        'header' => 'boxHeight',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    18 => 
    array (
      'code' => 'PRODUCT-ITEMS-PRE-BOX-FMT',
      'arguments' => 
      array (
        'column' => 'itemsPerBox',
        'value' => 'qqq',
        'header' => 'itemsPerBox',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    19 => 
    array (
      'code' => 'USER-USE-OG-META-FMT',
      'arguments' => 
      array (
        'column' => 'useCustomOpenGraphMeta',
        'value' => 'rrr',
        'header' => 'useCustomOpenGraphMeta',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    20 => 
    array (
      'code' => 'PRODUCT-MARKET-PRICE-FMT',
      'arguments' => 
      array (
        'column' => 'marketPrice',
        'value' => 'ttt',
        'header' => 'marketPrice',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    21 => 
    array (
      'code' => 'RELATED-PRODUCT-SKU-FMT',
      'arguments' => 
      array (
        'column' => 'relatedProducts',
        'value' => 'zzzz',
        'header' => 'relatedProducts',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
  ),
  'errors' => 
  array (
    0 => 
    array (
      'code' => 'PRODUCT-SKU-FMT',
      'arguments' => 
      array (
        'column' => 'sku',
        'value' => '',
        'header' => 'sku',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    1 => 
    array (
      'code' => 'PRODUCT-CLEAN-URL-FMT',
      'arguments' => 
      array (
        'column' => 'cleanURL',
        'value' => '',
        'header' => 'cleanURL',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    2 => 
    array (
      'code' => 'PRODUCT-SKU-FMT',
      'arguments' => 
      array (
        'column' => 'sku',
        'value' => '',
        'header' => 'sku',
      ),
      'file' => 'products-1.csv',
      'row' => 3,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    3 => 
    array (
      'code' => 'PRODUCT-NAME-FMT',
      'arguments' => 
      array (
        'column' => 'name',
        'value' => '',
        'header' => 'name_en',
      ),
      'file' => 'products-1.csv',
      'row' => 3,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
  ),
);
