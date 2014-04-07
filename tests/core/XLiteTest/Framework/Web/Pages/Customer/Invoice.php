<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages\Customer;

/**
 * Description of Invoice
 *
 * @author givi
 */
class Invoice extends \XLiteTest\Framework\Web\Pages\CustomerPage {
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_textInvoiceHeader */
    protected $textInvoiceHeader = 'h2.invoice';
    
    /** @findBy 'cssSelector' @var \WebDriverBy 
    * @property \RemoteWebElement $get_textEmail */
    protected $textEmail = '.email>a';
    
    /**
    * 
    * @return boolean
    */   
    public function validate() {
        return $this->isElementPresent($this->textInvoiceHeader);
    }
    
    public function getInvoiceNumber() {
        $number = false;
        $text = $this->get_textInvoiceHeader->getText();
	if (preg_match('/#(\d+)/', $text, $m)) {
            $number = $m[1];
        }
	return $number;
    }
    
    public function checkAddresses($data) {
        $output = '';
        $sections = array('shipping','payment');
        foreach ($sections as $section) {
            foreach ($data as $field => $value){
                $by = \WebDriverBy::cssSelector("td.${section} li.address-$field>span.address-field"); 
                $text = $this->driver->findElement($by)->getText();
                if ($text != $value) {
                    $output .= "\nField '$field' does not match. expected($value) given($text)\n";
                }
            }
        }
        if ($output!='') {
            return $output;
        } else {
            return true;
        }
    }
    
}
