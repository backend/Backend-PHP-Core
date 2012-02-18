<?php
namespace Backend\Core;
/**
 * File defining Application
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
 * In the traditional PHP setup, you will have one appllication per request. The application
 * will always have one view associated with it. If not specified, the view will be
 * deduced from the request.
 *
 * If you need to serve multiple views with one request, it is possible to instansiate
 * Application multiple times.
 *
 * If you need to serve multiple routes with one request, instansiate one application,
 * and execute the main function multiple times with the specified routes. You can also
 * run {@link Controller::execute} multiple times, although the behaviour around that
 * is undefined.
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
    private static $_namespaces = array();

    /**
     * @var integer The debugging level. The higher, the more verbose
     */
    protected static $_debugLevel = 3;

    /**
     * @var Request This contains the Request that is being handled
     */
    private $_request = null;

    /**
     * The constructor for the class
     *
     * @param mixed The Configuration to be used for the application. Can be a the
     * path of a config file, or a Config object
     */
    function __construct($config = null)
    {
        if (!self::$_constructed) {
            //Register Core Namespace
            self::registerNamespace('\Backend\Core', true);

            //Register all Vendor Namespaces
            $checkFolder = function($param) { return !(preg_match('/^[_.]/', $param) || !is_dir(VENDOR_FOLDER . $param)); };
            if (file_exists(VENDOR_FOLDER)) {
                foreach (glob(VENDOR_FOLDER . '*/*', \GLOB_ONLYDIR) as $folder) {
                    $namespace = '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', str_replace(VENDOR_FOLDER, '', $folder));
                    self::registerNamespace($namespace);
                }
            }
            
            //Register all Application Namespaces
            $checkFolder = function($param) { return !(preg_match('/^[_.]/', $param) || !is_dir(SOURCE_FOLDER . $param)); };
            if (file_exists(SOURCE_FOLDER)) {
                foreach (glob(SOURCE_FOLDER . '*/*', \GLOB_ONLYDIR) as $folder) {
                    $namespace = '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', str_replace(SOURCE_FOLDER, '', $folder));
                    self::registerNamespace($namespace);
                }
            }

            //PHP Helpers
            register_shutdown_function(array($this, 'shutdown'));

            set_exception_handler(array($this, 'exception'));
            set_error_handler(array($this, 'error'));

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


            self::$_constructed = true;
        }

        //Setup the specified tools
        //TODO: Maybe move the Toolbox to a separate class
        self::$_toolbox = array();

        //Use the default view until we have a request to use
        self::addTool('View', new View());

        if ($config === null) {
            if (file_exists(PROJECT_FOLDER . 'configs/' . SITE_STATE . '.yaml')) {
                $config = PROJECT_FOLDER . 'configs/' . SITE_STATE . '.yaml';
            } else if (file_exists(PROJECT_FOLDER . 'configs/default.yaml')) {
                $config = PROJECT_FOLDER . 'configs/default.yaml';
            } else {
                throw new \Exception(
                    'Could not find Configuration file. . Add one to ' . PROJECT_FOLDER . 'configs'
                );
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
     * Main function for the application
     *
     * @param Request The request to handle
     * @return Response The result of the call
     */
    public function main(Request $request = null)
    {
        $request = $request instanceof Request ? $request : new Request();

        //Determine the View
        try {
            $view = Utilities\ViewFactory::build($request);
        } catch (Exceptions\UnrecognizedRequestException $e) {
            Application::log('View Exception: ' . $e->getMessage(), 2);
            $view = new View();
        }
        self::log('Running Application in ' . get_class($view) . ' View');
        self::addTool('View', $view);
        
        //Resolve the Route
        $this->route = new Route();
        $routePath   = $this->route->resolve($request);
        if (!($routePath instanceof Utilities\RoutePath)) {
            throw new Exceptions\UnknownRouteException($request->getQuery());
        }

        //Determine the Call
        $controller = $routePath->getController();
        $action     = $routePath->getAction();
        $arguments  = $routePath->getArguments();
        
        $controllerClass = self::resolveClass($controller, 'controller');
        $methodName      = Utilities\Strings::camelCase($action . ' Action');
        
        if (!class_exists($controllerClass, true)) {
            throw new \Exception('Unknown Controller: ' . $controllerClass);
        }
        
        $controller = new $controllerClass($request);
        //Decorate the Controller
        if ($controller instanceof Interfaces\Decorable) {
            foreach ($controller->getDecorators() as $decorator) {
                $controller = new $decorator($controller);
                if (!($controller instanceof \Backend\Core\Decorators\ControllerDecorator)) {
                    throw new \Exception(
                        'Class ' . $decorator . ' is not an instance of \Backend\Core\Decorators\ControllerDecorator'
                    );
                }
            }
        }

        //Make the Call
        if (method_exists($controller, $methodName)) {
            $functionCall = array($controller, $methodName);
            //Execute the Controller method
            Application::log('Executing ' . get_class($functionCall[0]) . '::' . $functionCall[1], 4);
            $result = call_user_func_array($functionCall, $arguments);
        } else {
            throw new \Exception('Unknown call ' . $controllerClass . '::' . $methodName);
        }
        
        return $this->handleResult($result);
    }
    
    /**
     * Handle the result from the executed controller
     *
     * @param mixed The result returned from the controller
     * @return Response The response object to be outputted
     */
    private function handleResult($result)
    {
        //Return if we already have a Response
        if ($result instanceof Response) {
            return $result;
        }
        
        $view = self::getTool('View');

        //Convert the result to a Respose
        $response = $view->transform($result);

        if (!($response instanceof Response)) {
            throw new \Exception('Unrecognized Response');
        }
        return $response;

        //TODO: Do we want to allow the use of viewMethods?
        if ($view) {
            //Execute the View related method
            $viewMethod = $this->getViewMethod($action, $view);
            if ($viewMethod instanceof \ReflectionMethod) {
                Application::log('Executing ' . get_class($this) . '::' . $viewMethod->name, 4);
                $response = $viewMethod->invokeArgs($this, array($response));
            }
        }
    }

    /**
     * Shutdown function called when ever the script ends
     */
    public function shutdown()
    {
        self::log('Shutting down Application', 3);
    }

    /**
     * Error handling function called when ever an error occurs.
     *
     * Some types of errors will be converted into excceptions.
     * Called by set_error_handler.
     */
    public function error($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Exception handling function called when ever an exception isn't handled.
     *
     * Called by set_exception_handler.
     */
    public function exception($exception)
    {
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
            $response = $this->handleResult($exception);
            $response->setStatusCode(500);
            $response->output();
        //}
    }

    /**
     * Add a tool to the application
     *
     * @param mixed The tool to add. Can also be the name of a class to instansiate
     * @param array The parameters to pass to the constructor of the Tool
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
     * @param string The class of the tool to retrieve
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
     * @param string The namespace
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
     * Check the code bases for a class
     *
     * Packages can be in two locations, PROJECT_FOLDER . libraries and PROJECT_FOLDER . app.
     * This function checks the packages in the two locations for the specified class
     *
     * @param string The class name to check
     * @param string The type of class it is
     */
    public static function resolveClass($className, $type = false)
    {
        //If it's a specified class, return
        if (
            substr($className, 0, 1) == '\\'
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
                include($files[0]);
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
     * @param string message The message
     * @param integer level The logging level of the message
     * @param string context The context of the message
     */
    public static function log($message, $level = Utilities\LogMessage::LEVEL_IMPORTANT, $context = false)
    {
        /*if (!self::$_constructed || !class_exists('Backend\Core\Utilities\LogMessage', true)) {
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
