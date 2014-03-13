<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages;


/**
 * Description of Page
 *
 * @author givi
 */
class CustomerPage extends \XLiteTest\Framework\Web\Pages\Page{
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $logOffLink = ".log-in[href*='logoff']";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $logInLink = ".log-in[href*='login']";
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $pageTitle = "#page-title";
    
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
            $user = $this->getConfig('customer_user', 'username');
            $password = $this->getConfig('customer_user', 'password');
        }
        //TODO: переписать под кастомерку
        return false;
        
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
    
    public function categoriesBox_getLink($link_text) {
        $loc = \WebDriverBy::linkText($link_text);
        if ($this->isElementPresent($loc)) {
            return $this->driver->findElement($loc);
        }
        return false;
    }
    
    public function pageTitle() {
        $condition = \WebDriverExpectedCondition::presenceOfElementLocated($this->pageTitle);
        $this->driver->wait(10)->until($condition, 'Time out.');
        return $this->driver->findElement($this->pageTitle);
    }


}
