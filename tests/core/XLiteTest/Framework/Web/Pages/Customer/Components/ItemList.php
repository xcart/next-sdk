<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages\Customer\Components;

/**
 * Description of ItemList
 *
 * @author givi
 */
class ItemList extends \XLiteTest\Framework\Web\Pages\Component{
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $findBy = '.items-list.category-products';
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $itemPerPage = ".page-length";
    
    /**
     * 
     * @param int $value
     * @return \XLiteTest\Framework\Web\Pages\Customer\Components\ItemList
     */
    public function setItemPerPage($value) {
        $webElement = $this->getComponent()->findElement($this->itemPerPage);
        $webElement->sendKeys($value . \WebDriverKeys::ENTER);
        $this->waitForAjax();
        return $this;
    }
    
    /**
    * 
    * @param type $productId
    * @return bool
    */    
    public function isProductExist($productId) {
        return $this->isElementPresent(\WebDriverBy::cssSelector("div[class^='product productid-$productId ']"));
    }

    public function productName($productId) {
        return $this->getComponent()->findElement(\WebDriverBy::cssSelector("div[class^='product productid-$productId ']>h3.product-name>a"));
    }
}
