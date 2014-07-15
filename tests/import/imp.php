<?php

require_once '../../../src/top.inc.php';

XLite::getInstance()->run(true);

$EOL = "<br />";

$importer = new \XLite\Logic\Import\Importer(
    array(
        'warningsAccepted' => true,
        'delimiter' => ',',
        'clearFiles' => true,
        'ignoreFileChecking' => $argv[1],
        'clearImportDir' => false,
    )
);

$i = 0;
while ($importer->getStep()->valid()) {
    $i++;
    $importer->getStep()->current()->process();
    $importer->getStep()->next();
}

//Check warnings & errors
if($importer->hasWarnings()) {
    $warnings = \XLite\Core\Database::getRepo('XLite\Model\ImportLog')->findBy(array('type' => \XLite\Model\ImportLog::TYPE_WARNING));
    print_r($warnings);
}

if($importer->hasErrors()) {
    $errors = \XLite\Core\Database::getRepo('XLite\Model\ImportLog')->findBy(array('type' => \XLite\Model\ImportLog::TYPE_ERROR));
    print_r($errors);
}

if ($importer->isImportAllowed()) {

    $importer->getOptions()->step = $importer->getOptions()->step + 1;
    $importer->getOptions()->position = 0;
    $i = 0;
    while ($importer->getStep()->valid()) {
        $i++;
        $importer->getStep()->current()->process();
        $importer->getStep()->next();
    }

}

die('END' . PHP_EOL);

