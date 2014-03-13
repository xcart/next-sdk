<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteWeb\Pages\Admin;

/**
 * Description of Categories
 *
 * @author givi
 */
class Categories extends \XLiteWeb\AdminPage{
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $categoriesTabActive = ".menu-item.active>a[href*='?target=categories']";
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $listLastLine = "tr[class^='line last']";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $lastCategoryName = "tr[class^='line last']>td[class='cell name main'] span[class='value']";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $newCategoryButton = ".create-inline";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $inputCategoryName = "#new-n1-name";
    
    public function validate() {
        return $this->isElementPresent($this->categoriesTabActive);
    }
    
    public function load($autologin = false) {
        $result = true;
        $this->driver->get($this->storeUrl . 'admin.php?target=categories');
        if ($autologin === true && !$this->isLogedIn()) {
            $result = parent::load(true);   
            if ($result === true) {
                $this->driver->get($this->storeUrl . 'admin.php?target=categories');

                }
        }
        return $result;
    }
    
    public function NewCategory() {
        
        return $this->driver->findElement($this->newCategoryButton)->click();
    }
    
    public function inputCategoryName($value) {
        return $this->driver->findElement($this->inputCategoryName)->sendKeys($value);
    }

    public function getLastCategoryName() {
        $condition = \WebDriverExpectedCondition::presenceOfElementLocated($this->lastCategoryName);
        $this->driver->wait(10)->until($condition, 'Time out.');
        return $this->driver->findElement($this->lastCategoryName)->getText();
    }
    
    public function getLastAddedCategoryId() {
        $data = $this->driver->findElement($this->listLastLine)->getAttribute('class');
        $matches = array();
        preg_match('/entity-([0-9]+)/', $data, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return false;
    }

    public function editCategory($categoryId)
    {
        $editLink = \WebDriverBy::cssSelector("tr[class$='entity-$categoryId'] a[class='edit']");
        $this->driver->findElement($editLink)->click();
        
        return $this;
    }

           
    public function deleteCategory($categoryId)
    {
        $editLink = \WebDriverBy::cssSelector("tr[class$='entity-$categoryId'] button[class='remove']");
        $this->driver->findElement($editLink)->click();
        
        return $this;    
    }
}
