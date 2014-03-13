<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteWeb;
use XLiteTest\Framework\Config;

/**
 * Abstract web test case
 *
 * @package XLiteWeb
 */
abstract class AXLiteWeb extends \XLiteTest\Framework\TestCase
{
    /**
     * Storefront browser
     *
     * @var \WebDriver
     */
    private $storefrontDriver = null;

    /**
     * Backend browser
     *
     * @var \WebDriver
     */
    private $backendDriver = null;
    
    protected $capabilities = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    public function setUp()
    {

        $this->capabilities = array(
            \WebDriverCapabilityType::BROWSER_NAME => Config::getInstance()->getOptions('web_driver', 'browser_name')
        );


        parent::setUp();
    }
    
    public function getConfig($section, $key)
    {
        return Config::getInstance()->getOptions($section, $key);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        if ($this->storefrontDriver != null) {
            $this->storefrontDriver->quit();
        }
        if ($this->backendDriver != null) {
            $this->backendDriver->quit();
        }

        parent::tearDown();
    }
    /**
     * 
     * @return \RemoteWebDriver
     */
    
    private function getWebdriverInstance()
    {
        $driver = \RemoteWebDriver::create($this->getConfig('web_driver', 'driver_url'), $this->capabilities);
        $driver->manage()->timeouts()->implicitlyWait($this->getConfig('web_driver', 'implicitlyWait'));
        $driver->manage()->timeouts()->pageLoadTimeout($this->getConfig('web_driver', 'pageLoadTimeout'));
        $driver->manage()->timeouts()->setScriptTimeout($this->getConfig('web_driver', 'scriptTimeout'));
        $driver->manage()->window()->maximize();
        
        return $driver;
    }


    public function getBackendDriver() {
        if ($this->backendDriver == null) {
            // Start backend browser
            $this->backendDriver = $this->getWebdriverInstance();
            
        }
        return $this->backendDriver;
    }
    
    public function getStorefrontDriver() {
        if ($this->storefrontDriver == null) {        
            // Start storefront browser
            $this->storefrontDriver = $this->getWebdriverInstance();
        }
        return $this->storefrontDriver;
    }

    
    /**
     * @return \XLiteWeb\Pages\Customer\Index
     */
    public function getPage($path)
    {
        $className = '\\XLiteTest\\Framework\\Web\\Pages\\' . $path;
        if (strpos($path, 'Admin') === 0) {
            $driver = $this->getBackendDriver();
        } elseif (strpos($path, 'Customer') === 0) {
            $driver = $this->getStorefrontDriver();
        } else {
            throw new \Exception('Page object not found by given path.');
        }
        return new $className($driver, $this->getConfig('web_driver', 'store_url'));
    }
} 