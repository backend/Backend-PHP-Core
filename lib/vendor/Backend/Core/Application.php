<?php
namespace Backend\Core;
/**
 * File defining Core\Application
 *
 * Copyright (c) 2011 JadeIT cc
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the
 * following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR
 * A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package CoreFiles
 */
/**
 * The main application class.
 *
 * The application will / should be the only singleton in the framework, acting as
 * a Toolbox. That means that any resource that should be globally accessable (and
 * some times a singleton) should be passed to the Application. Read more at
 * {@link http://www.ibm.com/developerworks/webservices/library/co-single/index.html#h3}
 *
 * @package Core
 */
class Application
{
    /**
     * @var boolean This property indicates if the application has been initialized yet.
     */
    protected $_initialized = false;

    /**
     * @var boolean This static property indicates if the application has been constructed yet.
     */
    protected static $_constructed = false;

    /**
     * @var array This contains all tools that should be globally accessable. Use this wisely.
     */
    private static $_toolbox = array();

    /**
     * @var array This contains all the namespaces for the application
     */
    private static $_namespaces = array('Base');

    /**
     * This contains the router object that will help decide what controller, model and action to execute
     * @var Core\Router
     */
    private $_router = null;

    /**
     * This contains the request object that will influence the router and view objects.
     * @var Core\Request
     */
    private $_request = null;

    /**
     * This contains the view object that display the executed request
     * @var Core\View
     */
    private $_view = null;

    /**
     * @var integer The debugging level. The higher, the more verbose
     */
    protected static $_debugLevel = 3;

    /**
     * The class constructor
     *
     * @param Core\View The view for the application
     * @param Request The request to handle
     * @param array An array of tools to instansiate
     */
    function __construct(Core\View $view = null, Core\Request $request = null, array $tools = array())
    {
        $this->init();

        //Initiate the Tools
        foreach ($tools as $toolName => $tool) {
            self::addTool($toolName, $tool);
        }

        //Get the Request
        $this->_request = is_null($request) ? new Request() : $request;

        //Get the View
        if ($view instanceof CoreView) {
            $this->_view = $view;
        } else {
            try {
                $view = Utilities\ViewFactory::build($this->_request);
            } catch (\Exception $e) {
                self::log('View Exception: ' . $e->getMessage(), 2);
                $view = new View();
            }
            $this->_view = $view;
        }

        self::$_constructed = true;
        self::log('Showing application with ' . get_class($this->_view));
    }

    /**
     * Initialize the Application.
     *
     * This is a bootstrapping function and should only be run once.
     *
     * @return boolean If the initialization was succesful or not.
     */
    protected function init()
    {
        if ($this->_initialized) {
            return true;
        }

        //Core at the beginning, Application at the end
        self::registerNamespace('Core', true);
        self::registerNamespace('Application');

        //Load extra functions
        include(BACKEND_FOLDER . '/modifiers.inc.php');

        //PHP Helpers
        //Prepend the master autoload function to the beginning of the stack
        spl_autoload_register(array('\Backend\Core\Application', '__autoload'), true, true);

        register_shutdown_function(array($this, 'shutdown'));

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

        $this->_initialized = true;

        return true;
    }

    /**
     * Main function for the application
     *
     * @param Core\Router The route to execute
     * @return mixed The result of the call
     */
    public function main(Router $router = null)
    {
        //Get Router
        $this->_router = is_null($router) ? new Router($this->_request) : $router;

        $result = null;
        try {
            //Get and check the model
            $model = self::translateModel($this->_router->getArea());
            if (!class_exists($model, true)) {
                throw new Exceptions\UnknownModelException('Unkown Model: ' . $model);
            }
            $modelObj = new $model();

            //See if a controller exists for this model
            $controller = self::translateController($this->_router->getArea());
            if (!class_exists($controller, true)) {
                //Otherwise run the core controller
                $controller = 'Core\Controller';
            }
            $controllerObj = new $controller($modelObj, $this->_view);

            //Execute the Application Logic
            $action = $this->_router->getAction() . 'Action';
            $result = $controllerObj->execute(
                $action,
                $this->_router->getIdentifier(),
                $this->_router->getArguments()
            );
        } catch (\Exception $e) {
            self::log('Logic Exception: ' . $e->getMessage(), 1);
            //TODO Get the Error Model, and execute
            //TODO Handle UknownRouteException
            //TODO Handle UnknownModelException
            //TODO Handle UnsupportedMethodException
            $result = $e;
        }

        //Output
        $this->_view->bind('result', $result);
        $this->_view->output();
        return $result;
    }

    public function shutdown()
    {
        self::log('Shutting down Application', 3);
    }

