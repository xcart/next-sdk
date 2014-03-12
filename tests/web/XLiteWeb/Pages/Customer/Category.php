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
class Category extends \XLiteWeb\CustomerPage{
        
    public function validate() {
        // не ясно к чему тут прицепится, страница категории такая же как и остальные
        return true;
    }
    
    public function load($autologin = false) {
        //нужно продумать лоадер. пока он не нужен.
        return false;
    }
}
