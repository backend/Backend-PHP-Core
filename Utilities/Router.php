<?php
/**
 * File defining Backend\Core\Utilities\Routes
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
use Backend\Interfaces\ConfigInterface;
use Backend\Interfaces\CallbackFactoryInterface;
use Backend\Core\Utilities\Config;
use Backend\Core\Utilities\CallbackFactory;
use Backend\Core\Exceptions\ConfigException;
use Backend\Core\Exception as CoreException;
use Backend\Interfaces\RequestInterface;
/**
 * Class to inspect the Request to determine what callback should be executed.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class Router
{
    /**
     * An array of the defined routes.
     *
     * @var array
     */
    protected $routes;

    /**
     * The callback factory used to construct callbacks.
     *
     * @var Backend\Interfaces\CallbackFactoryInterface
     */
    protected $factory;

    /**
     * The class constructor.
     *
     * @param \Backend\Interfaces\CallbackFactoryInterface $factory A callback
     * factory used to create callbacks from strings.
     * @param Backend\Interfaces\ConfigInterface|string    $config  The routes
     * config or path to the routes file.
     */
    public function __construct(
        ConfigInterface $config, CallbackFactoryInterface $factory
    ) {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * Inspect the specified request and determine what callback to execute.
     *
     * @param RequestInterface $request The request to inspect.
     *
     * @return CallbackInterface
     */
    public function inspect(RequestInterface $request)
    {
        if ($this->config->routes) {
            foreach ($this->config->routes as $key => $route) {
                $callback = $this->check($request, $route);
                if ($callback) {
                    return $callback;
                }
            }
        }
        if ($this->config->controllers) {
            $callback = $this->checkControllers($request, $this->config->controllers);
            if ($callback) {
                return $callback;
            }
        }
        return false;
    }

    /**
     * Check the request against the supplied route. If they match, return an array
     * containing the callback and its arguments.
     *
     * @param \Backend\Interfaces\RequestInterface $request The request to compare
     * with the route.
     * @param array                                $route   The route information
     * to compare with the request.
     *
     * @return boolean|array
     */
    protected function check(RequestInterface $request, array $route)
    {
        //If the verb is defined, and it doesn't match, skip
        if (!empty($route['verb']) && $route['verb'] != $request->getMethod()) {
            return false;
        }

        $defaults = array_key_exists('defaults', $route) ? $route['defaults']
            : array();
        //Try to match the route
        if ($route['route'] == $request->getPath()) {
            //Straight match, no arguments
            $factory  = $this->getCallbackFactory();
            return $factory->fromString($route['callback'], $defaults);
        }
        $pregMatch = preg_match_all(
            '/\/<([a-zA-Z][a-zA-Z0-9_-]*)>/', $route['route'], $matches
        );
        if ($pregMatch) {
            //Compile the Regex
            $varNames = $matches[1];
            $search   = $matches[0];
            $replace  = '(/([^/]*))?';
            $regex    = str_replace(
                '/', '\/', str_replace($search, $replace, $route['route'])
            );
            if (preg_match_all('/^' . $regex . '$/', $request->getPath(), $matches)) {
                $arguments = array();
                $index = 2;
                foreach ($varNames as $name) {
                    $arguments[$name] = $matches[$index][0];
                    $index = $index + 2;
                }
                //Regex Match
                $arguments = array_merge($defaults, $arguments);
                return array($route['callback'], $arguments);
            }
        }
        return false;
    }

    /**
     * Check if the route is linked to a controller.
     *
     * @param \Backend\Interfaces\RequestInterface $request     The request to compare
     * with the route.
     * @param  array                               $controllers The controllers
     * linked to routes.
     *
     * @return boolean|array
     */
    protected function checkControllers(RequestInterface $request, array $controllers)
    {
        $queryArr = explode('/', ltrim($request->getPath(), '/'));
        if (count($queryArr) < 1) {
            return false;
        }

        //Resolve the controller
        $controller = $queryArr[0];
        if (array_key_exists($controller, $controllers) === false) {
            return false;
        }
        $controller = $controllers[$controller];

        $action = strtolower($request->getMethod());
        switch ($action) {
        case 'get':
            if (count($queryArr) == 1) {
                $action = 'list';
            } else {
                $action = 'read';
            }
            break;
        case 'post':
            $action = 'create';
            break;
        case 'put':
            $action = 'update';
            break;
        case 'delete':
            break;
        }
        $callback = $controller . '::' . $action;

        $factory  = $this->getCallbackFactory();
        return $factory->fromString($callback, array_slice($queryArr, 1));
    }

    /**
     * Determine what request will result in the specified callback.
     *
     * @param mixed $callback Either a callback or a string representation of
     * a callback.
     *
     * @return \Backend\Interfaces\RequestInterface
     */
    public function resolve($callback)
    {
        throw new CoreException('Unimplemented');
    }

    /**
     * Set the Callback Factory.
     *
     * @param \Backend\Interfaces\CallbackFactoryInterface $factory The Callback
     * Factory.
     *
     * @return \Backend\Core\Utilities\Router
     */
    public function setCallbackFactory(CallbackFactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * Get the Callback Factory.
     *
     * @return \Backend\Interfaces\CallbackFactoryInterface
     */
    public function getCallbackFactory()
    {
        $this->factory = $this->factory ?: new CallbackFactory();
        return $this->factory;
    }

    /**
     * Get the Config.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
