<?php
/**
 * File defining RoutePath
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Utilities;
/**
 * The RoutePath class stores and manages information about a single Route
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class RoutePath
{
    /**
     * @var string The route for this RoutePath
     */
    protected $route;

    /**
     * @var callback The RoutePath's callback
     */
    protected $callback;

    /**
     * @var string The HTTP verb for this RoutePath
     */
    protected $verb;

    /**
     * @var array Defaults for the arguments of this RoutePath
     */
    protected $defaults;

    /**
     * @var array The RoutePath's arguments
     */
    protected $arguments = array();

    /**
     * The constructor
     *
     * @param array $options The options to use to construct the RoutePath
     */
    public function __construct(array $options)
    {
        $this->route     = $options['route'];

        //Construct the Callback
        $this->callback  = $this->getCallback($options['callback']);

        $this->verb      = array_key_exists('verb', $options) ? strtoupper($options['verb']) : false;

        $this->defaults  = array_key_exists('defaults', $options) ? $options['defaults'] : array();

        $this->arguments = array_key_exists('arguments', $options) ? $options['arguments'] : array();
    }

    public function checkRequest(\Backend\Core\Request $request)
    {
        $result = $this->check($request->getMethod(), $request->getQuery());
        if (!$result) {
            return $result;
        }
        if (is_array($this->callback)) {
            $methodMessage = get_class($this->callback[0]) . '::' . $this->callback[1];
        } else {
            $methodMessage = $this->callback;
        }
        //Check the callback
        if (!is_callable($this->callback)) {
            throw new Exceptions\UncallableMethodException('Undefined method - ' . $methodMessage);
        }

        if (is_array($this->callback) && method_exists($this->callback[0], 'setRequest')) {
            //Set the request for the callback
            $this->callback[0]->setRequest($request);
        }
        return $this->callback;
    }

    /**
     * Check this RoutePath against the given verb / query for a match
     *
     * @param string $verb  The Verb to check against
     * @param string $query The query to check against
     *
     * @return mixed Return this RoutePath if there's a match, false otherwise
     */
    public function check($verb, $query)
    {
        //If the verb is defined, and it doesn't match, skip
        if ($this->verb && $verb != $this->verb) {
            return false;
        }

        //Try to match the route
        if ($this->route == $query) {
            //Straight match, no arguments
            return $this;
        } else if (preg_match_all('/\/<([a-zA-Z][a-zA-Z0-9]*)>/', $this->route, $matches)) {
            //Compile the Regex
            $varNames = $matches[1];
            $search   = $matches[0];
            $replace  = '(/([^/]*))?';
            $regex    = str_replace('/', '\/', str_replace($search, $replace, $this->route));
            if (preg_match_all('/' . $regex . '/', $query, $matches)) {
                $arguments = array();
                $index = 2;
                foreach ($varNames as $name) {
                    $arguments[$name] = $matches[$index][0];
                    $index = $index + 2;
                }
                //Regex Match
                $this->arguments = $this->constructArguments($arguments);
                return $this;
            }
        }
        return false;
    }

    /**
     * Get the RoutePath's callback
     *
     * @param string $callback The callback defined as a string
     *
     * @return callback The callback for the route path
     */
    public function getCallback($callback = null)
    {
        if ($this->callback && $callback === null) {
            return $this->callback;
        }
        $callbackArray = explode('::', $callback);
        if (count($callbackArray) == 1) {
            $this->callback = $callback[0];
        } else if (is_callable($callbackArray, true) === false) {
            throw new \Exception('Invalid Callback: ' . $callback);
        } else {
            $controllerClass = \Backend\Core\Application::resolveClass($callbackArray[0], 'controller');
            $methodName      = Strings::camelCase($callbackArray[1] . ' Action');

            if (empty($controllerClass)) {
                throw new \Backend\Core\Exceptions\UnknownControllerException('Invalid Callback: ' . $callback);
            }

            if (!class_exists($controllerClass, true)) {
                throw new \Backend\Core\Exceptions\UnknownControllerException('Unknown Controller: ' . $controllerClass);
            }
            $object = new $controllerClass();
            //Decorate the Controller
            //TODO This adds a dependancy. Rather use the DI framework to do it
            $object = \Backend\Core\Decorable::decorate($object);

            $this->callback = array(
                $object,
                $methodName
            );
        }
        return $this->callback;
    }

    /**
     * Construct the arguments for the current callback
     *
     * @param array $arguments The arguments to check against
     *
     * @return array The parameters for the callback
     */
    protected function constructArguments(array $arguments)
    {
        if (is_array($this->callback)) {
            $refMethod = new \ReflectionMethod($this->callback[0], $this->callback[1]);
        } else {
            $refMethod = new \ReflectionFunction($this->callback);
        }
        //Get the parameters in the correct order
        $parameters = array();
        foreach ($refMethod->getParameters() as $param) {
            if (!empty($arguments[$param->getName()])) {
                $parameters[] = $arguments[$param->getName()];
            } else if (isset($this->defaults[$param->getName()])) {
                $parameters[] = $this->defaults[$param->getName()];
            } else if (!$param->isOptional()) {
                throw new \Exception('Missing argument ' . $param->getName());
            }
        }
        return $parameters;
    }

    /**
     * Get the RoutePath's arguments
     *
     * @return array The arguments for the route path
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
