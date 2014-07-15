<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages\Customer\Components;

/**
 * Description of MiniCart
 *
 * @author givi
 */
class MiniCart extends \XLiteTest\Framework\Web\Pages\Component{
    /** @findBy 'cssSelector' @var \WebDriverBy */
    protected $findBy = '.lc-minicart';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_buttonCart */
    protected $buttonCart = '.action.cart';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_buttonCheckout */
    protected $buttonCheckout = 'button.checkout';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_textItemCount */
    protected $textItemCount = '.minicart-items-number';
    
    public function click() {
        return $this->get_findBy->click();
    }
}
