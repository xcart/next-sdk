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
class CategoryUpdate extends \XLiteWeb\AdminPage{
    /**
     * used for load method
     * 
     */
    private $categoryId = null;
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $updateButton = "div[class='button submit']>.action";
    
    /**
    * 
    * @return boolean
    */    
    public function validate() {
        return $this->isElementPresent($this->categoriesTabActive);
    }
    
    /**
    * 
    * @return boolean
    */    
    public function load($autologin = false) {
        if ($this->categoryId == null) {
            throw new \Exception('categoryid is null.');
        }
        $result = true;
        $this->driver->get($this->storeUrl . 'admin.php?target=category&id='. $this->categoryId);
        if ($autologin === true && !$this->isLogedIn()) {
            $result = parent::load(true);   
            if ($result === true) {
                $this->driver->get($this->storeUrl . 'admin.php?target=category&id='. $this->categoryId);

                }
        }
        return $result;
    }
    
    public function Update() {
        return $this->driver->findElement($this->updateButton)->click();
    }
}