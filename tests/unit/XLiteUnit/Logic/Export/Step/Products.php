<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteUnit\Logic\Import\Processor;

class ProductsTest extends \XLiteUnit\Logic\Export\Step\AStep
{
    // {{{ Test export of products

    /**
     * Test on export methods
     */
    public function testConstructStep()
    {
        $data = array(
            'dir' => $this->getDir(),
        );

        $step = $this->getStep($data);

        // Get real number of products in database
        $countProducts = \XLite\Core\Database::getEM()
            ->createQuery('SELECT COUNT(p.product_id) FROM \XLite\Model\Product p')
            ->getSingleScalarResult();

        // Check step->count()
        $this->assertEquals($countProducts, $step->count(), 'Test step->count()');

        $this->assertEquals(0, $step->key(), 'Test step->key() in first position');
        $this->assertInstanceOf('\XLite\Logic\Export\Step\Products', $step->current());
        $this->assertTrue($step->valid());

        // Move pointer to the next record
        $step->next();
        $this->assertEquals(1, $step->key(), 'Test step->key() on second position');

        // Move pointer to the last record
        $step->seek($countProducts - 1);
        $this->assertEquals($countProducts - 1, $step->key(), 'Test step->key() on last position');

        // Move pointer behind the last record: it's expected that pointer should not be changed
        $step->seek($countProducts);
        $this->assertEquals($countProducts - 1, $step->key(), 'Test step->key() after moving pointer behind last position');

        // Move pointer to the first record
        $step->rewind();
        $this->assertEquals(0, $step->key(), 'Test step->key() after rewind');

        // Create export file from first position
        \Includes\Utils\FileManager::unlinkRecursive($this->getDir());
        \Includes\Utils\FileManager::mkdirRecursive($this->getDir());

        $step->run();

        $file = $this->getDir() . LC_DS . $this->getFilename();

        $this->assertTrue(\Includes\Utils\FileManager::isFileReadable($file));

        // Check exported file
        $f = fopen($file, 'r');
        $rows = array();
        while (false !== ($data = fgetcsv($f, 0, ','))) {
            $rows[] = $data;
        }

        $this->assertNotEmpty($rows, 'Exported file is empty');
        $this->assertEquals(2, count($rows), 'Exported file count rows checking failed');
    }

    // }}}

    // {{{ Service methods

    /**
     * Get export step
     *
     * @return \XLite\Logic\Export\Step\Products
     */
    protected function getStep($options = array())
    {
        $generator = $this->getGenerator($options);
        $step = new \XLite\Logic\Export\Step\Products($generator);

        return $step;
    }

    /**
     * Get export generator
     *
     * @return \XLite\Logic\Export\Generator
     */
    protected function getGenerator($options = array())
    {
        static $generator;

        if (!isset($generator)) {
            $options = array_merge(
                array(
                    'copyResources' => false,
                ),
                $options
            );

            $generator = new \XLite\Logic\Export\Generator($options);
        }

        return $generator;
    }

    /**
     * Get directory path to save export files
     *
     * @return string
     */
    protected function getDir()
    {
        return __DIR__ . LC_DS . 'files' . LC_DS . 'output';
    }

    /**
     * Get export file name
     *
     * @return string
     */
    protected function getFilename()
    {
        return 'products-' . date('Y-m-d') . '.csv';
    }

    // }}}
}
