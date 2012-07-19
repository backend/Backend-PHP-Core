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
use Backend\Interfaces\DependencyInjectorContainerInterface;
use Backend\Core\Utilities\Router;
use Backend\Core\Utilities\Config;
use Backend\Core\Utilities\Formatter;
use Backend\Core\Utilities\Callback;
use Backend\Core\Utilities\DependencyInjectorContainer;
use Backend\Core\Exception as CoreException;
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
     * The Application Configuration.
     *
     * @var Backend\Interfaces\ConfigInterface
     */
    protected $config;

    /**
     * The Dependency Injector Container for the Application.
     *
     * @var Backend\Interfaces\DependencyInjectorContainerInterface
     */
    protected $container;

    /**
     * The constructor for the object.
     *
     * @param Backend\Interfaces\ConfigInterface $config The Configuration for the
     * Application.
     */
    public function __construct(ConfigInterface $config,
        DependencyInjectorContainerInterface $container
    ) {
        $this->config = $config;
        $this->container = $container;
        $this->init();
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
     * @param Backend\Interfaces\RequestInterface $request The request the
     * application should handle
     *
     * @return \Backend\Interfaces\ResponseInterface
     * @throws \Backend\Core\Exception When there's no route or formatter for the
     * request.
     */
    public function main(RequestInterface $request = null)
    {
        //Inspect the request and subsequent results, chain if necessary
        $toInspect = $request ?: new Request();
        $this->request = $toInspect;
        do {
            $callback = $toInspect instanceof RequestInterface
                ? $this->getRouter()->inspect($toInspect)
                : $toInspect;
            if ($callback instanceof RequestInterface) {
                $this->request = $callback;
                continue;
            } else if ($callback instanceof CallbackInterface) {
                //Transform the callback a bit if it's a controller
                $class = $callback->getClass();
                if ($class) {
                    $interfaces = class_implements($class);
                    $implements = array_key_exists(
                        'Backend\Interfaces\ControllerInterface', $interfaces
                    );
                    if ($implements) {
                        $controller = new $class();
                        $controller->setRequest($this->getRequest());
                        $callback->setObject($controller);
                        //Set the method name as actionAction
                        $callback->setMethod($callback->getMethod() . 'Action');
                    }
                }
                $toInspect = $callback->execute();
            } else {
                throw new CoreException('Unknown route requested', 404);
            }
        } while ($toInspect instanceof RequestInterface
            || $toInspect instanceof CallbackInterface);

        //Transform the Result
        $formatter = $this->getFormatter();
        return $formatter->transform($toInspect);
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
     * Get the Router for the Application.
     *
     * @return Backend\Interfaces\RouterInterface
     */
    public function getRouter()
    {
        if (empty($this->router)) {
            $this->router = $this->container->get('Backend\Interfaces\RouterInterface');
        }
        return $this->router;
    }

    /**
     * Set the Router for the Application.
     *
     * @param Backend\Interfaces\RouterInterface $router The router for the Applciation.
     *
     * @return Backend\Core\Application
     */
    public function setRouter(Backend\Interfaces\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Get the Formatter for the Application.
     *
     * @param Backend\Interfaces\RequestInterface $request The request to
     * determine what formatter to return.
     * @param Backend\Interfaces\ConfigInterface  $config  The current Application
     * configuration.
     *
     * @return \Backend\Interfaces\FormatterInterface
     * @todo Do this with the DIC at some point
     */
    public function getFormatter(
        RequestInterface $request = null, ConfigInterface $config = null
    ) {
        if (empty($this->formatter)) {
            $config  = $config  ?: $this->config;
            $request = $request ?: $this->request;
            //$this->formatter = $this->get('Backend\Interfaces\FormatterInterface');
            $this->formatter = $this->formatter ?: Formatter::factory($request);
            if (empty($this->formatter)) {
                throw new CoreException('Unsupported format requested', 415);
            }
            $this->formatter->setConfig($config);
        }
        return $this->formatter;
    }

    /**
     * Set the Formatter for the Application.
     *
     * @param Backend\Interfaces\FormatterInterface $formatter The Formatter for the
     * Application.
     *
     * @return Backend\Core\Application
     */
    public function setFormatter(Backend\Interfaces\FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Implement a Component.
     *
     * This method also sets the configuration if the class supports it.
     *
     * @param string $className The class name of the component to implement.
     *
     * @return object The constructed service
     * @throws \Backend\Core\Exception
     */
    public function implement($className)
    {
        $implementation = parent::implement($className);
        if (is_object($implementation)
            && method_exists($implementation, 'setConfig')
        ) {
            $implementation->setConfig($this->config);
        }
        return $implementation;
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
     * @param int    $errno   The error number.
     * @param string $errstr  The error string.
     * @param string $errfile The file the error occured in.
     * @param int    $errline The line number the errro occured on.
     * @param bool   $return  Return the exception instead of running it.
     *
     * @return \Exception
     */
    public function error($errno, $errstr, $errfile, $errline, $return = false)
    {
        $exception = new \ErrorException($errstr, 500, $errno, $errfile, $errline);
        $this->exception($exception, $return);
        return $exception;
    }

    /**
     * Exception handling function called when ever an exception isn't handled.
     *
     * Called by set_exception_handler.
     *
     * @param \Exception $exception The thrown exception.
     * @param bool       $return    Return the response instead of outputting it.
     *
     * @return \Backend\Interfaces\ResponseInterface
     */
    public function exception(\Exception $exception, $return = false)
    {
        $code = $exception->getCode();
        if ($code < 100 || $code > 599) {
            $code = 500;
        }
        $response = new Response(
            $exception->getMessage(),
            $code
        );
        if ($return) {
            return $response;
        }
        $response->output();
    }
}
