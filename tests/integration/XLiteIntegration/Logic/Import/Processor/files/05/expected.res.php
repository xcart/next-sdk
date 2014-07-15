<?php

// Expected result for test on import products from current directory

$expected = array (
  'warnings' => array (),
  'errors' => array (
    0 => array (
      'code' => 'PRODUCT-SKU-FMT',
      'arguments' => array (
        'column' => 'sku',
        'value' => '',
        'header' => NULL,
      ),
      'file' => 'products.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    1 => array (
      'code' => 'PRODUCT-NAME-FMT',
      'arguments' => array (
        'column' => 'name',
        'value' => '',
        'header' => NULL,
      ),
      'file' => 'products.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
  ),
);
