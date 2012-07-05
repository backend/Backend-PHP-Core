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
    protected $path = null;

    /**
     * The site end point. Used for calls to the site.
     *
     * @var string
     */
    protected $url = null;

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
     * The requested format of the request.
     *
     * @var string
     */
    protected $format = null;

    /**
     * The requested mime type of the request.
     *
     * @var string
     */
    protected $mimeType = null;

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
        $this->serverInfo['HTTP_HOST'] = $urlParts['host'];
        $this->serverInfo['QUERY_STRING'] = empty($urlParts['query']) ? ''
            : $urlParts['query'];
        $urlParts['path'] = empty($urlParts['path']) ? ''
            : $urlParts['path'];
        //RFC3875 4.1.5 defines path_info as "derived from the portion of the URI
        //path hierarchy following the part that identifies the script itself."
        //index.php will always be "the script", so use that.
        $this->setPath($urlParts['path']);
        if (strpos($urlParts['path'], 'index.php') === false
            && strlen($urlParts['path']) > 1
        ) {
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
     * Return the path of the current Request.
     *
     * In http://backend-php.net/index.php/something, the path will be
     * /index.php/something
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->path === null) {
            $this->setPath('');
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
     * @return \Backend\Core\Request The current object
     */
    public function setPath($path)
    {
        //Clean up the path
        //No trailing slash
        if (substr($path, -1) == '/') {
            $path = substr($path, 0, -1);
        }
        if (substr($path, 0, 1) != '/') {
            $path = '/' . $path;
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Return the url to this Request.
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->url === null) {
            $this->prepareUrl();
        }
        return $this->url;
    }

    /**
     * Prepare the URL.
     *
     * Build the URL from the current Server Info.
     *
     * @return null
     */
    protected function prepareUrl()
    {
        //Construct the current URL
        $this->url = 'http';
        if ($this->getServerInfo('SERVER_PORT') == 443
            || $this->getServerInfo('HTTPS') == 'on'
        ) {
            $this->url .= 's';
        }
        $this->url .= '://' . $this->getServerInfo('HOST');
        if ('index.php' == basename($this->getServerInfo('PHP_SELF'))) {
            $this->url .= $this->serverInfo['PHP_SELF'];
        } else {
            $pattern = str_replace('/', '\\/', $this->getServerInfo('PATH_INFO'));
            $pattern = '/' . $pattern . '$/';
            $subject = explode('?', $this->getServerInfo('REQUEST_URI'));
            $subject = reset($subject);
            $this->url .= preg_replace($pattern, '', $subject);
        }
        if (substr($this->url, -1) == '/') {
            $this->url = substr($this->url, 0, -1);
        }
    }

    /**
     * Determine the requested MIME Type for the request
     *
     * @return string The MIME Type for the request
     * @todo This doesn't check the q variable -
     * https://github.com/backend/Backend-PHP-Core/issues/1
     */
    public function getMimeType()
    {
        if ($this->mimeType !== null) {
            return $this->mimeType;
        }
        switch (true) {
        case $this->fromCli():
            $this->mimeType = 'cli';
            break;
        case $this->getServerInfo('http_accept') !== null:
            $mimeType = $this->getServerInfo('http_accept');
            if ($mimeType !== null) {
                //Try to get the first type
                $types = explode(',', $mimeType);
                //Remove the preference variable
                $mimeType = explode(';', reset($types));
                $this->mimeType = trim(reset($mimeType));
            }
            break;
        default:
            break;
        }
        return $this->mimeType;
    }

    /**
     * Determine the requested format for the request
     *
     * @return string The format for the request
     */
    public function getSpecifiedFormat()
    {
        if ($this->format !== null) {
            return $this->format;
        }
        //Check the format parameter
        if (is_array($this->payload) && array_key_exists('format', $this->payload)) {
            $this->format = $this->payload['format'];
        } else if (self::fromCli() && count($this->serverInfo['argv']) >= 4) {
            // Third CL parameter is the required format
            $this->format = $this->serverInfo['argv'][3];
        }
        return $this->format;
    }

    /**
     * Get the Request Extension
     *
     * @return string The extension of the request
     */
    public function getExtension()
    {
        if ($this->extension !== null) {
            return $this->extension;
        }
        $pattern = '/[^\/]+\.(.*)\??.*$/';
        preg_match('/\.(\w+)$/', $this->getPath(), $matches);
        if (!empty($matches[1])) {
            $this->extension = $matches[1];
        } else {
            $this->extension = null;
        }
        return $this->extension;
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
     * @todo Refactor so that we can use thirdparty / external parsers -
     * https://github.com/backend/Backend-PHP-Core/issues/2
     * @return array
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
        $payload = array();
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
