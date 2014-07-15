<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages;
use XLiteTest\Framework\Config;

/**
 * Description of Page
 *
 * @author givi
 */
class Page {    
    /**
    * Description of Page
    *
    * @var  \RemoteWebDriver
    */
    protected $driver;
    
    protected $webElementsGetters = array();
    /**
    * Base URL
    *
    * @var  String
    */
    protected $storeUrl;
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $errorMessage = '.error';
    
    public function getConfig($section, $key)
    {
        return Config::getInstance()->getOptions($section, $key);
    }
    
    public function initializeComponents()
    {
        $reflectionClass = new \ReflectionClass(get_class($this));
        $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PROTECTED);
        
        foreach ($properties as $property) {
            $propertyAnnotation = $property->getDocComment();
            $propertyName = $property->getName();
            
            $mathes = array();
            if (1 == preg_match("/@findBy[ ]*'(.*)'/", $propertyAnnotation, $mathes)) {
                $type = $mathes[1];
                $this->$propertyName = \WebDriverBy::$type($this->$propertyName);
                $this->webElementsGetters['get_' . $propertyName]=$propertyName;
            }
        }
    }
    
    public function __construct(\RemoteWebDriver $driver, $storeUrl) {
        $this->initializeComponents();
        $this->driver = $driver;
        $this->storeUrl = $storeUrl;
    }
    
    public function load($autologin = false) {
        return true;
    }
    
    public function validate() {
        return false;
    }
    
    public function isErrorOnPage() {
        
        try {
            $this->driver->findElement($this->errorMessage);
            return true;
        } catch (\WebDriverException $e) {
            return false;
        }
        
    }
    
    public function takeScreenshot() {
        $file = "./" . date('h-i-s', time()) . "-" . time() . ".png";
        $this->driver->takeScreenshot($file);
    }

    public function getErrorText() {
        if ($this->isErrorOnPage()) {
            return $this->driver->findElement($this->errorMessage)->getText();
        } else {
            return '';
        }
    }
    /**
     * 
     * @param \WebDriverBy $by
     * @param \RemoteWebElement $element
     * @return boolean
     */
    public function isElementPresent(\WebDriverBy $by, \RemoteWebElement $element = null) {
        if ($element == null) {
            $driver = $this->driver;
        } else {
            $driver = $element;
        }
        try {
            $el = $driver->findElement($by);
            return true;
        } catch (\WebDriverException $e) {
            return false;
        }
    }
    
    public function elementClassNotDisabled(\WebDriverBy $by, \RemoteWebElement $element = null) {
        return function($driver) use ($by) {
            try {
                $el = $driver->findElement($by);
            } catch (\WebDriverException $e) {
                return null;
            }
            if(strpos($el->getAttribute('class'), 'disabled') == FALSE) {
                return true;
            } else {
                return false;
            }
        };
    }

    public function waitForAjax($timeout=30 ,\WebDriverBy $element = null) {
        if ($element == null) {
            $element = \WebDriverBy::cssSelector('div.block-wait');
        } 
        if ($timeout <= 0) {
            $timeout = 1;
        }
        
        $timeout = $timeout * 2;
        
        while ($timeout > 0) {
            usleep(500000);
            if (!$this->isElementPresent($element)) {
                return $this;
            }
            $timeout--;
        }
        throw new \Exception('Ajax wait timeout');
    }
    
    public function __get($name) {
        if (isset($this->webElementsGetters[$name])) {
            $propertyName = $this->webElementsGetters[$name];
            $by = $this->$propertyName;
            return $this->driver->findElement($by);
        }
        throw new \Exception('Unknown property.');
    }
    
    protected function createComponent($path)
    {
        $className = '\\XLiteTest\\Framework\\Web\\Pages' . $path;

        return new $className($this->driver, $this->storeUrl);
    }
    
    public function fillForm($data) {
        foreach ($data as $element=>$value) {
            $methodName = 'input' . ucfirst(str_replace('-', '_', $element));
            if (method_exists ( $this, $methodName )) {
                $this->$methodName($value);
            } else {
                $by = \WebDriverBy::cssSelector('#' . $element);
                $webElement = $this->driver->findElement($by);
                $tag = $this->driver->findElement($by)->getTagName();
                
                if ($tag == 'select' && !is_array($value)) {
                    $Select = new \WebDriverSelect($webElement);
                    $Select->selectByValue($value);
                } elseif ($tag == 'select' && is_array($value)) {//multiselect
                    $multiSelect = new \WebDriverSelect($webElement);
                    $multiSelect->deselectAll();
                    foreach ($value as $item) {
                        $multiSelect->selectByValue($item);
                    }
                } elseif ($tag == 'textarea') {
                    if ($webElement->isDisplayed()) {
                        $webElement->sendKeys($value);
                    } else {
                        $this->driver->executeScript('$("#' . $element . '").text("' . $value . '");');
                    }
                } else {
                    $webElement->sendKeys($value);
                }
            }
        }
    }
}
