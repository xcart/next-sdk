<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages\Customer;

/**
 * Description of Product
 *
 * @author givi
 */
class Product extends \XLiteTest\Framework\Web\Pages\CustomerPage {
    
    /** @findBy 'cssSelector' @var \WebDriverBy */
    protected $form = "form.product-details";
    /** @findBy 'cssSelector' @var \WebDriverBy 
     * @property \RemoteWebElement $get_buttonAddToCart */
    protected $buttonAddToCart = "button.bright.add2cart";
     
    public function validate() {
        return $this->isElementPresent($this->form);
    }
    
    public function load($autologin = false) {
        //нужно продумать лоадер. пока он не нужен.
        return false;
    }
    //.blockUI.blockMsg.blockElement.block-wait.wait-progress>div
    public function addToCart() {
        $this->get_buttonAddToCart->click();
        $progress = \WebDriverBy::cssSelector('.blockUI.blockMsg.blockElement.block-wait.wait-progress>div');
        $this->waitForAjax(30, $progress);
     }
}
