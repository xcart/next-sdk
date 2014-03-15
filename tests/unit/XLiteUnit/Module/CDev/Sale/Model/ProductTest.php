<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteUnit\Module\CDev\Sale\Model;

/**
 * @coversDefaultClass \XLite\Module\CDev\Sale\Model\Product
 */
class ProductTest extends \XLiteUnit\AXLiteUnit
{
	public function testGetDisplayPrice()
	{
		$model = new \XLite\Model\Product;

		$model->setPrice(10);
		$this->assertEquals(10, $model->getPrice());
		$this->assertEquals(10, $model->getDisplayPrice());

		$model->setParticipateSale(true);
        $model->setDiscountType(\XLite\Module\CDev\Sale\Model\Product::SALE_DISCOUNT_TYPE_PRICE);
        $model->setSalePriceValue(8);
		$this->assertEquals(8, $model->getDisplayPrice());

        $model->setDiscountType(\XLite\Module\CDev\Sale\Model\Product::SALE_DISCOUNT_TYPE_PERCENT);
        $model->setSalePriceValue(60);
		$this->assertEquals(4, $model->getDisplayPrice());
	}
}
