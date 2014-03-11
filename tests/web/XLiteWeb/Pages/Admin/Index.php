<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteWeb\Pages\Admin;

/**
 * Description of Index
 *
 * @author givi
 */
class Index extends \XLiteWeb\Page{
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $logOffLink = ".link-item.logoff>a>span";
    
    public function validate() {
        return $this->isElementPresent($this->logOffLink);
    }
}
