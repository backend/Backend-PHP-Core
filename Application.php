<?php
/**
 * File defining Application
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
use Backend\Core\Utilities\ApplicationEvent;
use Backend\Core\Utilities\Subject;
use Backend\Core\Utilities\ServiceLocator;
use Backend\Core\Utilities\Format;
/**
 * The main application class.
 *
 * @category Backend
 * @package  Core
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Application extends Subject
{
    /**
     * @var boolean This static property indicates if the application has been constructed yet.
     */
    protected static $constructed = false;

    /**
     * @var array A set of observers for the log message
     */
    protected $observers = array();

    /**
     * @var string The current state of the Application. Mostly used for Observers
     */
    protected $state = null;

    /**
     * @var array This contains all tools that should be globally accessable. Use this wisely.
     */
    private static $_toolbox = array();

    /**
     * @var array This contains all the namespaces for the application
     */
    private static $_namespaces = array();

    /**
     * @var integer The debugging level. The higher, the more verbose
     */
    protected static $debugLevel = 3;

    /**
     * @var string The state of the site. Usually production, development or testing
     */
    protected static $siteState = null;

    /**
     * @var Request This contains the Request that is being handled
     */
    private $_request = null;

    /**
     * @var RoutePath This contains the RoutePath on what we're executing
     */
    private $_routePath = null;

    /**
     * The constructor for the class
     *
     * @param mixed   $config  The Configuration to be used for the application. Can be a the
     * path of a config file, or a Config object
     */
    function __construct($config = null)
    {
        $this->setState('constructing');

        if (!self::$constructed) {
            self::constructApplication();
        }

        // Setup the Config
        if ($config === null) {
            if (file_exists(PROJECT_FOLDER . 'configs/' . self::getSiteState() . '.' . CONFIG_EXT)) {
                $config = PROJECT_FOLDER . 'configs/' . self::getSiteState() . '.' . CONFIG_EXT;
            } else if (file_exists(PROJECT_FOLDER . 'configs/default.' . CONFIG_EXT)) {
                $config = PROJECT_FOLDER . 'configs/default.' . CONFIG_EXT;
            } else {
                $string = 'Could not find Configuration file. . Add one to ' . PROJECT_FOLDER . 'configs';
                throw new \Exception($string);
            }
        }
        if (is_string($config)) {
            //String specifies that we should parse the file specified
            $config = new Utilities\Config($config);
        }
        ServiceLocator::add('backend.Config', $config);

        //Initiate the Services
        ServiceLocator::add('backend.Application', $this);

        if ($services = $config->get('services')) {
            ServiceLocator::addFromConfig($services);
        }

        parent::__construct($config);

        $this->setState('constructed');

        return true;
    }

    /**
     * Define some Core Application constants and hooks
     *
     * @return null
     */
    protected function constructApplication()
    {
        //Register Core Namespace
        self::registerNamespace('\Backend\Core', true);

        //Register all Vendor Namespaces
        if (file_exists(VENDOR_FOLDER)) {
            foreach (glob(VENDOR_FOLDER . '*/*', \GLOB_ONLYDIR) as $folder) {
                $namespace = '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', str_replace(VENDOR_FOLDER, '', $folder));
                self::registerNamespace($namespace);
            }
        }

        //Register all Application Namespaces
        if (file_exists(SOURCE_FOLDER)) {
            foreach (glob(SOURCE_FOLDER . '*/*', \GLOB_ONLYDIR) as $folder) {
                $namespace = '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', str_replace(SOURCE_FOLDER, '', $folder));
                self::registerNamespace($namespace);
            }
        }

        //PHP Helpers
        register_shutdown_function(array('\Backend\Core\Application', 'shutdown'));

        set_exception_handler(array('\Backend\Core\Application', 'exception'));
        set_error_handler(array('\Backend\Core\Application', 'error'));

        if (empty(self::$siteState)) {
            self::setSiteState(defined('SITE_STATE') ? SITE_STATE : 'production');
        }

        //Some constants
        if (!defined('CONFIG_EXT')) {
            define('CONFIG_EXT', 'yaml');
        }

        //Set the Debug Level
        if (empty($_SERVER['DEBUG_LEVEL'])) {
            switch (self::getSiteState()) {
            case 'development':
                self::setDebugLevel(5);
                break;
            case 'production':
            case 'testing':
                self::setDebugLevel(1);
                break;
            }
        } else {
            self::setDebugLevel($_SERVER['DEBUG_LEVEL']);
        }

        self::$constructed = true;
    }

    /**
     * Main function for the application
     *
     * @param \Backend\Core\Request $request The request the application should handle
     * @param \Backend\Core\Route $route A route object to execute on
     *
     * @return mixed The result of the call
     * @todo   Make the 404 page pretty
     */
    public function main(Request $request = null, Route $route = null)
    {
        $this->setState('main');

        $this->setRequest($request instanceof Request ? $request : new Request());
        $request = $this->getRequest();

        //Resolve the Route
        $this->route = $route instanceof Route ? $route : new Route();

        try {
            //Get the Route
            $routePath = $this->route->resolve($request);
            //Get the callback and arguments
            $callback  = $routePath->getCallback();
            $arguments = $routePath->getArguments();
            //And execute
            $response  = $this->execute($callback, $arguments);
        } catch (Exceptions\UncallableMethodException $e) {
            new ApplicationEvent($e->getMessage(), ApplicationEvent::SEVERITY_WARNING);
            $response = new Response($e, 404);
        } catch (Exceptions\UnknownControllerException $e) {
            new ApplicationEvent($e->getMessage(), ApplicationEvent::SEVERITY_WARNING);
            $response = new Response($e, 404);
        } catch (Exceptions\UnrecognizedRequestException $e) {
            $response = new Response($e, 400);
        } catch (\Exception $e) {
            $response = new Response($e, 500);
        }
        $this->setState('mained');
        return $response;
    }

    /**
     * Execute the given callback, with the given arguments, and format as required
     *
     * @param callable                       $callback  The callback to execute
     * @param array                          $arguments The arguments to pass to the callback
     * @param \Backend\Core\Utilities\Format $format The Format class to transform the result into a Response
     *
     * @return \Backend\Core\Response The result of the callback transformed into a Response
     */
    public function execute($callback, $arguments, Format $format = null)
    {
        $isCallable = is_callable($callback, true, $methodMessage);
        if ($isCallable
            && is_array($callback)
            && ($callback[0] instanceof Decorators\Decorator)
        ) {
            $isCallable = $callback[0]->isCallable($callback[1]);
        } else {
            $isCallable = is_callable($callback);
        }
        new ApplicationEvent(
            'Executing ' . $methodMessage . ' with ' . count($arguments) . ' arguments',
            ApplicationEvent::SEVERITY_DEBUG
        );
        $result = call_user_func_array($callback, $arguments);
        $this->setState('executed');

        $this->setState('transforming');
        //Format the response using a Format class
        $format = $format ?: Format::build($this->getRequest());
        //Convert the result to a Respose
        $response = $format->transform($result, $callback, $arguments);

        $this->setState('transformed');
        return $response;
    }

    /**
     * Shutdown function called when ever the script ends
     *
     * @return null
     */
    public static function shutdown()
    {
        new ApplicationEvent('Shutting down Application', ApplicationEvent::SEVERITY_DEBUG);
    }

    /**
     * Error handling function called when ever an error occurs.
     *
     * Called by set_error_handler. Some types of errors will be converted into excceptions.
     *
     * @param int    $errno   The error number
     * @param string $errstr  The error string
     * @param string $errfile The file the error occured in
     * @param int    $errline The line number the errro occured on
     *
     * @return null
     */
    public static function error($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
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
    public static function exception(\Exception $exception)
    {
        new ApplicationEvent('Exception: ' . $exception->getMessage(), ApplicationEvent::SEVERITY_CRITICAL);
        //TODO: Let the Application be able to handle (and pretty up) Exceptions
        /*$data = array(
            'error/exception' => '',
            'exception' => $exception,
        );
        try {
            $response = $this->main(new Request($data, 'get'));
            //Which is then outputted to the Client
            $response->output();
            } catch (\Exception $e) {*/
        //We can't use handleResponse, as it throws exceptions. Just do the transform

        /*
        $view = ServiceLocator::get('backend.View');
        if (!$view) {*/
            echo (string)$exception;
            return;
        //}

        //Convert the result to a Respose
        $response = $view->transform($exception);

        if (!($response instanceof Response)) {
            echo $response;
            return;
        }
        $response->setStatusCode(500);
        $response->output();
        //}
    }

    /**
     * Register a namespace with the application
     *
     * @param string  $namespace The namespace
     * @param boolean $prepend   If the namespace should be prepended to the list
     *
     * @return null
     */
    public static function registerNamespace($namespace, $prepend = false)
    {
        if (in_array($namespace, self::$_namespaces)) {
            return;
        }
        if ($prepend) {
            array_unshift(self::$_namespaces, $namespace);
        } else {
            self::$_namespaces[] = $namespace;
        }
    }

    /**
     * Get the current Request
     *
     * @return Request The current Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set the Request for the Application
     *
     * @param Request $request The request for the Application
     *
     * @return null
     */
    public function setRequest(Request $request)
    {
        $this->_request = $request;
    }

    /**
     * Get the current RoutePath
     *
     * @return RoutePath The current RoutePath
     */
    public function getRoutePath()
    {
        return $this->_routePath;
    }

    /**
     * Set the RoutePath for the Application
     *
     * @param RoutePath $routePath The routePath for the Application
     *
     * @return null
     */
    public function setRoutePath(Utilities\RoutePath $routePath)
    {
        $this->_routePath = $routePath;
    }

    /**
     * Get the current Constructed state of the Application
     *
     * @return boolean The Constructed state of the Application
     */
    public static function getConstructed()
    {
        return self::$constructed;
    }

    /**
     * Set the constructed state of the application
     * 
     * @param boolean $constructed The new constructed state
     *
     * @return void
     */
    public static function setConstructed($constructed)
    {
        self::$constructed = (bool)$constructed;
    }

    /**
     * Return the registered namespaces for the application.
     *
     * @see registerNamespace
     * @return array The namespaces for the application
     */
    public static function getNamespaces()
    {
        return self::$_namespaces;
    }

    /**
     * Public getter for the Site State
     *
     * @return integer The site state
     */
    public static function getSiteState()
    {
        return self::$siteState;
    }

    /**
     * Public setter for the Site State
     *
     * @param string $state The site state
     *
     * @return null
     */
    public static function setSiteState($state)
    {
        self::$siteState = $state;
    }

    /**
     * Public getter for the Debug Level
     *
     * @return integer The debugging levels
     */
    public static function getDebugLevel()
    {
        return self::$debugLevel;
    }

    /**
     * Public setter for the Debug Level
     *
     * @param integer $level The debugging levels
     *
     * @return null
     */
    public static function setDebugLevel($level)
    {
        $level = (int)$level;
        if ($level <= 0) {
            return false;
        }
        self::$debugLevel = $level;
    }

    /**
     * Return the current state of the Application.
     *
     * @return array The current state of the Application.
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the current state of the Application.
     *
     * @param string $state The new state of the Application.
     *
     * @return Application The current object.
     */
    public function setState($state)
    {
        $this->state = $state;
        $this->notify();
        return $this;
    }

    /**
     * Check the code bases for a class
     *
     * Packages can be in two locations, PROJECT_FOLDER . libraries and PROJECT_FOLDER . app.
     * This function checks the packages in the two locations for the specified class
     *
     * @param string $className The class name to check
     * @param string $type      The type of class it is
     *
     * @return null
     */
    public static function resolveClass($className, $type = false)
    {
        //If it's a specified class, return
        if (substr($className, 0, 1) == '\\'
            && class_exists($className, true)
        ) {
            return $className;
        }

        if ($type) {
            $className = Utilities\Strings::className($className . ' ' . $type);
            switch (strtolower($type)) {
            case 'controller':
                $className = 'Controllers/' . $className;
                break;
            case 'interface':
                $className = 'Interfaces/' . $className;
                break;
            case 'exception':
                $className = 'Exceptions/' . $className;
                break;
            }
        }
        $namespaces = array_reverse(self::getNamespaces());
        //No namespace, so go through the namespaces to find the class
        foreach ($namespaces as $base) {
            $folder = str_replace('\\', DIRECTORY_SEPARATOR, $base);
            if ($files  = glob(PROJECT_FOLDER . '*' . $folder . '/' . $className . '.php')) {
                $className = $base . '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $className);
                include_once $files[0];
                return $className;
            }
        }


        //Try the type
        $className = Utilities\Strings::className($className . ' ' . $type);
        if (class_exists($className, true)) {
            return $className;
        }

        return false;
    }
}
