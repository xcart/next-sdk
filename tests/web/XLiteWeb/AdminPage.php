<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteWeb;


/**
 * Description of Page
 *
 * @author givi
 */
class AdminPage extends \XLiteWeb\Page{
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $logOffLink = ".link-item.logoff>a>span";
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $saveChangesButton = ".action.submit";
    
    public function load($autologin = false) {
        if ($autologin === true && !$this->isLogedIn()) {
            return $this->LogIn();
        }
        return true;
    }
    
    public function isLogedIn() {
        return $this->isElementPresent($this->logOffLink);
    }
    
    public function LogIn($user='', $password='') {
        
        if (empty($user) && empty($password)) {
            $user = $this->getConfig('admin_user', 'username');
            $password = $this->getConfig('admin_user', 'password');
        }
        
        $login = new \XLiteWeb\Pages\Admin\Login($this->driver, $this->storeUrl);
        if (!$login->validate()) {
            return false;
        }
        
        $login->inputEmail($user);
        $login->inputPassword($password);
        
        $login->submit();
        
        if ($login->isErrorOnPage()) {
            return false;
        }
        return true;
    }
    
    public function SaveChanges() {
        return $this->driver->findElement($this->saveChangesButton)->click();
    }

    public function fillForm($data) {
        foreach ($data as $element=>$value) {
            $methodName = 'input' . ucfirst(str_replace('-', '_', $element));
            if (method_exists ( $this, $methodName )) {
                $this->$methodName($value);
            } else {
                $by = \WebDriverBy::cssSelector('#' . $element);
                $webElement = $this->driver->findElement($by);
                $tag = $this->driver->findElement($by)->getTagName();
                
                if ($tag == 'select' && !is_array($value)) {
                    $Select = new \WebDriverSelect($webElement);
                    $Select->selectByValue($value);
                } elseif ($tag == 'select' && is_array($value)) {//multiselect
                    $multiSelect = new \WebDriverSelect($webElement);
                    $multiSelect->deselectAll();
                    foreach ($value as $item) {
                        $multiSelect->selectByValue($item);
                    }
                } elseif ($tag == 'textarea') {
                    if ($webElement->isDisplayed()) {
                        $webElement->sendKeys($value);
                    } else {
                        $this->driver->executeScript('$("#' . $element . '").text("' . $value . '");');
                    }
                } else {
                    $webElement->sendKeys($value);
                }
            }
        }
    }
    
}
