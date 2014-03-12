<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteWeb\Pages\Admin;

/**
 * Description of login
 *
 * @author givi
 */
class Login extends \XLiteWeb\AdminPage{
    /**
     * @findBy 'xpath'
     * @var \WebDriverBy
     */
    protected $submitButton = ".//*[@id='login_form']/table/tbody/tr[3]/td[2]/button";
    
    /**
     * @findBy 'xpath'
     * @var \WebDriverBy
     */
    protected $inputEmail = ".//*[@id='login_form']/table/tbody/tr[1]/td/input";
    
    /**
     * @findBy 'xpath'
     * @var \WebDriverBy
     */
    protected $inputPassword = ".//*[@id='login_form']/table/tbody/tr[2]/td/input";
    
    public function validate() {
        return $this->isElementPresent($this->submitButton);
    }
    
    public function load($autologin = false) {
        $this->driver->get($this->storeUrl . 'admin.php');
    }
    
    public function submit() {
        return $this->driver->findElement($this->submitButton)->click();
    }
    
    public function inputEmail($value) {
        return $this->driver->findElement($this->inputEmail)->sendKeys($value);
    }
    
    public function inputPassword($value) {
        return $this->driver->findElement($this->inputPassword)->sendKeys($value);
    }
}
