<?php
/**
 * File defining \Backend\Core\Application
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
use Backend\Interfaces\ApplicationInterface;
use Backend\Interfaces\RouterInterface;
use Backend\Interfaces\FormatterInterface;
use Backend\Interfaces\RequestInterface;
use Backend\Interfaces\CallbackInterface;
use Backend\Interfaces\ConfigInterface;
use Backend\Core\Utilities\Router;
use Backend\Core\Utilities\Formatter;
//TODO Remove this dependancy
use Backend\Modules\Callback;
/**
 * The main application class.
 *
 * @category Backend
 * @package  Core
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Application implements ApplicationInterface
{
    /**
     * Router to map callbacks to requests, and vice versa.
     *
     * @var Backend\Interfaces\RouterInterface
     */
    protected $router = null;

    /**
     * Formatter to convert results into responses.
     *
     * @var Backend\Interfaces\FormatterInterface
     */
    protected $formatter = null;

    /**
     * The request currently being executed.
     *
     * @var \Backend\Interfaces\RequestInterface
     */
    protected $request;

    /**
     * The constructor for the object.
     *
     * @param \Backend\Interfaces\RouterInterface    $router    The router to use.
     * @param \Backend\Interfaces\FormatterInterface $formatter The formatter to use.
     */
    public function __construct(
        RouterInterface $router = null,
        FormatterInterface $formatter = null
    ) {
        $this->init();
        $this->router    = $router  ?: new Router();
        $this->formatter = $formatter;
    }

    /**
     * Initialize the Application.
     *
     * @return void
     */
    public function init()
    {
        static $ran = false;
        if ($ran) {
            return;
        }
        //PHP Helpers
        register_shutdown_function(array($this, 'shutdown'));

        set_exception_handler(array($this, 'exception'));
        set_error_handler(array($this, 'error'));
        $ran = true;
    }

    /**
     * Main function for the application
     *
     * @param \Backend\Interfaces\RequestInterface $request The request the
     * application should handle
     *
     * @return \Backend\Interfaces\ResponseInterface
     */
    public function main(RequestInterface $request = null)
    {
        //Inspect the request and subsequent results, chain if necessary
        $toInspect = $request ?: Request::fromState();
        $this->request = $toInspect;
        do {
            $callback  = $toInspect instanceof RequestInterface
                ? $this->router->inspect($toInspect)
                : $toInspect;
            if ($callback instanceof RequestInterface) {
                $this->request = $callback;
                continue;
            } else if ($callback) {
                $callback = $this->checkCallback($callback);
                $toInspect = $callback->execute();
            } else {
                throw new Exception('No route to request found', 404);
            }
        } while ($toInspect instanceof RequestInterface
            || $toInspect instanceof CallbackInterface);

        //Transform the Result
        $formatter = $this->getFormatter();
        if (!$formatter) {
            throw new Exception('Could not find appropriate Formatter', 400);
        }
        return $formatter->transform($toInspect);
    }

    /**
     * Check the validity of the callback, and transform as necessary.
     *
     * If the $callback parameter is an array, the first element must be the string
     * representation of the callback (in the form class::method), and the second
     * the arguments for the callback.
     *
     * @param mixed $callback The callback to check.
     *
     * @return CallbackInterface
     */
    protected function checkCallback($callback)
    {
        if (is_array($callback) && count($callback) == 2) {
            $callback = Callback::fromString($callback[0], $callback[1]);
        }
        if (!($callback instanceof CallbackInterface)) {
            throw new \Exception('Invalid Callback');
        }
        $class = $callback->getClass();
        if (is_subclass_of($class, '\Backend\Interfaces\ControllerInterface')) {
            $controller = new $class();
            $controller->setRequest($this->getRequest());
            $callback->setObject($controller);
        }
        //Set the method name as actionAction
        $callback->setMethod($callback->getMethod() . 'Action');
        return $callback;
    }

    /**
     * Get the request that's currently being executed.
     *
     * @return \Backend\Interfaces\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the appropriate formatter object.
     *
     * @param \Backend\Interfaces\RequestInterface $request The request to
     * determine what formatter to return.
     * @param \Backend\Interfaces\ConfigInterface  $config  The current Application
     * configuration.
     *
     * @return \Backend\Interfaces\FormatterInteface
     */
    public function getFormatter(
        RequestInterface $request = null, ConfigInterface $config = null
    ) {
        $request = $request ?: $this->request;
        if (!$this->formatter) {
            $this->formatter = Formatter::factory($request, $config);
        }
        return $this->formatter;
    }

    /**
     * Shutdown function called when ever the script ends
     *
     * @return null
     */
    public function shutdown()
    {
    }

    /**
     * Error handling function called when ever an error occurs.
     *
     * Called by set_error_handler. Some types of errors will be converted into
     * excceptions.
     *
     * @param int    $errno   The error number
     * @param string $errstr  The error string
     * @param string $errfile The file the error occured in
     * @param int    $errline The line number the errro occured on
     *
     * @return null
     */
    public function error($errno, $errstr, $errfile, $errline)
    {
        $this->exception(
            new \ErrorException($errstr, 500, $errno, $errfile, $errline)
        );
    }

    /**
     * Exception handling function called when ever an exception isn't handled.
     *
     * Called by set_exception_handler.
     *
     * @param \Exception $exception The thrown exception
     *
     * @return null
     */
    public function exception(\Exception $exception)
    {
        $response = new Response(
            $exception->getMessage(),
            $exception->getCode()
        );
        $response->output();
    }
}
