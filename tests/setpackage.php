#!/usr/bin/env php
<?php

if ($argc != 3) {
    die('Usage: setpackage <xml file> <package>');
}

$filename = $argv[1];

$xml = simplexml_load_string(file_get_contents($filename));

$res = $xml->xpath('//testsuite[testcase]');
foreach ($res as $node) {
$node->attributes()->name = $argv[2] . '.' . $node->attributes()->name;
}

$xml->asXML($filename);