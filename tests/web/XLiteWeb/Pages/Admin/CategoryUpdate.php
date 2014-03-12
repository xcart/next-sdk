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
    protected $inputDescription = "textarea#description";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $inputMeta_title = "#meta-title";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $inputMeta_keywords = "#meta-tags";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $inputMeta_desc = "#meta-desc";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $updateButton = "div[class='button submit']>.action";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $inputAvailabilityYes = "#enabled>option[value='Y']";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $inputAvailabilityNo = "#enabled>option[value='N']";
    
    public function validate() {
        return $this->isElementPresent($this->categoriesTabActive);
    }
    
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
    
    public function inputDescription($value) {
        $sel = $this->inputDescription->getValue();
        $this->driver->executeScript('$("' . $sel . '").text("' . $value . '");');
        return $this;
    }
    
    public function inputMeta_title($value) {
        return $this->driver->findElement($this->inputMeta_title)->sendKeys($value);
    }
    
    public function inputMeta_keywords($value) {
        return $this->driver->findElement($this->inputMeta_keywords)->sendKeys($value);
    }
    
    public function inputMeta_desc($value) {
        return $this->driver->findElement($this->inputMeta_desc)->sendKeys($value);
    }
    
    public function Update() {
        return $this->driver->findElement($this->updateButton)->click();
    }
    
    public function inputMemberships($value) {
        foreach ($value as $membershipId) {
            $selector = \WebDriverBy::cssSelector("#memberships>option[value='$membershipId']");
            $this->driver->findElement($selector)->click();
        }
        
        return $this;
    }

    public function inputAvailability($value) {
        $selector = 'inputAvailability' . $value;
        $condition = \WebDriverExpectedCondition::presenceOfElementLocated($this->$selector);
        $this->driver->wait(10)->until($condition, 'Time out.');
        return $this->driver->findElement($this->$selector)->click();
    }



}
