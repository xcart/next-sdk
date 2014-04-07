<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteTest\TextUI;

/**
 * Text UI Result printer 
 */
class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter
{

    /**
     * Column 'Name' length 
     * 
     * @var   integer
     */
    protected $colNameLength = 60;

    /**
     * Column 'Memory' length 
     * 
     * @var   integer
     */
    protected $colMemoryLength = 10;

    /**
     * Column 'Time' length 
     * 
     * @var   integer
     */
    protected $colTimeLength = 12;

    /**
     * Memory start 
     * 
     * @var   integer
     */
    protected $memoryStart;

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
		parent::startTestSuite($suite);

        $name = $suite->getName();

        if (class_exists($name, false)) {
            $class = $suite->getName();
            $name = "\t" . $name;
            if (method_exists($class, 'getTestCaseName')) {
                $name .= ' [' . $class::getTestCaseName() . ']';
            }

        } elseif (preg_match('/^(.+)::(.+)$/Ss', $name, $match) && class_exists($match[1], false)) {
            $name = null;
        
        }

        if ($name) {
    		$this->write($name . PHP_EOL);
        }
	}

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        parent::startTest($test);

        if ($test instanceof \PHPUnit_Framework_TestCase) {
            $name = lcfirst(preg_replace('/^test/Ss', '', $test->getName()));
	    $fillCount =  $this->colNameLength - strlen($name) - 1;
	    if ($fillCount < 0) {
		$fillCount = 0;
	    }
            $this->write("\t\t" . $name . ' ' . str_repeat('.', $fillCount));
            $this->memoryStart = memory_get_usage();
        }
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        parent::endTest($test, $time);

        if ($test instanceof \PHPUnit_Framework_TestCase) {
            $memory = memory_get_usage() - $this->memoryStart;
            $memory = sprintf('%3.3fMb', $memory / 1048576);

            $t = round($time, 3);
            $t = gmdate('H:i:s', $time) . '.' . sprintf('%03d', ($t - floor($t)) * 1000);
            $this->write(
                ' '
                . str_repeat(' ', $this->colMemoryLength - strlen($memory)) . $memory
                . ' '
                . str_repeat(' ', $this->colTimeLength - strlen($t)) . $t
                . PHP_EOL
            );
        }
    }
}
