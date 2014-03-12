<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteWeb\Pages\Customer;

/**
 * Description of Index
 *
 * @author givi
 */
class Index extends \XLiteWeb\CustomerPage{
        
    public function validate() {
        return $this->isElementPresent($this->logInLink) || $this->isElementPresent($this->logOffLink);
    }
    
    public function load($autologin = false) {
        $result = true;
        $this->driver->get($this->storeUrl . 'cart.php');
        if ($autologin === true && !$this->isLogedIn()) {
            $result = parent::load(true);   
            if ($result === true) {
                $this->driver->get($this->storeUrl . 'cart.php');
            }
        }
        return $result;
    }
}
