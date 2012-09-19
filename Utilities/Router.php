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
     * @param Backend\Interfaces\ConfigInterface          $config  The routes
     * config as a Config object
     * @param Backend\Interfaces\CallbackFactoryInterface $factory A callback
     * factory used to create callbacks from strings.
     */
    public function __construct(
        ConfigInterface $config, CallbackFactoryInterface $factory
    ) {
        $this->config  = $config;
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
        if ($this->config->has('routes')) {
            foreach ($this->config->get('routes') as $key => $route) {
                $callback = $this->check($request, $route);
                if ($callback) {
                    return $callback;
                }
            }
        }
        if ($this->config->has('controllers')) {
            $callback = $this->checkControllers($request, $this->config->get('controllers'));
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
     * @param array $route The route information
     * to compare with the request.
     *
     * @return boolean|\Backend\Interfaces\CallbackInterface
     */
    protected function check(RequestInterface $request, array $route)
    {
        //If the verb is defined, and it doesn't match, skip
        if (!empty($route['verb']) && strtoupper($route['verb']) != $request->getMethod()) {
            return false;
        }

        $defaults = array_key_exists('defaults', $route) ? $route['defaults']
            : array();
        //Try to match the route
        $factory  = $this->getCallbackFactory();
        if ($route['route'] == $request->getPath()) {
            //Straight match, no arguments
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
            $regex = '/^' . $regex . '$/';
            if (preg_match_all($regex, $request->getPath(), $matches)) {
                $arguments = array_combine(
                    $varNames, array_fill(0, count($varNames), null)
                );
                // Populate the defaults
                $arguments = array_merge($arguments, $defaults);

                // Check for passed values
                $index = 2;
                foreach ($varNames as $name) {
                    if (empty($matches[$index-1][0]) === false) {
                        $arguments[$name] = $matches[$index][0];
                    }
                    $index = $index + 2;
                }
                return $factory->fromString($route['callback'], $arguments);
            }
        }

        return false;
    }

    /**
     * Check if the route is linked to a controller.
     *
     * @param \Backend\Interfaces\RequestInterface $request The request to compare
     * with the route.
     * @param array $controllers The controllers
     * linked to routes.
     *
     * @return boolean|array
     */
    protected function checkControllers(RequestInterface $request, array $controllers)
    {
        $path = ltrim($request->getPath(), '/');
        if ($path === '') {
            return false;
        }
        $queryArr = explode('/', $path);

        //Resolve the controller
        $controller = $queryArr[0];
        if (array_key_exists($controller, $controllers) === false) {
            return false;
        }
        $controller = $controllers[$controller];

        $action = strtolower($request->getMethod());
        switch ($action) {
        case 'post':
            $action = 'create';
            break;
        case 'put':
            $action = 'update';
            break;
        case 'delete':
            $action = 'delete';
            break;
        case 'get':
        default:
            if (count($queryArr) == 1) {
                $action = 'list';
            } else {
                $action = 'read';
            }
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
    public function resolve($callback, $arguments = array())
    {
        // Get the callback
        if (is_string($callback)) {
            $factory  = $this->getCallbackFactory();
            $callback = $factory->fromString($callback, $arguments);
        }
        if (($callback instanceof \Backend\Interfaces\CallbackInterface) === false) {
            throw new \RuntimeException('Invalid Callback Type');
        }
        if ($callback->getMethod() !== null && substr($callback->getMethod(), -6) === 'Action') {
            $callback->setMethod(substr($callback->getMethod(), 0, strlen($callback->getMethod()) - 6));
        }

        // Get the routes that match the callback
        if ($this->config->has('routes')) {
            foreach ($this->config->get('routes') as $key => $route) {
                if ((string)$callback === $route['callback']) {
                    return $route;
                }
            }
        }
        if ($this->config->has('controllers')) {
            foreach($this->config->get('controllers') as $key => $controller) {
                if ($controller !== $callback->getClass()) {
                    continue;
                }
                switch ($callback->getMethod()) {
                case 'create':
                case 'list':
                    return $key;
                case 'read':
                case 'update':
                case 'delete':
                    return $key . '/' . reset($arguments);
                }
            }
        }

        return false;
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
