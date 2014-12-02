<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * LiteCommerce
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@litecommerce.com so we can send you a copy immediately.
 * 
 * PHP version 5.3.0
 * 
 * @category  LiteCommerce
 * @author    Creative Development LLC <info@cdev.ru> 
 * @copyright Copyright (c) 2010-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 */

/**
 * REST API client 
 */
class RESTAPIClient extends \GuzzleHttp\Client
{
    
    CONST DEFAULT_MODE = 'default';

    /**
     * Factory 
     *
     * @param string $url URL
     * @param string $key Access key
     *  
     * @return \RESTAPIClient
     */
    public static function factory($url, $key, $mode = 'default')
    {
        $client = new static(array('base_url' => $url));
        $client->setKey($key);
        $client->setMode($mode);

        return $client;
    }

    public function setMode($mode) 
    {
        if (!in_array($mode, $this->getAllowedModes())) {
            $mode = static::DEFAULT_MODE;
        }

        $this->mode = $mode;
    }

    public function getMode() 
    {
        return $this->mode;
    }

    protected function getAllowedModes() 
    {
        return array ('default', 'complex');
    }


    /**
     * Set key
     *
     * @param string $key Access key
     *
     * @return void
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    // {{{ Interfaces

    public function get($uri = null, $options = array())
    {
        return parent::get($this->assembleAPIURI($uri), $options);
    }

    public function head($uri = null, array $options = array())
    {
        throw new \Exception('HEAD request not supported');
    }

    public function delete($uri = null, array $options = array())
    {
        return parent::delete($this->assembleAPIURI($uri), $options);
    }

    public function put($uri = null, array $options = array())
    {
        if (isset($options['body']) && is_array($options['body'])) {
            $options['body'] = http_build_query(array('model' => $options['body']));
        }

        return parent::put($this->assembleAPIURI($uri), $options);
    }

    public function patch($uri = null, array $options = array())
    {
        throw new \Exception('PATCH request not supported');
    }

    public function post($uri = null, array $options = array())
    {
        $request = $this->createRequest('POST', $this->assembleAPIURI($uri));

        if (isset($options['body']) && is_array($options['body'])) {
            $postBody = $request->getBody();
            $postBody->setField('model', json_encode($options['body']));
        }

        return $this->send($request);
    }

    public function options($uri = null, array $options = array())
    {
        throw new \Exception('OPTIONS request not supported');
    }

    /**
     * Assemble full URI
     *
     * @param string $uri Short URI
     *
     * @return string
     */
    protected function assembleAPIURI($uri)
    {
        return $this->getBaseUrl() . '?target=RESTAPI&_key=' . $this->getKey() . '&_schema=' . $this->getMode() . '&_path=' . $uri;
    }

    // }}}

}