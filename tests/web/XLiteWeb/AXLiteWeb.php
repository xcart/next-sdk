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
            $this->storefrontDriver->close();
        }
        if ($this->backendDriver != null) {
            $this->backendDriver->close();
        }

        parent::tearDown();
    }
    
    public function getBackendDriver() {
        if ($this->backendDriver == null) {
            // Start backend browser
            $this->backendDriver = \RemoteWebDriver::create($this->getConfig('web_driver', 'driver_url'), $this->capabilities);
        }
        return $this->backendDriver;
    }
    
    public function getStorefrontDriver() {
        if ($this->storefrontDriver == null) {        
            // Start storefront browser
            $this->storefrontDriver = \RemoteWebDriver::create($this->getConfig('web_driver', 'driver_url'), $this->capabilities);
        }
        return $this->storefrontDriver;
    }

    
    /**
     * @return \XLiteWeb\Pages\Customer\Index
     */
    public function getPage($path)
    {
        $store_url = $this->getConfig('web_driver', 'store_url');
        $var = '\\XLiteWeb\\Pages\\' . $path;
        if (strpos($path, 'Admin') === 0) {
            return new $var($this->getBackendDriver(), $store_url);
        } elseif (strpos($path, 'Customer') === 0) {
            return new $var($this->getStorefrontDriver(), $store_url);
        }
    }
} 