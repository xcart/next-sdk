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
    protected $storefrontDriver;

    /**
     * Backend browser
     *
     * @var \WebDriver
     */
    protected $backendDriver;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    public function setUp()
    {

        $capabilities = array(
            \WebDriverCapabilityType::BROWSER_NAME => Config::getInstance()->getOptions('web_driver', 'browser_name')
        );

        // Start storefront browser
        $this->storefrontDriver = \RemoteWebDriver::create(Config::getInstance()->getOptions('web_driver', 'driver_url'), $capabilities);

        // Start backend browser
        $this->backendDriver = \RemoteWebDriver::create(Config::getInstance()->getOptions('web_driver', 'driver_url'), $capabilities);

        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown()
    {
        $this->storefrontDriver->close();
        $this->backendDriver->close();

        parent::tearDown();
    }
    
    /**
     * 
     */
    public function getPage($path)
    {
        $var = '\\XLiteWeb\\Pages\\' . $path;
        if (strpos($path, 'Admin') === 0) {
            return new $var($this->backendDriver);
        } elseif (strpos($path, 'Customer') === 0) {
            return new $var($this->storefrontDriver);
        }
    }
} 