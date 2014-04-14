<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages\Admin;

/**
 * Description of Orders
 *
 * @author givi
 */
class Orders extends \XLiteTest\Framework\Web\Pages\AdminPage {
    
    /** @findBy 'cssSelector' @var \WebDriverBy */
    protected $searchOrdersTabActive = ".menu-item.active>a[href*='?target=order_list']";
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_inputSearchBy */
    protected $inputSearchBy = '#substring';
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_buttonSearch */
    protected $buttonSearch = 'form.searchpanel-order-admin-main .actions>button';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_firstLastRow */
    protected $firstLastRow = 'tr.first.last';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_buttonSaveChanges */
    protected $buttonSaveChanges = 'button.regular-main-button.action.submit';
     /**
    * 
    * @return boolean
    */    
    public function validate() {
        return $this->isElementPresent($this->searchOrdersTabActive);
    }
    
    /**
    * 
    * @return boolean
    */    
    public function load($autologin = false) {
        
        $result = true;
        $this->driver->get($this->storeUrl . 'admin.php?target=order_list');
        if ($autologin === true && !$this->isLogedIn()) {
            $result = parent::load(true);   
            if ($result === true) {
                $this->driver->get($this->storeUrl . 'admin.php?target=order_list');

                }
        }
        return $result;
    }
    /**
     * 
     * @return boolean|array
     */
    public function getOrderId() {
        $data = $this->get_firstLastRow->getAttribute('class');
        $matches = array();
        preg_match('/entity-([0-9]+)/', $data, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return false;
    }
    
    /**
     * 
     * @param int $orderId
     * @return \WebDriverSelect
     */
    public function selectStatus($orderId) {
        $element = $this->driver->findElement(\WebDriverBy::cssSelector("#data-${orderId}-paymentstatus"));
        return new \WebDriverSelect($element);
    }
    
}
