<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteTest\Framework;

/**
 * Abstract X-Cart 5 test case 
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Runs the test case and collects the results in a TestResult object.
     * If no TestResult object is passed a new one will be created.
     *
     * @param  \PHPUnit_Framework_TestResult $result
     * @return \PHPUnit_Framework_TestResult
     * @throws \PHPUnit_Framework_Exception
     */
    public function run(\PHPUnit_Framework_TestResult $result = null)
    {
        if ($result === null) {
            $result = $this->createResult();
        }

        if ($result->getCodeCoverage()) {
            $result->getCodeCoverage()->filter()->addDirectoryToWhitelist(LC_DIR_CACHE_CLASSES);
        }

        return parent::run($result);
    
    }

}
