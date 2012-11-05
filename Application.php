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
use Backend\Interfaces\ResponseInterface;
use Backend\Interfaces\CallbackInterface;
use Backend\Interfaces\ConfigInterface;
use Backend\Interfaces\DependencyInjectionContainerInterface;
use Backend\Core\Utilities\Router;
use Backend\Core\Utilities\Callback;
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
     * The Dependency Injection Container for the Application.
     *
     * @var Backend\Interfaces\DependencyInjectionContainerInterface
     */
    protected $container;

    /**
     * The constructor for the object.
     *
     * @param Backend\Interfaces\ConfigInterface $config The Configuration for the
     * Application.
     * @param Backend\Interfaces\DependencyInjectionContainerInterface $container The
     * DI Container for the Application.
     * Application.
     */
    public function __construct(ConfigInterface $config, DependencyInjectionContainerInterface $container)
    {
        $this->config = $config;
        $this->container = $container;
        $this->container->set('application.config', $this->config);
        $this->init();
    }

    /**
     * Initialize the Application.
     *
     * @return boolean Returns true if the initialization ran. False otherwise.
     */
    public function init()
    {
        static $ran = false;
        if ($ran) {
            return false;
        }

        $this->raiseEvent('core.init');

        // PHP Helpers
        register_shutdown_function(array($this, 'shutdown'));

        set_exception_handler(array($this, 'exception'));
        set_error_handler(array($this, 'error'));

        // Error Reporting
        switch (BACKEND_SITE_STATE) {
            case 'testing':
            case 'development':
                error_reporting(-1);
                ini_set('display_errors', 1);
                break;
            default:
                error_reporting(E_ALL & ~E_DEPRECATED);
                ini_set('display_errors', 0);
                break;
        }

        $ran = true;

        return true;
    }

    /**
     * Main function for the application.
     *
     * Inspect the request and subsequent results, chain if necessary.
     *
     * @param Backend\Interfaces\RequestInterface $request The request the
     * application should handle
     *
     * @return \Backend\Interfaces\ResponseInterface
     * @throws \Backend\Core\Exception               When there's no route or formatter for the
     * request.
     */
    public function main(RequestInterface $request = null)
    {
        // Get the initial Request / result
        $result = $request ?: $this->container->get('request');

        do {
            // Raise the event with the request
            if ($result instanceof RequestInterface) {
                $this->setRequest($result);
                $callback = $this->getRouter()->inspect($this->getRequest());
            } else if ($result instanceof CallbackInterface) {
                $callback = $result;
            }

            if (empty($callback) || ($callback instanceof CallbackInterface) === false) {
                // 404 - Not Found
                $message = 'Unknown route requested:' . $result->getMethod()
                    . ' ' . $result->getPath();
                throw new CoreException($message, 404);
            }

            // Transform the request by executing the Callback
            $callback = $this->transformCallback($callback);
            $result   = $callback->execute();

        } while ($result instanceof RequestInterface
            || $result instanceof CallbackInterface);

        // Get the Formatter
        try {
            $formatter = $this->getFormatter();
        } catch (\Backend\Core\Exception $e) {
            // Don't fall over if we already have a response
            if ($e->getCode() === 415 && $result instanceof ResponseInterface) {
                return $result;
            }
            throw $e;
        }

        // Transform the Result
        try {
            $callback = $this->transformFormatCallback($callback, $formatter);
            $result   = $callback->execute(array($result));
        } catch (CoreException $e) {
            // If the callback is invalid, it won't be called, result won't change
        }

        return $formatter->transform($result);
    }

    /**
     * Transform the callback.
     *
     * @param Backend\Interfaces\CallbackInterface $callback The callback to transform.
     *
     * @return Backend\Interfaces\CallbackInterface The transformed callback.
     */
    protected function transformCallback(CallbackInterface $callback)
    {
        //Transform the callback a bit if it's a controller
        $class = $callback->getClass();
        if ($class) {
            // Check for a ControllerInterface, and adjust accordingly
            $interfaces = class_implements($class);
            $implements = array_key_exists(
                'Backend\Interfaces\ControllerInterface', $interfaces
            );
            if ($implements === true) {
                $controller = new $class(
                    $this->getContainer(),
                    $this->getRequest()
                );
                $controller->setRequest($this->getRequest());
                $callback->setObject($controller);
                //Set the method name as actionAction
                if (substr($callback->getMethod(), -6) !== 'Action') {
                    $callback->setMethod($callback->getMethod() . 'Action');
                }
            }
        }
        $this->raiseEvent('core.callback', $callback);
        return $callback;
    }

    /**
     * Transform the callback in relation with the format.
     *
     * @param Backend\Interfaces\CallbackInterface $callback The callback on which
     * the call will be based.
     * @param Backend\Interfaces\FormatterInterface $formatter The formatter on which
     * the call will be based.
     *
     * @return Backend\Interfaces\CallbackInterface The transformed format callback.
     */
    protected function transformFormatCallback(CallbackInterface $callback, FormatterInterface $formatter)
    {
        $method = $callback->getMethod();
        if ($method) {
            $class = get_class($formatter);
            $class = explode('\\', $class);
            $method = str_replace('Action', end($class), $method);
            $callback->setMethod($method);
        }

        $this->raiseEvent('core.format_callback', $callback);
        return $callback;
    }

    /**
     * Get the request that's currently being executed.
     *
     * @return \Backend\Interfaces\RequestInterface
     */
    public function getRequest()
    {
        if ($this->request === null && $this->container->has('request')) {
            $this->request = $this->container->get('request');
        }

        return $this->request;
    }

    /**
     * Set the request that's will be executed.
     *
     * @param \Backend\Interfaces\RequestInterface $request The request to set.
     *
     * @return \Backend\Core\Application
     */
    public function setRequest($request)
    {
        $this->raiseEvent('core.main', $this->getRequest());
        $this->container->set('request', $this->request);
        $this->request = $request;

        return $this;
    }

    /**
     * Get the application's configuration.
     *
     * @return \Backend\Interfaces\ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the Router for the Application.
     *
     * @return Backend\Interfaces\RouterInterface
     */
    public function getRouter()
    {
        if (empty($this->router)) {
            $this->router = $this->container->get('router');
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
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Get the Formatter for the Application.
     *
     * @return \Backend\Interfaces\FormatterInterface
     * @todo Do this with the DIC at some point
     */
    public function getFormatter()
    {
        if (empty($this->formatter)) {
            try {
                $this->formatter = $this->container->get('formatter');
            } catch (\Exception $e) {
                throw new CoreException('Unsupported format requested', 415, $e);
            }
        }
        if (empty($this->formatter)) {
            throw new CoreException('Unsupported format requested', 415);
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
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Set the Application's DI Container.
     *
     * @param \Backend\Interfaces\DependencyInjectionContainerInterface $container
     * The DI Container for the Application.
     *
     * @return \Backend\Interfaces\ControllerInterface The current object.
     */
    public function setContainer(DependencyInjectionContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the Application's DI Container
     *
     * @return \Backend\Interfaces\DependencyInjectionContainerInterface The
     * Application's DI Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Raise an event.
     *
     * @return \Backend\Core\Application The current object
     */
    public function raiseEvent($name)
    {
        if ($this->container->has('event_dispatcher')) {
            $this->container->get('event_dispatcher')->dispatch($name);
        }
        return $this;
    }

    /**
     * Shutdown function called when ever the script ends
     *
     * @return null
     */
    public function shutdown()
    {
        $this->raiseEvent('core.shutdown');
    }

    /**
     * Error handling function called when ever an error occurs.
     *
     * Called by set_error_handler. Some types of errors will be converted into
     * excceptions.
     *
     * @param int    $errno      The error number.
     * @param string $errstr     The error string.
     * @param string $errfile    The file the error occured in.
     * @param int    $errline    The line number the error occured on.
     * @param array  $errcontext The context the error occured in.
     * @param bool   $return     Return the exception instead of running it.
     *
     * @return \Exception
     */
    public function error($errno, $errstr, $errfile, $errline, $errcontext, $return = false)
    {
        $exception = new \ErrorException($errstr, 500, $errno, $errfile, $errline);
        // Don't raise an event here, it's raised in the exception method.
        if ($return) {
            return $exception;
        }
        throw $exception;
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
        $this->raiseEvent('core.exception', $exception);

        $code = $exception->getCode();
        if ($code < 100 || $code > 599) {
            $code = 500;
        }
        $responseClass = $this->container->getParameter('response.class');
        $response = new $responseClass(
            $exception->getMessage(),
            $code
        );
        if ($return) {
            return $response;
        }
        if (BACKEND_SITE_STATE === 'testing') {
            throw $exception;
        }
        $response->output();
        die;
    }
}
