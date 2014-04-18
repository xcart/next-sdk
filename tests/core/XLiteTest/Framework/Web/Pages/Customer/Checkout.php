<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages\Customer;

/**
 * Description of Checkout
 *
 * @author givi
 */
class Checkout extends \XLiteTest\Framework\Web\Pages\CustomerPage {
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_buttonSignIn */
    protected $buttonSignIn = 'td.sign-in-button>button.submit';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_buttonSignInAnonymous */
    protected $buttonSignInAnonymous = 'div.signin-anonymous-box>button';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_checkCreateProfile */
    protected $checkCreateProfile = '#create_profile';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_inputPassword */
    protected $inputPassword = '#password';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_buttonShowPassword */
    protected $buttonShowPassword = '.fa.fa-eye-slash';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_buttonPlaceOrder */
    protected $buttonPlaceOrder = "button.place-order.submit";
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_inputLoginEmail */
    protected $inputLoginEmail = '#login-email';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_inputLoginPassword */
    protected $inputLoginPassword = '#login-password';
    
    public function validate() {
        //FIXME: Subj!
        return true;
    }
    /**
     * 
     * @return \RemoteWebElement
     */
    public function waitForCreateProfileCheckBox() {
        $cond = \WebDriverExpectedCondition::elementToBeClickable($this->checkCreateProfile);
        $this->driver->wait(30)->until($cond);
        return $this->driver->findElement($this->checkCreateProfile);
        
    }
    /**
     * 
     * @return \RemoteWebElement
     */
    public function waitForPasswordField() {
        $cond = \WebDriverExpectedCondition::elementToBeClickable($this->inputPassword);
        $this->driver->wait(30)->until($cond);
        return $this->driver->findElement($this->inputPassword);
    }
    
    /**
     * 
     * @return \RemoteWebElement
     */
    public function waitForPlaceOrderButton() {
        $cond = \WebDriverExpectedCondition::elementToBeClickable($this->buttonPlaceOrder);
        $this->driver->wait(30)->until($cond);
        return $this->driver->findElement($this->buttonPlaceOrder);
    }
}
