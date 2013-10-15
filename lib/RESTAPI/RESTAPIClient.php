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
class RESTAPIClient extends \Guzzle\Http\Client
{
    /**
     * Factory 
     *
     * @param string $url URL
     * @param string $key Access key
     *  
     * @return \RESTAPIClient
     */
    public static function factory($url, $key)
    {
        $client = new static($url);
        $client->setKey($key);

        return $client;
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

    /**
     * Create a GET request for the client
     *
     * @param string|array                              $uri     Resource URI
     * @param array|Collection                          $headers HTTP headers
     * @param string|resource|array|EntityBodyInterface $body    Where to store the response entity body
     *
     * @return RequestInterface
     * @see    \Guzzle\Http\ClientInterface::createRequest()
     */
    public function get($uri = null, $headers = null, $body = null)
    {
        return parent::get($this->assembleAPIURI($uri), $headers, $body);
    }

    /**
     * Create a HEAD request for the client
     *
     * @param string|array     $uri     Resource URI
     * @param array|Collection $headers HTTP headers
     *
     * @return RequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function head($uri = null, $headers = null, array $options = array())
    {
        throw new \Exception('HEAD request not supported');
    }

    /**
     * Create a DELETE request for the client
     *
     * @param string|array     $uri     Resource URI
     * @param array|Collection $headers HTTP headers
     * @param string|resource|EntityBodyInterface $body    Body to send in the request
     *
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function delete($uri = null, $headers = null, $body = null, array $options = array())
    {
        return parent::delete($this->assembleAPIURI($uri), $headers, $body, $options);
    }

    /**
     * Create a PUT request for the client
     *
     * @param string|array                        $uri     Resource URI
     * @param array|Collection                    $headers HTTP headers
     * @param string|resource|EntityBodyInterface $body    Body to send in the request
     *
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function put($uri = null, $headers = null, $body = null, array $options = array())
    {
        if (is_array($body)) {
            $body = http_build_query(array('model' => $body));
        }

        return parent::put($this->assembleAPIURI($uri), $headers, $body, $options);
    }

    /**
     * Create a PATCH request for the client
     *
     * @param string|array                        $uri     Resource URI
     * @param array|Collection                    $headers HTTP headers
     * @param string|resource|EntityBodyInterface $body    Body to send in the request
     *
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function patch($uri = null, $headers = null, $body = null, array $options = array())
    {
        throw new \Exception('PATCH request not supported');
    }

    /**
     * Create a POST request for the client
     *
     * @param string|array                                $uri      Resource URI
     * @param array|Collection                            $headers  HTTP headers
     * @param array|Collection|string|EntityBodyInterface $postBody POST body. Can be a string, EntityBody, or
     *                                                    associative array of POST fields to send in the body of the
     *                                                    request.  Prefix a value in the array with the @ symbol to
     *                                                    reference a file.
     * @return EntityEnclosingRequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
    public function post($uri = null, $headers = null, $postBody = null, array $options = array())
    {
        return parent::post($this->assembleAPIURI($uri), $headers, $postBody, $options);
    }

    /**
     * Create an OPTIONS request for the client
     *
     * @param string|array $uri Resource URI
     *
     * @return RequestInterface
     * @see    Guzzle\Http\ClientInterface::createRequest()
     */
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
        return $this->getBaseUrl() . '?target=RESTAPI&_key=' . $this->getKey() . '&_path=' . $uri;
    }

    // }}}

}

