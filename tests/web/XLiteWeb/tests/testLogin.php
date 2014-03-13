<?php
namespace XLiteWeb\tests;

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
    
    
    public function testAdminLogin() {
        
        $login = $this->getPage('Admin\Login');
        $login->load();
        $this->assertTrue($login->validate(),'Submit button not found');
        
        $userName = $this->getConfig('admin_user', 'username');
        $password = $this->getConfig('admin_user', 'password');
        
        $login->inputEmail($userName);
        $login->inputPassword($password);
        
        $login->submit();
        $this->assertFalse($login->isErrorOnPage(),$login->getErrorText());

        $dashBoard = $this->getPage('Admin\Index');
        $this->assertTrue($dashBoard->validate(), 'Error login in to ADMIN zone.');
    }
}
