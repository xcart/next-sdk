<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages\Admin;

/**
 * Description of ProductAdd
 *
 * @author givi
 */
class ProductAdd extends \XLiteTest\Framework\Web\Pages\AdminPage{
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $hiddenProductId = "form.product>fieldset>input[name='product_id']";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $addProductTabActive = ".menu-item.active>a[href*='?target=add_product']";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $addPoduct = "button.action.submit";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $inputCategory = "#categories";
    
    /**
    * 
    * @return boolean
    */    
    public function validate() {
        return $this->isElementPresent($this->addProductTabActive);
    }
    
    /**
    * 
    * @return boolean
    */    
    public function load($autologin = false) {
        
        $result = true;
        $this->driver->get($this->storeUrl . 'admin.php?target=add_product');
        if ($autologin === true && !$this->isLogedIn()) {
            $result = parent::load(true);   
            if ($result === true) {
                $this->driver->get($this->storeUrl . 'admin.php?target=add_product');

                }
        }
        return $result;
    }
    
    public function addProduct() {
        return $this->driver->findElement($this->addPoduct)->click();
    }
    
    /**
     * select and return category name.
     * @return string
     */
    public function selectRandomCategory() {
        $categories = new \WebDriverSelect($this->driver->findElement($this->inputCategory));
        $options = $categories->getOptions();
        
        do {
            $option = $options[array_rand($options)];
        } while(preg_match('/^-|Test/', $option->getText()));
        
        $option->click();
        return $option->getText();
    }
    
    public function getCategory($value) {
        $text = '';
        $categories = new \WebDriverSelect($this->driver->findElement($this->inputCategory));
        $selected = $categories->getAllSelectedOptions();
        $text = $selected[0]->getText();
        return $text;
    }

    public function getProductId() {
        return $this->driver->findElement($this->hiddenProductId)->getAttribute('value');
    }
}
