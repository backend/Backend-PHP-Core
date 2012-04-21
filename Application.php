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
/**
 * The main application class.
 *
 * The application will / should be the only singleton in the framework, acting as
 * a Toolbox. That means that any resource that should be globally accessable (and
 * some times a singleton) should be passed to the Application. Read more at
 * {@link http://www.ibm.com/developerworks/webservices/library/co-single/index.html#h3}
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
     * @param Request $request The request the application should handle
     * @param mixed   $config  The Configuration to be used for the application. Can be a the
     * path of a config file, or a Config object
     */
    function __construct(Request $request = null, $config = null)
    {
        $this->setState('constructing');

        $this->setRequest($request instanceof Request ? $request : new Request());

        if (!self::$constructed) {
            self::constructApplication();
        }

        //Setup the specified tools
        //TODO: Maybe move the Toolbox to a separate class
        self::$_toolbox = array();

        if ($config === null) {
            if (file_exists(PROJECT_FOLDER . 'configs/' . self::getSiteState() . '.yaml')) {
                $config = PROJECT_FOLDER . 'configs/' . self::getSiteState() . '.yaml';
            } else if (file_exists(PROJECT_FOLDER . 'configs/default.yaml')) {
                $config = PROJECT_FOLDER . 'configs/default.yaml';
            } else {
                $string = 'Could not find Configuration file. . Add one to ' . PROJECT_FOLDER . 'configs';
                throw new \Exception($string);
            }
        }
        if (is_string($config)) {
            //String specifies that we should parse the file specified
            $config = new Utilities\Config($config);
        }
        self::addTool('Config', $config);

        //Determine the View
        try {
            $view = Utilities\ViewFactory::build($this->getRequest());
        } catch (Exceptions\UnrecognizedRequestException $e) {
            new ApplicationEvent('View Exception: ' . $e->getMessage(), ApplicationEvent::SEVERITY_WARNING);
            $view = new View($this->getRequest());
        }
        new ApplicationEvent('Running Application in ' . get_class($view) . ' View', ApplicationEvent::SEVERITY_INFORMATION);
        self::addTool('View', $view);

        //Initiate the Tools
        self::addTool('Application', $this);

        $tools = $config->tools;
        if ($tools) {
            foreach ($tools as $toolName => $tool) {
                self::addTool($toolName, $tool);
            }
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
     * @param \Backend\Core\Route $route A route object to execute on
     *
     * @return mixed The result of the call
     * @todo   Make the 404 page pretty
     */
    public function main(Route $route = null)
    {
        $this->setState('executing');

        $request = $this->getRequest();
        //Resolve the Route
        $this->route = $route instanceof Route ? $route : new Route();
        try {
            $this->setRoutePath($this->route->resolve($request));
            $result = $this->executeRoutePath();
        } catch (Exceptions\UncallableMethodException $e) {
            new ApplicationEvent($e->getMessage(), ApplicationEvent::SEVERITY_WARNING);
            $result = new Response($e, 404);
        } catch (Exceptions\UnknownControllerException $e) {
            new ApplicationEvent($e->getMessage(), ApplicationEvent::SEVERITY_WARNING);
            $result = new Response($e, 404);
        }

        $this->setState('executed');
        return $this->handleResult($result);
    }

    /**
     * Execute the identified routePath.
     *
     * @param Utilities\RoutePath $routePath The RoutePath to execute
     *
     * @return mixed The result of the callback
     */
    protected function executeRoutePath(Utilities\RoutePath $routePath = null)
    {
        $routePath = $routePath ? $routePath : $this->getRoutePath();

        //Determine the Call
        $callback  = $routePath->getCallback();
        $arguments = $routePath->getArguments();

        $request = $this->getRequest();
        $isCallable = is_callable($callback);
        if (is_array($callback)) {
            //Set the request for the callback
            $callback[0]->setRequest($request);
            $methodMessage = get_class($callback[0]) . '::' . $callback[1];
            if ($callback[0] instanceof \Backend\Core\Decorators\Decorator) {
                $isCallable = $callback[0]->isCallable($callback[1]);
            }
        } else {
            //The first argument for the callback is the request
            array_unshift($request, $arguments);
            $methodMessage = $callback;
        }
        new ApplicationEvent('Executing ' . $methodMessage, ApplicationEvent::SEVERITY_DEBUG);

        if (!$isCallable) {
            throw new Exceptions\UncallableMethodException('Undefined method - ' . $methodMessage);
        }
        $result = call_user_func_array($callback, $arguments);

        //Execute the View related method
        if (is_array($callback)) {
            $view = self::getTool('View');
            $viewMethod = $this->getViewMethod($callback, $view);
            //Do both the is_callable check and the try, as some __call methods throw an exception
            if (is_callable(array($callback[0], $viewMethod))) {
                new ApplicationEvent(
                    'Executing ' . get_class($callback[0]) . '::' . $viewMethod, ApplicationEvent::SEVERITY_DEBUG
                );
                try {
                    $result = call_user_func(array($callback[0], $viewMethod), $result);
                } catch (Exceptions\UncallableMethodException $e) {
                    new ApplicationEvent(
                        get_class($callback[0]) . '::' . $viewMethod . ' does not exist',
                        ApplicationEvent::SEVERITY_DEBUG
                    );
                    unset($e);
                }
            }
        }

        return $result;
    }

    /**
     * Handle the result from the executed callback
     *
     * @param mixed $result The result returned from the callback
     *
     * @return Response The response object to be outputted
     * @todo Not sure why this was static?
     */
    protected function handleResult($result)
    {
        $this->setState('transforming');
        $view = self::getTool('View');
        //Make sure we have a view to work with
        if (!$view) {
            throw new \Exception('No View to work with');
            //$view = new View($this->getRequest());
        }

        //Convert the result to a Respose
        $response = $view->transform($result);

        if (!($response instanceof Response)) {
            throw new \Exception('Unrecognized Response');
        }
        $this->setState('transformed');
        return $response;
    }

    /**
     * Return a view method for the specified action
     *
     * @param array $callback The callback to check for
     * @param View  $view     The view to use
     *
     * @return string The name of the View Method
     */
    public function getViewMethod(array $callback, View $view = null)
    {
        $view = is_null($view) ? self::getTool('View') : $view;

        //Check for a transform for the current view in the controller
        $methodName = get_class($view);
        $methodName = substr($methodName, strrpos($methodName, '\\') + 1);
        $methodName = preg_replace('/Action$/', $methodName, $callback[1]);
        return $methodName;
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
        self::exception(new \ErrorException($errstr, 0, $errno, $errfile, $errline));
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

        $view = self::getTool('View');
        if (!$view) {
            echo (string)$exception;
            return;
        }

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
     * Add a tool to the application
     *
     * @param mixed $toolName The tool to add. Can also be the name of a class to instansiate
     * @param array $tool     The parameters to pass to the constructor of the Tool
     *
     * @return null
     */
    public static function addTool($toolName, $tool)
    {
        if (is_string($tool)) {
            if (class_exists($tool, true)) {
                $tool = new $tool();
            } else {
                new ApplicationEvent('Undefined Tool: ' . $tool, ApplicationEvent::SEVERITY_DEBUG);
                throw new Exceptions\BackendException('Undefined Tool: ' . $tool);
            }
        } else if (is_array($tool) && count($tool) == 2) {
            if (class_exists($tool[0], true)) {
                $tool = new $tool[0]($tool[1]);
            } else {
                new ApplicationEvent('Undefined Tool: ' . $tool[0], ApplicationEvent::SEVERITY_DEBUG);
                throw new Exceptions\BackendException('Undefined Tool: ' . $tool[0]);
            }
        }
        $toolName = empty($toolName) || is_numeric($toolName) ? get_class($tool) : $toolName;
        self::$_toolbox[$toolName] = $tool;
    }

    /**
     * Get a tool from the application
     *
     * @param string $className The class of the tool to retrieve
     *
     * @return mixed The requested Tool, or null if it doesn't exist
     */
    public static function getTool($className)
    {
        if (array_key_exists($className, self::$_toolbox)) {
            return self::$_toolbox[$className];
        }
        return null;
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
    public function getConstructed()
    {
        return self::$constructed;
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
        $namespaces = array_reverse(\Backend\Core\Application::getNamespaces());
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

    /**
     * Mail function hook. This will call the provided Mailer to do the mailing.
     *
     * @param string $recipient The recipient of the email
     * @param string $subject   The subject of the email
     * @param string $message   The content of the email
     * @param array  $options   Extra email options
     *
     * @return boolean If the mail was succesfully scheduled
     */
    public static function mail($recipient, $subject, $message, array $options = array())
    {
        $mail = self::getTool('Mailer');

        if (array_key_exists('headers', $options)) {
            $headers = $options['headers'];
            unset($options['headers']);
        } else {
            $headers = array();
        }

        if ($mail) {
        } else {
            $options['headers'] = 'X-Mailer: BackendCore / PHP';
            return mail($recipient, $subject, $message, $options['headers'], $options);
        }
    }
}