    /**
     * Add a tool to the application
     *
     * @param mixed The tool to add. Can also be the name of a class to instansiate
     * @param array The parameters to pass to the constructor of the Tool
     */
    public static function addTool($toolName, $tool, array $parameters = array())
    {
        if (is_string($tool)) {
            $function = false;
            if (is_callable(array($tool, 'getInstance'))) {
                $function = array($tool, 'getInstance');
            } else if (is_callable(array($tool, 'instance'))) {
                $function = array($tool, 'instance');
            } else if (is_callable(array($tool, 'factory'))) {
                $function = array($tool, 'factory');
            } else if (is_callable(array($tool, 'singleton'))) {
                $function = array($tool, 'singleton');
            } else if (is_callable(array($tool, 'build'))) {
                $function = array($tool, 'build');
            }
            if ($function) {
                $tool = call_user_func_array($function, $parameters);
            } else {
                $tool = new $tool($parameters);
            }
        }
        $toolName = empty($toolName) || is_numeric($toolName) ? get_class($tool) : $toolName;
        self::$_toolbox[$toolName] = $tool;
    }

    /**
     * Get a tool from the application
     *
     * @param string The class of the tool to retrieve
     * @return mixed The requested Tool, or null if it doesn't exist
     */
    public static function getTool($className)
    {
        //Check that we have a running Application first
        if (!self::$_constructed) {
            return false;
        }

        if (array_key_exists($className, self::$_toolbox)) {
            return self::$_toolbox[$className];
        }
        return null;
    }

    /**
     * Register a namespace with the application
     *
     * @param string The namespace
     */
    public static function registerNamespace($namespace, $prepend = false)
    {
        if (!in_array($namespace, self::$_namespaces)) {
            if ($prepend) {
                array_unshift(self::$_namespaces, $namespace);
            } else {
                self::$_namespaces[] = $namespace;
            }
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
        return self::$_debugLevel;
    }

    /**
     * Public setter for the Debug Level
     *
     * @param integer The debugging levels
     */
    public function setDebugLevel($level)
    {
        if ($level <= 0) {
            return false;
        }
        self::$_debugLevel = $level;
    }

    /**
     * Function to autoload BackendMVC classes
     *
     * It gets set by Core\Application::init
     * @param string The class name to auto load
     * @return boolean If the class file was found and included
     */
    public static function __autoload($className)
    {
        self::log('Checking for ' . $className, 5);

        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        var_dump(VENDOR_FOLDER . $fileName);
        if (file_exists(VENDOR_FOLDER . $fileName)) {
            require_once(VENDOR_FOLDER . $fileName);
            return true;
        } else {
            return false;
        }


        if (substr($className, 0, 1) == '\\') {
            $className = substr($className, 1);
        }
        //Check for a name spaced className
        $parts = explode('\\', $className);
        if (count($parts) === 1) {
            $bases = self::getNamespaces();
        } else {
            $bases = array(reset($parts));
            $className = implode('/', array_slice($parts, 1));
        }
        $bases = array_reverse($bases);
        foreach ($bases as $base) {
            //Check other types
            foreach ($types as $type => $part) {
                if (
                    file_exists(BACKEND_FOLDER . '/' . $base . '/' . $type . '/' . $className . '.' . $part . '.php')
                ) {
                    require_once(BACKEND_FOLDER . '/' . $base . '/' . $type . '/' . $className . '.' . $part . '.php');
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Utility function to translate a URL part to a Controller Name
     *
     * All Controllers are plural, and ends with Controller
     * @param string The resource to translate into a Controller name
     * @return string The translated Controller Name
     * @todo We need to define naming standards
     */
    public static function translateController($resource)
    {
        return class_name($resource) . 'Controller';
    }

    /**
     * Utility function to translate a URL part to a Model Name
     *
     * All Models are plural, and ends with Model
     * @param string The resource to translate into a Model name
     * @return string The translated Model Name
     * @todo We need to define naming standards
     */
    public static function translateModel($resource)
    {
        return class_name($resource) . 'Model';
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
     * @param string message The message
     * @param integer level The logging level of the message
     * @param string context The context of the message
     */
    public static function log($message, $level = 3, $context = false)
    {
        if ($level > self::$_debugLevel) {
            return false;
        }

        $logger = self::getTool('Logger');
        if (!$logger) {
            return false;
        }

        if (!$context) {
            $bt = debug_backtrace();
            //Remove the call to this function
            array_shift($bt);
            if ($caller = reset($bt)) {
                $context = empty($caller['class']) ? $caller['file'] : $caller['class'];
            }
        }
        $context = $context ? $context : get_called_class();
        if ($context) {
            $message = '[' . $context . '] ' . $message;
        }

        switch ($level) {
        case 1:
            $message = ' (CRITICAL) ' . $message;
            break;
        case 2:
            $message = ' (WARNING) ' . $message;
            break;
        case 3:
            $message = ' (IMPORTANT) ' . $message;
            break;
        case 4:
            $message = ' (DEBUG) ' . $message;
            break;
        case 5:
            $message = ' (INFORMATION) ' . $message;
            break;
        default:
            $message = ' (OTHER - ' . $level . ') ' . $message;
            break;
        }

        return $logger->log($message, $level);
    }

    /**
     * Mail function hook. This will call the provided Mailer to do the mailing.
     *
     * @param string The recipient of the email
     * @param string The subject of the email
     * @param string The content of the email
     * @param array Extra email options
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
