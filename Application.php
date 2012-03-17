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
class Application
{
    /**
     * @var boolean This static property indicates if the application has been constructed yet.
     */
    protected static $constructed = false;

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
     * @var Request This contains the Request that is being handled
     */
    private $_request = null;

    /**
     * The constructor for the class
     *
     * @param Request $request The request the application should handle
     * @param mixed   $config  The Configuration to be used for the application. Can be a the
     * path of a config file, or a Config object
     */
    function __construct(Request $request = null, $config = null)
    {
        $this->setRequest($request instanceof Request ? $request : new Request());

        if (!self::$constructed) {
            self::constructApplication();
        }

        //Setup the specified tools
        //TODO: Maybe move the Toolbox to a separate class
        self::$_toolbox = array();

        if ($config === null) {
            if (file_exists(PROJECT_FOLDER . 'configs/' . SITE_STATE . '.yaml')) {
                $config = PROJECT_FOLDER . 'configs/' . SITE_STATE . '.yaml';
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
        if (!($config instanceof \Backend\Core\Utilities\Config)) {
            throw new \Exception('Invalid Configuration');
        }
        self::addTool('Config', $config);

        //Determine the View
        try {
            $view = Utilities\ViewFactory::build($this->getRequest());
        } catch (Exceptions\UnrecognizedRequestException $e) {
            Application::log('View Exception: ' . $e->getMessage(), 2);
            $view = new View($this->getRequest());
        }
        self::log('Running Application in ' . get_class($view) . ' View');
        self::addTool('View', $view);

        //Initiate the Tools
        $tools = $config->tools;
        if ($tools) {
            foreach ($tools as $toolName => $tool) {
                self::addTool($toolName, $tool);
            }
        }
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
        $checkFolder = function($param) {
            return !(preg_match('/^[_.]/', $param) || !is_dir(VENDOR_FOLDER . $param));
        };
        if (file_exists(VENDOR_FOLDER)) {
            foreach (glob(VENDOR_FOLDER . '*/*', \GLOB_ONLYDIR) as $folder) {
                $namespace = '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', str_replace(VENDOR_FOLDER, '', $folder));
                self::registerNamespace($namespace);
            }
        }

        //Register all Application Namespaces
        $checkFolder = function($param) {
            return !(preg_match('/^[_.]/', $param) || !is_dir(SOURCE_FOLDER . $param));
        };
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

        //Some constants
        if (!defined('SITE_STATE')) {
            define('SITE_STATE', 'production');
        }

        if (empty($_SERVER['DEBUG_LEVEL'])) {
            switch (SITE_STATE) {
            case 'development':
                self::setDebugLevel(5);
                break;
            case 'production':
                self::setDebugLevel(1);
                break;
            }
        } else {
            self::setDebugLevel((int)$_SERVER['DEBUG_LEVEL']);
        }

        self::$constructed = true;
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
     * Get the current Constructed state of the Application
     *
     * @return boolean The Constructed state of the Application
     */
    public function getConstructed()
    {
        return self::$constructed;
    }

    /**
     * Main function for the application
     *
     * @return mixed The result of the call
     */
    public function main()
    {
        $request = $this->getRequest();

        //Resolve the Route
        $this->route = new Route();
        try {
            $routePath   = $this->route->resolve($request);
        } catch (Exceptions\UnknownControllerException $e) {
            self::log($e->getMessage(), 2);
            return new Response($e->getMessage(), 404);
        }

        return $this->executeRoutePath($routePath);
    }

    /**
     * Execute the identified routePath.
     *
     * @param Utilities\RoutePath $routePath The RoutePath to execute
     *
     * @return mixed The result of the callback
     */
    protected function executeRoutePath(Utilities\RoutePath $routePath)
    {
        $request = $this->getRequest();

        //Determine the Call
        $callback  = $routePath->getCallback();
        $arguments = $routePath->getArguments();

        if (is_array($callback)) {
            //Set the request for the callback
            $callback[0]->setRequest($request);
            Application::log('Executing ' . get_class($callback[0]) . '::' . $callback[1], 4);
        } else {
            //The first argument for the callback is the request
            Application::log('Executing ' . $callback, 4);
            array_unshift($request, $arguments);
        }

        $result = call_user_func_array($callback, $arguments);

        //Execute the View related method
        if (is_array($callback)) {
            $view = self::getTool('View');
            $viewMethod = $this->getViewMethod($callback, $view);
            Application::log('Executing ' . get_class($viewMethod[0]) . '::' . $viewMethod[1], 4);
            $result = call_user_func($viewMethod, $result);
        }

        return self::handleResult($result);
    }

    /**
     * Handle the result from the executed callback
     *
     * @param mixed $result The result returned from the callback
     *
     * @return Response The response object to be outputted
     */
    protected static function handleResult($result)
    {
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

        return $response;
    }

    /**
     * Return a view method for the specified action
     *
     * @param array $callback The callback to check for
     * @param View  $view     The view to use
     *
     * @return callback The callback to execute
     */
    public function getViewMethod(array $callback, View $view = null)
    {
        $view = is_null($view) ? self::getTool('View') : $view;

        //Check for a transform for the current view in the controller
        $methodName = get_class($view);
        $methodName = substr($methodName, strrpos($methodName, '\\') + 1);
        $methodName = preg_replace('/Action$/', $methodName, $callback[1]);

        $object = $callback[0] instanceof \Backend\Core\Interfaces\DecoratorInterface
            ? $callback[0]->isCallable($methodName) : $callback[0];

        return array($object, $methodName);
    }

    /**
     * Shutdown function called when ever the script ends
     *
     * @return null
     */
    public static function shutdown()
    {
        self::log('Shutting down Application', 3);
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
        self::log('Exception: ' . $exception->getMessage(), 1);
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
            $response = self::handleResult($exception);
            $response->setStatusCode(500);
            $response->output();
            die;
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
                self::log('Undefined Tool: ' . $tool);
            }
        } else if (is_array($tool) && count($tool) == 2) {
            if (class_exists($tool[0], true)) {
                $tool = new $tool[0]($tool[1]);
            } else {
                self::log('Undefined Tool: ' . $tool[0]);
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
     * Public getter for the Debug Level
     *
     * @return integer The debugging levels
     */
    public function getDebugLevel()
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
    public function setDebugLevel($level)
    {
        $level = (int)$level;
        if ($level <= 0) {
            return false;
        }
        self::$debugLevel = $level;
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
     * Logging function hook. This will call the provided Logger to do the logging
     *
     * Log levels:
     * 1. Critical Messages
     * 2. Warning | Alert Messages
     * 3. Important Messages
     * 4. Debugging Messages
     * 5. Informative Messages
     *
     * @param string  $message The message
     * @param integer $level   The logging level of the message
     * @param string  $context The context of the message
     *
     * @return Utilities\LogMessage The log message
     */
    public static function log($message, $level = Utilities\LogMessage::LEVEL_IMPORTANT, $context = false)
    {
        /*if (!self::$constructed || !class_exists('Backend\Core\Utilities\LogMessage', true)) {
            return false;
        }*/

        if (!$context) {
            $backtrace = debug_backtrace();
            //Remove the call to this function
            array_shift($backtrace);
            if ($caller = reset($backtrace)) {
                $context = empty($caller['class']) ? $caller['file'] : $caller['class'];
            }
        }
        $context = $context ? $context : get_called_class();
        if ($context) {
            $message = '[' . $context . '] ' . $message;
        }

        return new Utilities\LogMessage($message, $level);
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
