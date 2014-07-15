<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteIntegration\Logic\Import\Processor;

class ProductsTest extends \XLiteIntegration\Logic\Import\Processor\AProcessor
{
    /**
     * Perform some actions before first test of this class is run
     */
    public static function setUpBeforeClass()
    {
		static::createDump();
    }

    /**
     * Perform some actions after last test of this class
     */
    public static function tearDownAfterClass()
    {
		static::restoreFromDump();
    }

    // {{{ Tests on common methods. (Use class Products to test common methods)

    /**
     * Test getTitle() method
     */
    public function testGetTitle()
    {
        $processor = $this->getProcessor();

        $this->assertEquals('Products imported', $processor->getTitle());
    }

    /**
     * Test getMessages() method
     */
    public function testGetMessages()
    {
        $processor = $this->getProcessor();

        $messages = $processor->getMessages();

        $this->assertInternalType('array', $messages, 'getMessages() returned not an array');

        $this->assertTrue(0 < count($messages));

        foreach ($messages as $key => $value) {
            $this->assertInternalType('string', $key, 'Message key is not string');
            $this->assertInternalType('string', $value, 'Message value is not string');
        }
    }

    /**
     * Test getErrorTexts() method
     */
    public function testGetErrorTexts()
    {
        $processor = $this->getProcessor();

        $messages = $processor->getErrorTexts();

        $this->assertInternalType('array', $messages, 'getErrorTexts() returned not an array');

        $this->assertTrue(0 < count($messages));

        foreach ($messages as $key => $value) {
            $this->assertInternalType('string', $key, 'Message key is not string');
            $this->assertInternalType('string', $value, 'Message value is not string');
        }
    }

    /**
     * Test getFileNameFormat() method
     */
    public function testGetFileNameFormat()
    {
        $processor = $this->getProcessor();

        $this->assertEquals('products.csv', $processor->getFileNameFormat());
    }

    /**
     * Test getFiles() and related methods. Set #1
     */
    public function testGetFilesSet1()
    {
        $dir = __DIR__ . LC_DS . 'files' . LC_DS . '01';
        $data = array(
            'dir' => $dir,
        );

        $processor = $this->getProcessor($data);

        $files = $processor->getFiles();

        $this->assertInternalType('array', $files);

        $expected = array(
            $dir . LC_DS . 'products-1.csv' => 100,
            $dir . LC_DS . 'products-3.csv' => 100,
        );

        $this->assertEquals(array_keys($expected), $files, 'Wrong files list returned');

        $this->assertTrue($processor->isValid());
        $this->assertTrue($processor->valid());
        $this->assertFalse($processor->isEof());

        $this->assertInstanceOf('\XLite\Logic\Import\Processor\Products', $processor->current());
        $this->assertInternalType('int', $processor->key());
        $this->assertEquals(0, $processor->key());

        $this->assertNull($processor->next());
        $this->assertInternalType('int', $processor->key());
        $this->assertEquals(1, $processor->key());
        $this->assertInstanceOf('\XLite\Logic\Import\Processor\Products', $processor->current());

        $this->assertNull($processor->next());
        $this->assertInternalType('int', $processor->key());
        $this->assertEquals(2, $processor->key());
        $this->assertInstanceOf('\XLite\Logic\Import\Processor\Products', $processor->current());

        $this->assertEquals(200, $processor->count());
        $this->assertEquals($expected, $processor->getCounts());

        // Go to first record
        $processor->seek(0);
        $this->assertEquals(0, $processor->key());
        $this->assertInstanceOf('\XLite\Logic\Import\Processor\Products', $processor->current());

        // Go to last record
        $processor->seek(199);
        $this->assertEquals(199, $processor->key());

        // Go to the record after last record
        $processor->seek(200);
        $this->assertEquals(199, $processor->key());
        $this->assertInstanceOf('\XLite\Logic\Import\Processor\Products', $processor->current());

        // Go to first record
        $processor->rewind();
        $this->assertEquals(0, $processor->key());
        $this->assertInstanceOf('\XLite\Logic\Import\Processor\Products', $processor->current());
    }

    /**
     * Test getFiles() and related methods. Set #2
     */
    public function testGetFilesSet2()
    {
        $dir = __DIR__ . LC_DS . 'files' . LC_DS . '02';  // Empty dir
        $data = array(
            'dir' => $dir,
        );

        $processor = $this->getProcessor($data);

        $files = $processor->getFiles();

        $this->assertInternalType('array', $files);

        $expected = array();

        $this->assertEquals($expected, $files, 'Wrong files list returned');

        $this->assertFalse($processor->isValid());
        $this->assertFalse($processor->valid());
        $this->assertTrue($processor->isEof());

        $this->assertInstanceOf('\XLite\Logic\Import\Processor\Products', $processor->current());
        $this->assertInternalType('int', $processor->key());
        $this->assertEquals(0, $processor->key());
    }

