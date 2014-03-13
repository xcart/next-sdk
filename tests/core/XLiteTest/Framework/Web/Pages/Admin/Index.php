<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages\Admin;

/**
 * Description of Index
 *
 * @author givi
 */
class Index extends \XLiteTest\Framework\Web\Pages\AdminPage{
        
    public function validate() {
        return $this->isLogedIn();
    }
}
