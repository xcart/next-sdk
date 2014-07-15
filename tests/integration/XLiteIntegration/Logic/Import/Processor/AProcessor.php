<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteIntegration\Logic\Import\Processor;

abstract class AProcessor extends \XLiteIntegration\AXLiteIntegration
{
    // {{{ Service methods

    /**
     * Get importer
     *
     * @return \XLite\Logic\Import\Importer
     */
    protected function getImporter($options)
    {
        $importer = new \XLite\Logic\Import\Importer($options);

        return $importer;
    }

    /**
     * Do import routine.
     * Returns array of messages
     *
     * @return array
     */
    protected function doImport($data, $doImportStep = false)
    {
        $messages = array(
            'warnings' => array(),
            'errors'   => array(),
        );

        $data = array_merge(
            array(
                'warningsAccepted' => false,
                'delimiter' => ',',
                'clearFiles' => true,
                'ignoreFileChecking' => true,
                'clearImportDir' => false,
            ),
            $data
        );

        $importer = $this->getImporter($data);

        $i = 0;
        while ($importer->getStep()->valid()) {
            $i++;
            $importer->getStep()->current()->process();
            $importer->getStep()->next();
        }

        if ($importer->hasWarnings()) {
            $warnings = \XLite\Core\Database::getRepo('XLite\Model\ImportLog')->findBy(array('type' => \XLite\Model\ImportLog::TYPE_WARNING));
            if ($warnings) {
                foreach ($warnings as $w) {
                    $messages['warnings'][] = $this->convertLogToArray($w);
                }
            }
        }

        if ($importer->hasErrors()) {
            $errors = \XLite\Core\Database::getRepo('XLite\Model\ImportLog')->findBy(array('type' => \XLite\Model\ImportLog::TYPE_ERROR));
            if ($errors) {
                foreach ($errors as $e) {
                    $messages['errors'][] = $this->convertLogToArray($e);
                }
            }
        }

        if ($doImportStep) {

            $importer->getOptions()->step = $importer->getOptions()->step + 1;
            $importer->getOptions()->position = 0;
            $i = 0;

            while ($importer->getStep()->valid()) {
                $i++;
                $importer->getStep()->current()->process();
                $importer->getStep()->next();
            }
        }

        return $messages;
    }

    /**
     * Convert ImportLog item to array
     *
     * @return array
     */
    protected function convertLogToArray($log)
    {
        return array(
            'code'      => $log->getCode(),
            'arguments' => $log->getArguments(),
            'file'      => $log->getFile(),
            'row'       => $log->getRow(),
            'processor' => $log->getProcessor(),
        );
    }

    // }}}
}
