<?php
namespace XLiteWeb\tests;
use XLiteTest\Framework\Config;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of testLogin
 *
 * @author givi
 */
class testLogin extends \XLiteWeb\AXLiteWeb{
    
    
    public function testLogin() {
        
        $login = $this->getPage('Admin\Login');
        $login->load();
        $this->assertTrue($login->validate(),'Submit button not found');
        
        $userName = Config::getInstance()->getOptions('admin_user', 'username');
        $password = Config::getInstance()->getOptions('admin_user', 'password');
        
        $login->inputEmail($userName);
        $login->inputPassword($password);
        
        $login->submit();
        $this->assertFalse($login->isErrorOnPage(),$login->getErrorText());

        $dashBoard = $this->getPage('Admin\Index');
        $this->assertTrue($dashBoard->validate(), 'Error login in to ADMIN zone.');
    }
}
