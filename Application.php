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
use Backend\Core\Utilities\DependencyInjectionContainer;
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
     * @param Backend\Interfaces\ConfigInterface                       $config    The
     * Configuration for the Application.
     * @param Backend\Interfaces\DependencyInjectionContainerInterface $container The
     * DI Container for the Application.
     * Application.
     */
    public function __construct(ConfigInterface $config,
        DependencyInjectionContainerInterface $container
    ) {
        $this->config = $config;
        $this->container = $container;
        $this->container->set('backend.application.config', $this->config);
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

        // PHP Helpers
        register_shutdown_function(array($this, 'shutdown'));

        set_exception_handler(array($this, 'exception'));
        set_error_handler(array($this, 'error'));

        // Error Reporting
        switch (BACKEND_SITE_STATE)
        {
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
        $toInspect = $request ?: $this->container->get('backend.request');
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
                        $controller = new $class(
                            $this->getContainer(),
                            $this->getRequest()
                        );
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
        $this->container->set('backend.request', $this->request);

        // Get the Formatter
        $formatter = $this->getFormatter();

        // Transform the Result
        if ($callback) {
            $class = get_class($formatter);
            $class = explode('\\', $class);
            $method = $callback->getMethod();
            $method = str_replace('Action', end($class), $method);
            try {
                $callback->setMethod($method);
                if ($callback->isValid()) {
                    $toInspect = $callback->execute(array($toInspect));
                }
            } catch (CoreException $e) {
            }
        }
        if ($toInspect instanceof ResponseInterface) {
            return $toInspect;
        }

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
            $this->router = $this->container->get('backend.router');
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
     * @return \Backend\Interfaces\FormatterInterface
     * @todo Do this with the DIC at some point
     */
    public function getFormatter()
    {
        if (empty($this->formatter)) {
            try {
                $this->formatter = $this->container->get('backend.formatter');
            } catch (CoreException $e) {
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
    public function setFormatter(Backend\Interfaces\FormatterInterface $formatter)
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
        $response->output() && die;
    }
}
