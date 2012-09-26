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
 * @todo     Refactore MimeType methods into separate class.
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
     * The headers of the Request.
     *
     * @var array
     */
    protected $headers = null;

    /**
     * The body of the Request.
     *
     * @var array
     */
    protected $body = null;

    /**
     * The method of the Request. Can be one of GET, POST, PUT, DELETE, HEAD or OPTIONS.
     *
     * @var string
     */
    protected $method  = null;

    /**
     * The extension of the Request.
     *
     * @var string
     */
    protected $extension = null;

    /**
     * The requested format of the Request.
     *
     * @var string
     */
    protected $format = null;

    /**
     * The requested mime type of the Request.
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
     * @param mixed  $url    The URL of the request
     * @param string $method The request method. Can be one of GET, POST, PUT,
     * DELETE or HEAD
     * @param mixed $body    The request data. Defaults to the HTTP request data
     * if not supplied
     */
    public function __construct($url = null, $method = null, $body = null)
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

        $this->setBody($body);
        $this->getHeaders();
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
        $this->setHeader('host', $urlParts['host']);
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
        $body = $this->body === null ? $this->getBody() : $this->body;
        switch (true) {
        case is_array($body) && array_key_exists('_method', $body):
            $method = $body['_method'];
            unset($this->body['_method']);
            break;
        case $this->getHeader('METHOD_OVERRIDE') !== null:
            $method = $this->getHeader('METHOD_OVERRIDE');
            break;
        //First CL parameter is the method
        case $this->fromCli()
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
     * Build the headers if necessary.
     *
     * @return Request The current object.
     */
    public function buildHeaders($force = false)
    {
        if ($force || $this->headers === null) {
            if (function_exists('apache_request_headers')) {
                $this->headers = apache_request_headers();
                $this->headers = array_change_key_case($this->headers);
            } else {
                $this->headers = array();
                foreach($this->serverInfo as $name => $value) {
                    if (strtolower(substr($name, 0, 5)) !== 'http_') {
                        continue;
                    }
                    $name = strtolower(substr($name, 5));
                    $this->headers[$name] = $value;
                }
            }
        }
        return $this;
    }

    /**
     * Return the Request headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        $this->buildHeaders();

        // Return the compiled headers
        $headers = array();
        foreach ($this->headers as $name => $content) {
            if (is_numeric($name) === false) {
                $content = ucwords($name) . ': ' . $content;
            }
            $headers[] = $content;
        }
        return $headers;
    }

    /**
     * Set the Request headers.
     *
     * @param array $headers An array of headers for the Request.
     *
     * @return \Backend\Interfaces\RequestInterface
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Return the specified Request header.
     *
     * @param string $name The name of the header to return.
     *
     * @return string
     */
    public function getHeader($name)
    {
        $this->buildHeaders();

        $name = strtolower($name);
        return array_key_exists($name, $this->headers) ? $this->headers[$name] : null;
    }

    /**
     * Set the specified Request headers.
     *
     * If the name is null, the header won't have a name, and will contain only
     * the value of the header.
     *
     * @param string $name  The name of the header to set.
     * @param string $value The value of the header.
     *
     * @return \Backend\Interfaces\RequestInterface
     */
    public function setHeader($name, $value)
    {
        if ($name === null) {
            $this->headers[] = $value;
        } else {
            $name = strtolower($name);
            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Return the path of the current Request.
     *
     * In http://backend-php.net/index.php/something, the path will be
     * /something
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->path === null) {
            $path = $this->getServerInfo('PATH_INFO');
            $path = $path ?: '/';
            $this->setPath($path);
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
     * Set the Request's URL.
     *
     * @param string $url The url.
     *
     * @return \Backend\Interfaces\RequestInterface
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
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
        if ($this->getServerInfo('server_port') == 443
            || $this->getServerInfo('https') == 'on'
        ) {
            $this->url .= 's';
        }
        $this->url .= '://' . $this->getHeader('host');

        $script = $this->getServerInfo('script_name');
        $this->url .= $script;

        // Check for URL Rewriting
        $uri = $this->getServerInfo('request_uri');
        if (substr($uri, 0, strlen($script)) !== $script) {
            $this->url = preg_replace('|/' . basename($script) . '$|', $this->path, $this->url);
        } else {
            $this->url .= $this->path;
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
        case $this->getHeader('accept') !== null:
            $mimeType = $this->getHeader('accept');
            if ($mimeType !== null) {
                //Try to get the first type
                $types = explode(',', $mimeType);
                $types = array_map('trim', $types);
                usort($types, array($this, 'compareMimeTypes'));
                //Remove the preference variable
                $mimeType = explode(';', end($types));
                $this->mimeType = trim(reset($mimeType));
            }
            break;
        case $this->fromCli():
            $this->mimeType = 'cli';
            break;
        default:
            break;
        }

        return $this->mimeType;
    }

    /**
     * Set the Request MIME Type.
     *
     * @param mixed $mimeType The Request's MIME Type.
     *
     * @return \Backend\Interfaces\RequestInterface
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * Compare two mime types. Used for sorting.
     *
     * @param string $tOne The first mime type.
     * @param string $tTwo The first mime type.
     *
     * @return int
     */
    protected function compareMimeTypes($tOne, $tTwo)
    {
        $tOne = $this->prepareMimeType($tOne);
        $tTwo = $this->prepareMimeType($tTwo);
        if ($tOne['primary'] === '*') {
            return -1;
        } elseif ($tTwo['primary'] === '*') {
            return 1;
        }
        if ($tOne['secondary'] === '*') {
            return -1;
        } elseif ($tTwo['secondary'] === '*') {
            return 1;
        }
        if ($tOne['priority'] === $tTwo['priority']) {
            return 0;
        }

        return $tOne['priority'] < $tTwo['priority'] ? -1 : 1;
    }

    /**
     * Prepare a mime type for comparison.
     *
     * @param string $type The string representation of the mime type to prepare.
     *
     * @return array
     */
    protected function prepareMimeType($type)
    {
        $type = explode(';', $type);
        $type[1] = empty($type[1]) ? 'q=1' : $type[1];
        parse_str($type[1], $type[1]);
        $parts = explode('/', $type[0]);
        $parts[1] = empty($parts[1]) ? '*' : $parts[1];
        $type = array(
            'primary'   => $parts[0],
            'secondary' => $parts[1],
            'priority'  => $type[1]['q'],
        );

        return $type;
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
        if (is_array($this->body) && array_key_exists('format', $this->body)) {
            $this->format = $this->body['format'];
        } elseif ($this->fromCli() && count($this->serverInfo['argv']) >= 4) {
            // Third CL parameter is the required format
            $this->format = $this->serverInfo['argv'][3];
        }

        return $this->format;
    }

    /**
     * Set the Request Specified Format.
     *
     * @param mixed $format The Request's specified format.
     *
     * @return \Backend\Interfaces\RequestInterface
     */
    public function setSpecifiedFormat($format)
    {
        $this->format = $format;
        return $this;
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
     * Set the Request Extension.
     *
     * @param mixed $extension The Request's extension.
     *
     * @return \Backend\Interfaces\RequestInterface
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
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
        //Check for deprecated X- values http://tools.ietf.org/html/rfc6648
        case array_key_exists('X_' . $name, $this->serverInfo):
            return $this->serverInfo['X_' . $name ];
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
        if (in_array($name, array('argv', 'argc')) === false) {
            $name = strtoupper($name);
        }
        $this->serverInfo[$name] = $value;
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
        return strtoupper($method) === $this->getMethod();
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
     * @todo Expand this to include other content types
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
        $body = null;
        $type = explode(';', $type);
        $type = trim($type[0]);
        switch ($type) {
        case 'application/json':
        case 'text/json':
        case 'text/javascript':
            $body = json_decode($data);
            $body = is_object($body) ? (array) $body : $body;
            break;
        case 'application/x-www-form-urlencoded':
        case 'multipart/form-data':
        case 'text/plain':
            parse_str($data, $body);
            break;
        case 'application/xml':
            $body = simplexml_load_string($data);
            $body = is_object($body) ? (array) $body : $body;
            break;
        default:
            throw new CoreException(
                'Unknown Content Type: ' . $type,
                400
            );
            break;
        }

        return $body;
    }

    /**
     * Return the request's body.
     *
     * @return array The Request body
     */
    public function getBody()
    {
        if ($this->body !== null) {
            return $this->body;
        }
        if ($this->fromCli()) {
            if (count($this->serverInfo['argv']) >= 5) {
                //Fourth CL parameter is a query string
                parse_str($this->serverInfo['argv'][4], $body);
                $this->body = $body;

                return $this->body;
            }
        }
        $body = $this->parseContent($this->getServerInfo('content_type'));
        if ($body) {
            $this->setBody($body);

            return $this->body;
        }
        $body = null;
        switch (true) {
        case count($_GET) > 0:
            $body = isset($_GET) ? $_GET : array();
            break;
        case count($_POST) > 0:
            $body = isset($_POST) ? $_POST : array();
            break;
        default:
            break;
        }
        if ($body === null) {
            $body = isset($_REQUEST) ? $_REQUEST : array();
        }
        $this->setBody($body);

        return $this->body;
    }

    /**
     * Set the request's body.
     *
     * Strings will be parsed for variables and objects will be casted to arrays.
     *
     * @param mixed $body The Request's body
     *
     * @return Request The current object.
     */
    public function setBody($body)
    {
        if (is_string($body)) {
            parse_str($body, $body);
        } elseif (is_object($body)) {
            $body = (array) $body;
        }
        $this->body = $body;

        return $this;
    }

    /**
     * Check if this requests originates from a CLI.
     *
     * @return boolean If this is a CLI request
     */
    public function fromCli()
    {
        return !empty($this->serverInfo['argc']);
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
        return $this;
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
