<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteUnit\Model;

/**
 * @coversDefaultClass \XLite\Model\Product
 */
class ProductTest extends \XLiteUnit\AXLiteUnit
{
	public function testGetName()
	{
		$model = new \XLite\Model\Product;

        $this->assertNull($model->getName());

		$model->setName('test');

		$this->assertEquals('test', $model->getName());
	}

    /**
     * @dataProvider providerSetName
     */
    public function testSetName($name)
    {
        $model = new \XLite\Model\Product;

        $model->setName($name);
        $this->assertEquals($name, $model->getName());
    }

    public function providerSetName()
    {
        return array(
            array('test'),
            array('<a>test</a>'),
        );
    }    

}
