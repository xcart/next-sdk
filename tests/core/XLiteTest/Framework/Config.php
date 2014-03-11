<?php
// vim: set ts=4 sw=4 sts=4 et:

namespace XLiteTest\Framework;


/**
 * Singleton config class
 */
class Config
{
    /**
     * Instance
     *
     * @var Config
     */
    protected static $instance;

    /**
     * Config settings
     *
     * @var array
     */
    protected static $config;

    /**
     * Method to access a singleton
     *
     * @return \XLiteTest\Framework\Config
     */
    public static function getInstance()
    {
        // Create new instance of the object (if it is not already created)
        if (!isset(static::$instance)) {
            static::$instance = new Config();
        }
        return static::$instance;
    }

    /**
     * Protected constructor
     */
    protected function __construct()
    {
        static::setConfig();
    }

    /**
    * Set config options from config file
    *
    * @return array
    */
    protected function setConfig()
    {
        $configFile = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "config.php";

        if (file_exists($configFile) && false !== ($config = parse_ini_file($configFile, true))) {
            static::$config = $config;
        } else {
            die('Config file not found: ' . $configFile);
        }
    }

    /**
     * Get option from $config variable
     *
     * @param $section
     * @param $name
     * @return string
     */
    public function getOptions($section, $name)
    {
        return static::$config[$section][$name];
    }

}
