<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages;

/**
 * Description of Component
 *
 * @author givi
 */
class Component extends \XLiteTest\Framework\Web\Pages\Page{
    
    private $component = null;
    
    protected $findBy = null; 
    
    /**
    * 
    * @return \RemoteWebElement
    * @throws Exception
    */
    public function getComponent() {
        
        if ($this->findBy == null) {
            throw new \Exception('Error in component declaration. findBy is null.');
        }
        if ($this->component == null) {
            $this->component = $this->driver->findElement($this->findBy);
        }
        return $this->component;
    }
    
    public function isElementPresent(\WebDriverBy $by, \RemoteWebElement $element = null) {
        return parent::isElementPresent($by, $this->component);
    }

}
