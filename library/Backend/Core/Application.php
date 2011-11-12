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
     * @var integer The debugging level. The higher, the more verbose
     */
    protected static $_debugLevel = 3;

    /**
     * The constructor for the class
     *
     * @param mixed The Configuration to be used for the application. Can be a the
     * path of a config file, or a Config object
     */
    function __construct($config = null)
    {
        if (!self::$_constructed) {
            //Core at the beginning, Application at the end
            self::registerNamespace('Core', true);
            self::registerNamespace('Application');

            //Load extra functions
            require_once(BACKEND_FOLDER . '/modifiers.inc.php');

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

            self::$_constructed = true;
        }

        //Setup the specified tools
        self::$_toolbox = array();
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
     * This function can be executed multiple times in one request, so one request
     * can be mapped to multiple routes.
     *
     * @param Route The route to execute
     * @return Response The result of the call
     */
    public function main(Route $route = null)
    {
        $route = $route instanceof Route ? $route : new Route();

        $controllerName = 'Backend\Controllers\\' . class_name($route->getArea());
        if (!class_exists($controllerName, true)) {
            //Otherwise check the Bases for a controller
            $controllerName = self::getBackendClass('Controller');
        }
        if (!class_exists($controllerName)) {
            throw new \Exception('Unknown Controller: ' . $controllerName);
        }

        $controller = new $controllerName($route->getRequest());
        //Decorate the Controller
        foreach ($controller->getDecorators() as $decorator) {
            $controller = new $decorator($controller);
        }

        //Execute the controller
        return $controller->execute();
    }

    public function output(Response $response)
    {
        //Pass the result to the View
        $response = $this->_view->transform($this->_response);
        echo $response;
        return $response;
    }


    /**
     * Shutdown function called when ever the script ends
     */
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
        //self::log('Checking for ' . $className, 5);

        $className = ltrim($className, '\\');
        $parts  = explode('\\', $className);
        $vendor = false;
        $base   = false;
        if (count($parts) > 1) {
            $vendor = $parts[0];
            if (count($parts) > 2) {
                $base = $parts[1];
            }
        }

        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $bases = self::getNamespaces();
        if ($vendor && $base && $vendor == 'Backend' && !in_array($base, $bases)) {
            //Not in a defined Base, check all
            $bases = array_reverse($bases);
            foreach ($bases as $base) {
                $namespace = implode('/', array_slice($parts, 1, count($parts) - 2));
                if (
                    file_exists(BACKEND_FOLDER . '/' . $base . '/' . $namespace . '/' . $className . '.php')
                ) {
                    require_once(BACKEND_FOLDER . '/' . $base . '/' . $namespace . '/' . $className . '.php');
                    return true;
                }
            }
        } else {
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
            if (file_exists(VENDOR_FOLDER . $fileName)) {
                require_once(VENDOR_FOLDER . $fileName);
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Check the registered bases for a class
     *
     * Using this function to retrieve a class name allows the coder to override
     * Base classes
     *
     * @param string The class name to check
     * @param string The type of class it is
     */
    public static function getBackendClass($className, $type = false)
    {
        $bases = array_reverse(self::getNamespaces());
        foreach ($bases as $base) {
            $class = 'Backend\\' . $base . '\\';
            if ($type) {
                $class .= $type . '\\';
            }
            $class .= $className;
            if (class_exists($class, true)) {
                return $class;
            }
        }
        return $className;
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

        $logMessage = new Utilities\LogMessage($message, $level);
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
