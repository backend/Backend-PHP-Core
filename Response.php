<?php
/**
 * File defining Response
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
use Backend\Interfaces\ResponseInterface;
use Backend\Core\Exception as CoreException;
/**
 * The response that will be sent back to the client
 *
 * @category Backend
 * @package  Core
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Response implements ResponseInterface
{
    /**
     * The body of the response
     *
     * @var mixed
     */
    protected $body = null;

    /**
     * @var int The HTTP response code
     */
    protected $status  = 200;

    /**
     * @var string The HTTP version
     */
    protected $httpVersion = null;

    /**
     * @var array A list of HTTP Response Codes with their default texts
     *
     * Copied from the Zend_Http_Response object
     */
    protected static $messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    /**
     * @var array An associative array containing headers to be sent along with the response
     */
    protected $headers = array();

    /**
     * The constructor for the Response class
     *
     * @param string $body    The body for the response
     * @param int    $status  The status code for the response
     * @param array  $headers The headers for the response
     */
    public function __construct($body = '', $status = 200, array $headers = array())
    {
        $this->setStatusCode($status);
        $this->setHeaders($headers);
        $this->setBody($body);
        //TODO Don't use SERVER, use the REQUEST
        $this->httpVersion = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
    }

    /**
     * Return the current status code for the Response
     *
     * @return int The status code
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Set the status code for the Response
     *
     * @param int $code The new status code
     *
     * @return Response          The current object
     * @throws \RuntimeException If the response code is smaller than 100 or larger than 600.
     */
    public function setStatusCode($code)
    {
        $code = (int) $code;
        if ($code < 100 || $code > 600) {
            throw new \RuntimeException('HTTP Response Code must be between 100 and 600');
        }
        $this->status = (int) $code;

        return $this;
    }

    /**
     * Get the text associated with a status code
     *
     * @param int $code The status code to get the text for
     *
     * @return string The status code text
     */
    public function getStatusText($code = null)
    {
        $code = $code ?: $this->getStatusCode();
        if (array_key_exists($code, self::$messages)) {
            return self::$messages[$code];
        }

        return 'Unknown Status';
    }

    /**
     * Return the Response's body
     *
     * @return mixed The Response's body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the body for the Response
     *
     * @param mixed $body The new body
     *
     * @return Response The current object
     */
    public function setBody($body)
    {
        $this->body = $body;

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
     * @return array
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
     * Return the Response's headers
     *
     * @return array An array containing the Response's headers
     */
    public function getHeaders()
    {
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
     * Set the headers for the Response
     *
     * @param array $headers The new headers
     *
     * @return Response The current object
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Output the Response to the client
     *
     * @return null
     */
    public function output()
    {
        $this->sendHeaders()
            ->sendBody();
        return $this;
    }

    /**
     * Send the Response's headers to the client
     *
     * @return Response The current object
     */
    public function sendHeaders()
    {
        if (headers_sent($file, $line)) {
            throw new CoreException('Headers already sent in ' . $file . ', line ' . $line);
        }
        //Always send the HTTP status header first
        $httpStatus = $this->httpVersion . ' ' . $this->status
            . ' ' . $this->getStatusText($this->status);
        array_unshift($this->headers, $httpStatus);
        if (!array_key_exists('X-Application', $this->headers)) {
            $this->headers['X-Application'] = 'Backend-PHP (Core)';
        }
        foreach ($this->getHeaders() as $content) {
            $this->writeHeader($content);
        }

        return $this;
    }

    /**
     * Write a HTTP Header.
     *
     * @param string $content The contents of the header.
     *
     * @return void
     * @todo This function isn't easily testable. Fix it!
     */
    public function writeHeader($content)
    {
        header($content);
    }

    /**
     * Send the Response's body to the client
     *
     * @return Response The current object
     */
    public function sendBody()
    {
        echo $this->body;

        return $this;
    }

    /**
     * Convert the Response to a string
     *
     * @return string The response as a string
     */
    public function __toString()
    {
        try {
            ob_start();
            $this->sendBody();

            return ob_get_clean();
        } catch (\Exception $e) {
            return 'Exception: ' . $e->getMessage();
        }
    }
}
