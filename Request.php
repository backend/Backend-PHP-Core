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
use Backend\Core\Utilities\ApplicationEvent;
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
     * @var string The absolute path to the site. Used for links to assets
     */
    protected $sitePath = null;

    /**
     * @var string The site end point. Used for calls to the site
     */
    protected $siteUrl = null;

    /**
     * The path of the request.
     *
     * @var string
     */
    protected $path   = null;

    /**
     * @var array The payload of the request
     */
    protected $payload = null;

    /**
     * @var string The method of the request. Can be one of GET, POST, PUT or DELETE
     */
    protected $method  = null;

    /**
     * @var string The extension of the request.
     */
    protected $extension = null;

    public static function fromState()
    {
        return new self();
    }

    /**
     * The constructor for the class
     *
     * If no method is supplied, it's determined by one of the following:
     * 1. A _method POST variable
     * 2. A X_HTTP_METHOD_OVERRIDE header
     * 3. The REQUEST_METHOD
     *
     * @param mixed  $url     The URL of the request
     * @param string $method  The request method. Can be one of GET, POST, PUT, DELETE or HEAD
     * @param mixed  $payload The request data. Defaults to the HTTP request data if not supplied
     */
    function __construct($url = null, $method = null, $payload = null)
    {
        if (is_null($url)) {
            $this->serverInfo = $_SERVER;
            $this->serverInfo['PATH_INFO'] = array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : '';
        } else {
            $this->parseUrl($url);
            $method = is_null($method) ? 'GET' : $method;
        }
        if (!is_null($method)) {
            $this->setMethod($method);
        }

        if (is_null($payload)) {
            $payload = $this->getPayload();
        } else if (is_string($payload)) {
            parse_str($payload, $payload);
        } else if (is_object($payload)) {
            $payload = (array)$payload;
        } else if (is_array($payload)) {
            $payload = $payload;
        }
        $this->setPayload($payload);

        $message = 'Request: ' . $this->getMethod() . ': ' . $this->getPath();
        new ApplicationEvent($message, ApplicationEvent::SEVERITY_DEBUG);
    }

    /**
     * Parse the given URL, and populate the object
     *
     * @param string $url The URL to parse
     *
     * @return Object The current object
     */
    protected function parseUrl($url)
    {
        $urlParts = parse_url($url);
        $this->serverInfo['HTTP_HOST']    = $urlParts['host'];
        $this->serverInfo['QUERY_STRING'] = array_key_exists('query', $urlParts) ? $urlParts['query'] : '';
        $urlParts['path']                 = array_key_exists('path', $urlParts) ? $urlParts['path'] : '';
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
        //$this->serverInfo['PATH_INFO'] = str_replace($_SERVER['SCRIPT_NAME'], '', $urlParts['path']);
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
     * Return the link that will result in this request
     *
     * @return string
     */
    public function getLink()
    {

    }

    /**
     * Return the path of the Request.
     *
     * @return string
     */
    public function getPath()
    {
        if (is_null($this->path)) {
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
     * @return Object The current object
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
        if ($extension = $this->getExtension()) {
            $this->path = preg_replace('/[_\.]' . $extension . '$/', '', $this->path);
        }
        return $this;
    }

    /**
     * Get the Site URL
     *
     * @return string The API end point
     */
    public function getSiteUrl()
    {
        if (is_null($this->siteUrl)) {
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
        if ($this->serverInfo['SERVER_PORT'] == 443
            || (!empty($this->serverInfo['HTTPS']) && $this->serverInfo['HTTPS'] == 'on')
        ) {
            $this->siteUrl .= 's';
        }
        $this->siteUrl .= '://' . $this->serverInfo['HTTP_HOST'];
        if ('index.php' == basename($this->serverInfo['PHP_SELF'])) {
            $this->siteUrl .= $this->serverInfo['PHP_SELF'];
        } else {
            $pattern = '/' . str_replace('/', '\\/', $this->serverInfo['PATH_INFO']) . '$/';
            $subject = explode('?', $this->serverInfo['REQUEST_URI']);
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
        if (is_null($this->sitePath)) {
            $this->prepareSitePath();
        }
        return $this->sitePath;
    }

    /**
     * Prepare the Site Path
     *
     * @return null
     */
    protected function prepareSitePath()
    {
        $path = dirname($this->getSiteUrl());
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        $this->sitePath = $path;
    }

    /**
     * Return the serverInfo property
     *
     * @return array The serverInfo property
     */
    public function getServerInfo()
    {
        return $this->serverInfo;
    }

    /**
     * Determine the requested format for the request
     *
     * @return string The format for the request
     */
    public function getSpecifiedFormat()
    {
        //Check the format parameter
        if (array_key_exists('format', $this->payload)) {
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
        if (is_null($this->extension)) {
            $this->prepareExtension();
        }
        return $this->extension;
    }

    /**
     * Determine the extension for the request
     *
     * @return string The extension for the request
     */
    public function prepareExtension()
    {
        preg_match('/[^\/]+\.(.*)\??.*$/', $this->getPath(), $matches);
        if (!empty($matches[1])) {
            $this->extension = $matches[1];
        } else {
            $this->extension = false;
        }
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
     * Return the HTTP Method used to make the request.
     *
     * @return string
     */
    public function getMethod()
    {
        if (!is_null($this->method)) {
            return $this->method;
        }
        //Copied the way to determine the method from CakePHP
        //http://book.cakephp.org/2.0/en/development/rest.html#the-simple-setup
        switch (true) {
        case array_key_exists('_method', $_POST):
            $method = $_POST['_method'];
            break;
        case array_key_exists('X_HTTP_METHOD_OVERRIDE', $this->serverInfo):
            $method = $this->serverInfo['X_HTTP_METHOD_OVERRIDE'];
            break;
        default:
            if (self::fromCli()) {
                //First CL parameter is the method
                $method = count($this->serverInfo['argv']) >= 2
                    ? $this->serverInfo['argv'][1] : 'GET';
            } else {
                $method = $this->serverInfo['REQUEST_METHOD'];
            }
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
        if (!in_array($method, array('DELETE', 'GET', 'HEAD', 'OPTIONS', 'POST', 'PUT'))) {
            throw new Exceptions\UnsupportedHttpMethodException('Unsupported method ' . $method);
        }
        $this->method = $method;
        $this->serverInfo['REQUEST_METHOD'] = $method;
        return $this;
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
     * Return the request's payload.
     *
     * @return array The Request Payload
     */
    public function getPayload()
    {
        if (!is_null($this->payload)) {
            return $this->payload;
        }
        if (self::fromCli()) {
            $payload = array(
                //Second CL parameter is the query. This will be picked up later
                count($this->serverInfo['argv']) >= 3
                    ? $this->serverInfo['argv'][2] : '' => '',
            );
            if (count($this->serverInfo['argv']) >= 5) {
                //Fourth CL parameter is a query string
                parse_str($this->serverInfo['argv'][4], $queryVars);
                if (is_array($queryVars)) {
                    $payload = array_merge($this->payload, $queryVars);
                }
            }
        }
        if (!empty($this->serverInfo['CONTENT_TYPE'])
            && $payload = $this->parseContent()) {
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
        if (is_null($payload)) {
            $payload = isset($_REQUEST) ? $_REQUEST : array();
        }
        $this->setPayload($payload);
        return $this->payload;
    }

    /**
     * Parse the content / body of the Request
     *
     * @param string $content The content to parse
     * @param string $type    The type of the content
     *
     * @todo Expand this to include XML and other content types
     * @return array The payload as an array? This might change
     */
    public function parseContent($content = null, $type = null)
    {
        $type = $type ?: $this->serverInfo['CONTENT_TYPE'];
        if (is_null($content)) {
            $data     = '';
            $fpointer = fopen('php://input', 'r');
            while ($chunk = fread($fpointer, 1024)) {
                $data .= $chunk;
            }
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
            throw new Exceptions\UnrecognizedRequestException('Unknown Content Type: ' . $type);
            break;
        }
        return $payload;
    }

    /**
     * Set the request's payload.
     *
     * @param array $payload The Request's Payload
     *
     * @return The current object
     */
    public function setPayload(array $payload)
    {
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
        return !array_key_exists('REQUEST_METHOD', $this->serverInfo)
            && array_key_exists('argv', $this->serverInfo);
    }
}
