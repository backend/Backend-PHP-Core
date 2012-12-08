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
        $this->container = $container;
        $this->setConfig($config);
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
                $event = new Event\RequestEvent($result);
                $this->raiseEvent('core.request', $event);
                $this->setRequest($event->getRequest());

                $callback = $this->getRouter()->inspect($this->getRequest());
            } elseif ($result instanceof CallbackInterface) {
                $callback = $result;
            }

            if (empty($callback) || ($callback instanceof CallbackInterface) === false) {
                // 404 - Not Found
                $message = 'Unknown route requested:' . $result->getMethod()
                    . ' ' . $result->getPath();
                throw new CoreException($message, 404);
            }
            $this->container->set('callback', $callback);

            // Callback Event
            $event = new Event\CallbackEvent($callback);
            $this->raiseEvent('core.callback', $event);
            $callback = $event->getCallback();

            // Transform the request by executing the Callback
            $result = $callback->execute();

        } while ($result instanceof RequestInterface
            || $result instanceof CallbackInterface);


        // Transform the Result into a Response
        $event = new Event\ResultEvent($result);
        $this->raiseEvent('core.result', $event);
        $response = $event->getResponse();

        // Transform the Response
        $event = new Event\ResponseEvent($response);
        $this->raiseEvent('core.response', $event);

        return $event->getResponse();
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
        $this->request = $request;
        $this->container->set('request', $this->request);

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
     * Set the request that's will be executed.
     *
     * @param \Backend\Interfaces\RequestInterface $request The request to set.
     *
     * @return \Backend\Core\Application
     */
    public function setConfig($config)
    {
        $this->config = $config;
        $this->container->set('application.config', $this->config);

        return $this;
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
    public function raiseEvent($name, $event = null)
    {
        if ($this->container->has('event_dispatcher')) {
            $this->container->get('event_dispatcher')->dispatch($name, $event);
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
    public function exception(\Exception $exception)
    {
        $event = new Event\ExceptionEvent($exception);
        $this->raiseEvent('core.exception', $event);

        $exception = $event->getException();
        $response  = $event->getResponse();

        // Not 100% sure this is good design
        $response instanceof ResponseInterface && $response->output() && die;

        // If output returns false, it won't exit, which will throw the exception
        throw $exception;
    }
}
