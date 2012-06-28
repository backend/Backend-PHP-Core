<?php
/**
 * File defining Request
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Core
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core;
use Backend\Interfaces\RequestInterface;
use Backend\Core\Exception as CoreException;
/**
 * The Request class which helps determine the Path and request format.
 *
 * @category Backend
 * @package  Core
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Request implements RequestInterface
{
    protected $serverInfo = array();

    /**
     * The absolute path to the site. Used for links to assets.
     *
     * @var string
     */
    protected $sitePath = null;

    /**
     * The site end point. Used for calls to the site.
     *
     * @var string
     */
    protected $siteUrl = null;

    /**
     * The path of the request.
     *
     * @var string
     */
    protected $path   = null;

    /**
     * The payload of the request.
     *
     * @var array
     */
    protected $payload = null;

    /**
     * The method of the request. Can be one of GET, POST, PUT or DELETE.
     *
     * @var string
     */
    protected $method  = null;

    /**
     * The extension of the request.
     *
     * @var string
     */
    protected $extension = null;

    /**
     * Stream location to read content from.
     *
     * @var string
     */
    protected $inputStream = 'php://input';

    /**
     * HTTP Methods allowed in the Request.
     *
     * @var array
     */
    public static $allowedMethods = array(
        'DELETE', 'GET', 'HEAD', 'OPTIONS', 'POST', 'PUT'
    );

    /**
     * The constructor for the class
     *
     * If no method is supplied, it's determined by one of the following:
     * 1. A _method POST variable
     * 2. A X_HTTP_METHOD_OVERRIDE header
     * 3. The REQUEST_METHOD
     *
     * @param mixed  $url     The URL of the request
     * @param string $method  The request method. Can be one of GET, POST, PUT,
     * DELETE or HEAD
     * @param mixed  $payload The request data. Defaults to the HTTP request data
     * if not supplied
     */
    function __construct($url = null, $method = null, $payload = null)
    {
        if ($url === null) {
            $this->serverInfo = $_SERVER;
            $this->serverInfo['PATH_INFO']
                = array_key_exists('PATH_INFO', $_SERVER)
                    ? $_SERVER['PATH_INFO'] : '';
        } else {
            $this->parseUrl($url);
        }
        if ($method !== null) {
            $this->setMethod($method);
        }

        $this->setPayload($payload);
    }

    /**
     * Parse the given URL, and populate the object
     *
     * @param string $url The URL to parse
     *
     * @return Request The current object
     */
    protected function parseUrl($url)
    {
        $urlParts = parse_url($url);
        $this->serverInfo['HTTP_HOST']    = $urlParts['host'];
        $this->serverInfo['QUERY_STRING'] = array_key_exists('query', $urlParts)
            ? $urlParts['query'] : '';
        $urlParts['path']                 = array_key_exists('path', $urlParts)
            ? $urlParts['path'] : '';
        //TODO For now we're assuming all URL's passed will have an index.php in them
        if (strpos($urlParts['path'], 'index.php') === false) {
            if (substr($urlParts['path'], -1) != '/') {
                $urlParts['path'] .= '/';
            }
            $urlParts['path'] .= 'index.php';
        }
        $this->serverInfo['REQUEST_URI']  = $urlParts['path'];
        $pathInfo = explode('index.php', $urlParts['path'], 2);
        $this->serverInfo['PATH_INFO'] = end($pathInfo);
        $this->serverInfo['REQUEST_TIME'] = time();
        if ($urlParts['scheme'] == 'https') {
            $this->serverInfo['HTTPS']       = 'on';
            $this->serverInfo['SERVER_PORT'] = 443;
        } else {
            $this->serverInfo['SERVER_PORT'] = 80;
        }
        if (array_key_exists('port', $urlParts)) {
            $this->serverInfo['SERVER_PORT'] = $urlParts['port'];
        }
        //Keep all $_SERVER details we haven't set
        $this->serverInfo = array_merge($_SERVER, $this->serverInfo);
        return $this;
    }

    /**
     * Return the HTTP Method used to make the request.
     *
     * @return string
     */
    public function getMethod()
    {
        if ($this->method !== null) {
            return $this->method;
        }
        //Default to GET
        $method = 'GET';
        //Copied the way to determine the method from CakePHP
        //http://book.cakephp.org/2.0/en/development/rest.html#the-simple-setup
        switch (true) {
        case is_array($this->payload) && array_key_exists('_method', $this->payload):
            $method = $this->payload['_method'];
            break;
        case $this->getServerInfo('METHOD_OVERRIDE') !== null:
            $method = $this->getServerInfo('METHOD_OVERRIDE');
            break;
        //First CL parameter is the method
        case self::fromCli()
            && count($this->serverInfo['argv']) >= 2
            && in_array(strtoupper($this->serverInfo['argv'][1]), self::$allowedMethods):
            $method = $this->serverInfo['argv'][1];
            break;
        case $this->getServerInfo('request_method') !== null:
            $method = $this->getServerInfo('request_method');
            break;
        default:
            break;
        }
        $this->setMethod($method);
        return $this->method;
    }

    /**
     * Set the request's method
     *
     * @param string $method The Request Method
     *
     * @return Request The current object
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        if (!in_array($method, self::$allowedMethods)) {
            throw new CoreException('Unsupported method ' . $method);
        }
        $this->method = $method;
        $this->serverInfo['REQUEST_METHOD'] = $method;
        return $this;
    }

    /**
     * Return the path of the Request.
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->path === null) {
            $this->setPath(urldecode($this->serverInfo['PATH_INFO']));
        }
        return $this->path;
    }

    /**
     * Set and cleanup the path.
     *
     * The path should be URL decoded before calling this method.
     *
     * @param string $path The path
     *
     * @return Request The current object
     */
    public function setPath($path)
    {
        //Clean up the path
        //No trailing slash
        if (substr($path, -1) == '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }
        if (substr($path, 0, 1) != '/') {
            $path = '/' . $path;
        }

        $this->path = $path;

        //Remove the extension if present
        $extension = $this->getExtension();
        if ($extension) {
            $this->path = preg_replace(
                '/[_\.]' . $extension . '$/', '', $this->path
            );
        }
        return $this;
    }

    /**
     * Return the link that will result in this request
     *
     * @return string
     */
    public function getLink()
    {

    }

    /**
     * Determine the requested MIME Type for the request
     *
     * @return string The MIME Type for the request
     */
    public function getMimeType()
    {
        if (self::fromCli()) {
            return 'cli';
        } else if (array_key_exists('HTTP_ACCEPT', $this->serverInfo)) {
            //No format found, check if there's an Accept Header
            $mimeType = $this->serverInfo['HTTP_ACCEPT'];
            //Try to get the first type
            $types = explode(',', $mimeType);
            //Remove the preference variable
            $mimeType = explode(';', reset($types));
            return reset($mimeType);
        }
        return false;
    }

    /**
     * Determine the requested format for the request
     *
     * @return string The format for the request
     */
    public function getSpecifiedFormat()
    {
        //Check the format parameter
        if (is_array($this->payload) && array_key_exists('format', $this->payload)) {
            return $this->payload['format'];
        }

        //Third CL parameter is the required format
        if (self::fromCli() && count($this->serverInfo['argv']) >= 4) {
            return $this->serverInfo['argv'][3];
        }
        return false;
    }

    /**
     * Get the Request Extension
     *
     * @return string The extension of the request
     */
    public function getExtension()
    {
        if ($this->extension === null) {
            preg_match('/[^\/]+\.(.*)\??.*$/', $this->getPath(), $matches);
            if (!empty($matches[1])) {
                $this->extension = $matches[1];
            } else {
                $this->extension = false;
            }
        }
        return $this->extension;
    }

    /**
     * Get the Site URL
     *
     * @return string The API end point
     */
    public function getSiteUrl()
    {
        if ($this->siteUrl === null) {
            $this->prepareSiteUrl();
        }
        return $this->siteUrl;
    }

    /**
     * Prepare the SITE URL
     *
     * @return null
     */
    protected function prepareSiteUrl()
    {
        //Construct the current URL
        $this->siteUrl = 'http';
        if ($this->getServerInfo('SERVER_PORT') == 443
            || $this->getServerInfo('HTTPS') == 'on'
        ) {
            $this->siteUrl .= 's';
        }
        $this->siteUrl .= '://' . $this->getServerInfo('HOST');
        if ('index.php' == basename($this->getServerInfo('PHP_SELF'))) {
            $this->siteUrl .= $this->serverInfo['PHP_SELF'];
        } else {
            $pattern = '/' . str_replace('/', '\\/', $this->getServerInfo('PATH_INFO'))
                . '$/';
            $subject = explode('?', $this->getServerInfo('REQUEST_URI'));
            $subject = reset($subject);
            $this->siteUrl .= preg_replace($pattern, '', $subject);
        }
        if (substr($this->siteUrl, -1) != '/') {
            $this->siteUrl .= '/';
        }
    }

    /**
     * Get the Site Path
     *
     * @return string The Site Path
     */
    public function getSitePath()
    {
        if ($this->sitePath !== null) {
            $path = dirname($this->getSiteUrl());
            if (substr($path, -1) === '/') {
                $path = substr($path, 0, -1);
            }
            $this->sitePath = $path;
        }
        return $this->sitePath;
    }

    /**
     * Return the serverInfo property
     *
     * @param string $name A specific piece of Server Info
     *
     * @return array The serverInfo property
     */
    public function getServerInfo($name = null)
    {
        if ($name === null) {
            return $this->serverInfo;
        }
        if (in_array($name, array('argv')) === false) {
            $name = strtoupper($name);
        }
        if (substr($name, 0, 2) === 'X_') {
            $name = substr($name, 2);
        }
        switch (true) {
        case array_key_exists($name, $this->serverInfo):
            return $this->serverInfo[$name];
            break;
        case array_key_exists('HTTP_' . $name, $this->serverInfo):
            return $this->serverInfo['HTTP_' . $name ];
            break;
        //Check for deprecated X- values http://tools.ietf.org/html/rfc6648
        case array_key_exists('X_' . $name, $this->serverInfo):
            return $this->serverInfo['X_' . $name ];
            break;
        case array_key_exists('X_HTTP_' . $name, $this->serverInfo):
            return $this->serverInfo['X_HTTP_' . $name ];
            break;
        default:
            break;
        }
        return null;
    }

    /**
     * Set serverInfo values.
     *
     * @param string $name  The name of the serverInfo value to set.
     * @param string $value The value of the serverInfo value.
     *
     * @return Request The current object
     */
    public function setServerInfo($name, $value)
    {
        if (in_array($name, array('argv')) === false) {
            $name = strtoupper($name);
        }
        $this->serverInfo[$name] = $value;
    }

    /**
     * Utility function to check if the current method equals the specified method
     *
     * @param string $method The method to check
     *
     * @return boolean If the current method equals the specified method
     */
    protected function isMethod($method)
    {
        return strtoupper($method) == $this->method;
    }

    /**
     * Check if the current request is a DELETE request
     *
     * @return boolean If the current request is a DELETE request
     */
    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    /**
     * Check if the current request is a GET request
     *
     * @return boolean If the current request is a GET request
     */
    public function isGet()
    {
        return $this->isMethod('GET');
    }

    /**
     * Check if the current request is a HEAD request
     *
     * @return boolean If the current request is a HEAD request
     */
    public function isHead()
    {
        return $this->isMethod('HEAD');
    }

    /**
     * Check if the current request is a OPTIONS request
     *
     * @return boolean If the current request is a OPTIONS request
     */
    public function isOptions()
    {
        return $this->isMethod('OPTIONS');
    }

    /**
     * Check if the current request is a POST request
     *
     * @return boolean If the current request is a POST request
     */
    public function isPost()
    {
        return $this->isMethod('POST');
    }

    /**
     * Check if the current request is a PUT request
     *
     * @return boolean If the current request is a PUT request
     */
    public function isPut()
    {
        return $this->isMethod('PUT');
    }

    /**
     * Parse the content / body of the Request
     *
     * @param string $type    The type of the content
     * @param string $content The content to parse
     *
     * @todo Expand this to include XML and other content types
     * @todo Refactor so that we can use thirdparty / external parsers
     * @return array The payload as an array? This might change
     */
    public function parseContent($type, $content = null)
    {
        if ($type === null) {
            return null;
        }
        if ($content === null) {
            $data     = '';
            $fpointer = fopen($this->getInputStream(), 'r');
            while ($fpointer && $chunk = fread($fpointer, 1024)) {
                $data .= $chunk;
            }
        } else {
            $data = $content;
        }
        $payload = null;
        switch ($type) {
        case 'application/json':
        case 'text/json':
        case 'text/javascript':
            $payload = json_decode($data);
            $payload = is_object($payload) ? (array)$payload : $payload;
            break;
        case 'application/x-www-form-urlencoded':
        case 'text/plain':
            parse_str($data, $payload);
            break;
        case 'application/xml':
            //TODO
        default:
            throw new CoreException(
                'Unknown Content Type: ' . $type,
                400
            );
            break;
        }
        return $payload;
    }

    /**
     * Return the request's payload.
     *
     * @return array The Request Payload
     */
    public function getPayload()
    {
        if ($this->payload !== null) {
            return $this->payload;
        }
        if ($this->fromCli()) {
            if (count($this->serverInfo['argv']) >= 5) {
                //Fourth CL parameter is a query string
                parse_str($this->serverInfo['argv'][4], $queryVars);
                $this->payload = $queryVars;
                return $this->payload;
            }
        }
        $payload = $this->parseContent($this->getServerInfo('content_type'));
        if ($payload) {
            $this->setPayload($payload);
            return $this->payload;
        }
        $payload = null;
        switch ($this->getMethod()) {
        case 'GET':
            $payload = isset($_GET) ? $_GET : array();
            break;
        case 'POST':
        case 'PUT':
            $payload = isset($_POST) ? $_POST : array();
            break;
        }
        if ($payload === null ) {
            $payload = isset($_REQUEST) ? $_REQUEST : array();
        }
        $this->setPayload($payload);
        return $this->payload;
    }

    /**
     * Set the request's payload.
     *
     * Strings will be parsed for variables and objects will be casted to arrays.
     *
     * @param mixed $payload The Request's Payload
     *
     * @return Request The current object.
     */
    public function setPayload($payload)
    {
        if (is_string($payload)) {
            parse_str($payload, $payload);
        } else if (is_object($payload)) {
            $payload = (array)$payload;
        }
        $this->payload = $payload;
        return $this;
    }

    /**
     * Check if this requests originates from a CLI.
     *
     * @return boolean If this is a CLI request
     */
    public function fromCli()
    {
        return array_key_exists('REQUEST_METHOD', $this->serverInfo) === false
            && array_key_exists('argv', $this->serverInfo);
    }

    /**
     * Set the input stream.
     *
     * @param string $stream The stream.
     *
     * @return Request The current object.
     */
    public function setInputStream($stream)
    {
        $this->inputStream = $stream;
    }

    /**
     * Get the input stream.
     *
     * @return string
     */
    public function getInputStream()
    {
        return $this->inputStream;
    }
}
