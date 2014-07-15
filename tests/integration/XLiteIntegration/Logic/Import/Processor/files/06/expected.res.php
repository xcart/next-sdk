<?php

// Expected result for test on import products from current directory

$expected = array (
  'warnings' => array (
     0 => array (
      'code' => 'GLOBAL-CATEGORY-FMT',
      'arguments' => array (
        'column' => 'categories',
        'value' => 'Test',
        'header' => 'categories',
      ),
      'file' => 'products-1.csv',
      'row' => 2,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
    1 => array (
      'code' => 'GLOBAL-CATEGORY-FMT',
      'arguments' => array (
        'column' => 'categories',
        'value' => 'Test2',
        'header' => 'categories',
      ),
      'file' => 'products-1.csv',
      'row' => 3,
      'processor' => 'XLite\\Logic\\Import\\Processor\\Products',
    ),
  ),
  'errors' => array (),
);