    /**
     * Test processCurrentRow() method
     */
    public function testProcessCurrentRow()
    {
        \XLite\Core\Database::getRepo('XLite\Model\ImportLog')->clearAll();

        $dir = __DIR__ . LC_DS . 'files' . LC_DS . '02';  // Empty dir
        $data = array(
            'dir' => $dir,
        );

        $processor = $this->getProcessor($data);

        $this->assertFalse($processor->processCurrentRow(\XLite\Logic\Import\Processor\AProcessor::MODE_VERIFICATION));

        $data['dir'] = __DIR__ . LC_DS . 'files' . LC_DS . '01';

        $processor = $this->getProcessor($data);

        $this->assertTrue($processor->processCurrentRow(\XLite\Logic\Import\Processor\AProcessor::MODE_VERIFICATION));
        $processor->next();
        $this->assertTrue($processor->processCurrentRow(\XLite\Logic\Import\Processor\AProcessor::MODE_VERIFICATION));
        $this->assertFalse($processor->processCurrentRow(\XLite\Logic\Import\Processor\AProcessor::MODE_IMPORT));
    }

    /**
     * Test isVerificationFailed() method
     */
    public function testIsVerificationFailed()
    {
        $processor = $this->getProcessor();

        \XLite\Core\Database::getRepo('XLite\Model\ImportLog')->clearAll();

        $this->assertFalse($processor->isVerificationFailed(), 'Import has no warnings but verification is failed');

        $log = new \XLite\Model\ImportLog;
        $log->setType(\XLite\Model\ImportLog::TYPE_WARNING);
        $log->setCode('MSG-CODE');
        $log->setArguments(array());
        $log->setFile('File path');
        $log->setRow(10);
        $log->setProcessor('\XLite\Logic\Import\Processor\Products');

        \XLite\Core\Database::getEM()->persist($log);
        \XLite\Core\Database::getEM()->flush($log);

        $this->assertTrue($processor->isVerificationFailed(), 'Import has warnings but verification is not failed');
    }

    // }}}

    // {{{ Test products import

    /**
     * Test import routine. Set #1
     */
    public function testImport1()
    {
        $dir = __DIR__ . LC_DS . 'files' . LC_DS . '03';

        $data = array(
            'dir' => $dir,
        );

        $messages = $this->doImport($data, false);

        require $dir . LC_DS . 'expected.res.php';

        $this->assertEquals($expected, $messages);
    }

    /**
     * Test import routine. Set #2 (empty file)
     */
    public function testImport2()
    {
        $dir = __DIR__ . LC_DS . 'files' . LC_DS . '04';

        $data = array(
            'dir' => $dir,
        );

        $messages = $this->doImport($data, false);

        require $dir . LC_DS . 'expected.res.php';

        $this->assertEquals($expected, $messages);
    }

    /**
     * Test import routine. Set #3 (no sku and name fields)
     */
    public function testImport3()
    {
        $dir = __DIR__ . LC_DS . 'files' . LC_DS . '05';

        $data = array(
            'dir' => $dir,
        );

        $messages = $this->doImport($data, false);

        require $dir . LC_DS . 'expected.res.php';

        $this->assertEquals($expected, $messages);
    }

    /**
     * Test import routine. Set #4 (correct verification with warnings)
     */
    public function testImport4()
    {
        $dir = __DIR__ . LC_DS . 'files' . LC_DS . '06';

        $data = array(
            'dir' => $dir,
        );

        \XLite\Core\Database::getRepo('XLite\Model\ImportLog')->clearAll();

        $messages = $this->doImport($data, false);

        require $dir . LC_DS . 'expected.res.php';

        $this->assertEquals($expected, $messages);
    }

    /**
     * Test import routine. Set #5 (correct verification and proceed to import)
     */
    public function testImport5()
    {
        $dir = __DIR__ . LC_DS . 'files' . LC_DS . '06';

        $data = array(
            'dir' => $dir,
            'warningsAccepted' => true,
        );

        \XLite\Core\Database::getRepo('XLite\Model\ImportLog')->clearAll();

        $messages = $this->doImport($data, true);

        require $dir . LC_DS . 'expected.res.php';

        $this->assertEquals($expected, $messages);
    }

    /**
     * Test import routine. Set #6 (correct verification and proceed to import)
     */
    public function testImport6()
    {
        $dir = __DIR__ . LC_DS . 'files' . LC_DS . '07';

        $data = array(
            'dir' => $dir,
            'warningsAccepted' => true,
        );

        \XLite\Core\Database::getRepo('XLite\Model\ImportLog')->clearAll();

        $messages = $this->doImport($data, true);

        require $dir . LC_DS . 'expected.res.php';

        $this->assertEquals($expected, $messages);
    }

    // }}}

    // {{{ Service methods

    /**
     * Get processor
     *
     * @return \XLite\Logic\Import\Processor\AProcessor
     */
    protected function getProcessor($options = array())
    {
        $importer = $this->getImporter($options);
        $processor = new \XLite\Logic\Import\Processor\Products($importer);

        return $processor;
    }

    // }}}
}
