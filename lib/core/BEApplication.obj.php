<?php
/**
 * File defining BEApplication
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
 * @package Core
 */
class BEApplication
{
    /**
     * @var boolean This property indicates if the application has been initialized yet.
     */
    private $_initialized = false;

    /**
     * @var integer The debugging level. The higher, the more verbose
     */
    protected static $_debugLevel = 3;

    /**
     * This contains the router object that will help decide what controller, model and action to execute
     * @var BERouter
     */
    private $_router = null;

    /**
     * The class constructor
     */
    function __construct(BERouter $router = null)
    {
        $this->init();

        //Get Router
        $this->_router = is_null($router) ? new BERouter(new BERequest()) : $router;
    }

    /**
     * Initialize the Application.
     *
     * This is a bootstrapping function and should only be run once.
     *
     * @return boolean If the initialization was succesful or not.
     */
    private function init()
    {
        if ($this->_initialized) {
            return true;
        }
        if (empty($_SERVER['DEBUG_LEVEL'])) {
            switch (SITE_STATE) {
            case 'development':
                self::setDebugLevel(4);
                break;
            case 'production':
                self::setDebugLevel(1);
                break;
            }
        } else {
            self::setDebugLevel((int)$_SERVER['DEBUG_LEVEL']);
        }

        //PHP Helpers
        spl_autoload_register(array('BEApplication', '__autoload'));

        //Load extra functions
        include(BACKEND_FOLDER . '/functions.inc.php');
        include(BACKEND_FOLDER . '/modifiers.inc.php');

        $this->_initialized = true;

        return true;
    }

    /**
     * Main function for the application
     */
    public function main()
    {
        $result = null;
        try {
            //Get and check the model
            $model = self::translateModel($this->_router->getArea());
            if (!class_exists($model, true)) {
                throw new UnknownModelException('Unkown Model: ' . $model);
            }
            $modelObj = new $model();

            //See if a controller exists for this model
            $controller = self::translateController($this->_router->getArea());
            if (!class_exists($controller, true)) {
                //Otherwise run the core controller
                $controller = 'BEController';
            }
            $controllerObj = new $controller($modelObj);

            //Execute the Application Logic
            $action = $this->_router->getAction() . 'Action';
            $result = $controllerObj->execute(
                $action,
                $this->_router->getIdentifier(),
                $this->_router->getArguments()
            );
        } catch (Exception $e) {
            BEApplication::log('Logic Exception: ' . $e->getMessage(), 1);
            //TODO Get the Error Model, and execute
            //TODO Handle UknownRouteException
            //TODO Handle UnknownModelException
            //TODO Handle UnsupportedMethodException
            $result = $e;
        }

        //Get the View
        try {
            $view = self::translateView($this->_router->getFormat());
            if (!class_exists($view, true)) {
                throw new UnknownViewException('Unknown View: ' . $view);
            }
        } catch (Exception $e) {
            BEApplication::log('View Exception: ' . $e->getMessage(), 1);
            $view = 'BEView';
        }
        $viewObj = new $view($controllerObj, $modelObj, $result);
        $viewObj->output();
        return $result;
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
     * It gets set by BEApplication::init
     *
     * @return boolean If the class file was found and included
     */
    static public function __autoload($classname)
    {
        $types = array(
            'controllers' => 'ctl',
            'models'      => 'obj',
            'utilities'   => 'util',
            'views'       => 'view',
        );
        self::log('Checking for ' . $classname, 5);

        //Check the core
        if (substr($classname, 0, 2) == 'BE') {
            if (file_exists(BACKEND_FOLDER . '/core/' . $classname . '.obj.php')) {
                include(BACKEND_FOLDER . '/core/' . $classname . '.obj.php');
                return true;
            } else {
                throw new Exception('Missing Core Class: ' . $classname);
            }
        } else if (substr($classname, -9) == 'Exception') {
            if (file_exists(BACKEND_FOLDER . '/exceptions/' . $classname . '.obj.php')) {
                include(BACKEND_FOLDER . '/exceptions/' . $classname . '.obj.php');
                return true;
            } else {
                throw new Exception('Missing Exception Class: ' . $classname);
            }
        } else {
            foreach ($types as $type => $part) {
                switch (true) {
                case file_exists(BACKEND_FOLDER . '/' . $type . '/' . $classname . '.' . $part . '.php'):
                    include(BACKEND_FOLDER . '/' . $type . '/' . $classname . '.' . $part . '.php');
                    return true;
                    break;
                case file_exists(APP_FOLDER . '/' . $type . '/' . $classname . '.' . $part . '.php'):
                    include(APP_FOLDER . '/' . $type . '/' . $classname . '.' . $part . '.php');
                    return true;
                    break;
                }
            }
        }
        return false;
    }

    /**
     * Utility function to translate a URL part to a Controller Name
     *
     * All Controllers are plural, and ends with Controller
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
     * @todo We need to define naming standards
     */
    public static function translateModel($resource)
    {
        return class_name($resource) . 'Model';
    }

    /**
     * Utility function to translate a format to a View Name
     *
     * All Views are singular, and ends with View
     * @todo We need to define naming standards
     */
    public static function translateView($resource)
    {
        return str_replace(' ', '', humanize($resource) . 'View');
    }

    /**
     * Basic placehold function to do logging. This will be deprecated eventually.
     *
     * Log levels:
     * * 1 - Critical Messages
     * * 2 - Important Messages
     * * 3 - Debugging Messages
     * * 4 - Informative Messages
     *
     * @todo Replace this with a proper logging module
     * @param string message The message
     * @param integer level The logging level of the message
     * @param string context The context of the message
     */
    public static function log($message, $level = 3, $context = false)
    {
        if ($level > self::$_debugLevel) {
            return;
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
        switch ($level) {
        case 1:
            $message = ' (CRITICAL) ' . $message;
            break;
        case 2:
            $message = ' (IMPORTANT) ' . $message;
            break;
        case 3:
            $message = ' (DEBUG) ' . $message;
            break;
        case 4:
            $message = ' (INFORMATION) ' . $message;
            break;
        default:
            $message = ' (OTHER - ' . $level . ') ' . $message;
            break;
        }
        if ($context) {
            $message = ' [' . $context . '] ' . $message;
        }
        $message = date('Y-m-d H:i:s') . $message;
        echo $message . '<br>';
    }
}
