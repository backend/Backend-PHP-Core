<?php
/**
 * File defining Route
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
 * The Route class that uses the query string to help determine the controller,
 * action and arguments for the request.
 *
 * @category Backend
 * @package  Core
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Route
{
    /**
     * @var array An array of predefined routes
     */
    protected $routes;

    /**
     * The constructor for the class
     *
     * @param string $routesFile The path to the cpnfiguration file for the routes
     */
    public function __construct($routesFile = false)
    {
        $routesFile = $routesFile ?: PROJECT_FOLDER . 'configs/routes.' . CONFIG_EXT;
        if (!file_exists($routesFile)) {
            return false;
        }
        $routes = array();

        $ext  = pathinfo($routesFile, PATHINFO_EXTENSION);
        switch ($ext) {
        case 'json':
            $routes = json_decode(file_get_contents($routesFile), true);
            break;
        case 'yaml':
            if (function_exists('yaml_parse_file')) {
                $routes = \yaml_parse_file($routesFile);
            } else if (class_exists('\sfYamlParser')) {
                $yaml   = new \sfYamlParser();
                $routes = $yaml->parse(file_get_contents($routesFile));
            }
        }
        if (!array_key_exists('routes', $routes)) {
            $routes['routes'] = array();
        }
        if (!array_key_exists('controllers', $routes)) {
            $routes['controllers'] = array();
        }
        $this->routes = $routes;
    }

    /**
     * Add a route to the Route object
     *
     * @param string $name  The name of the route
     * @param array  $route The route information
     *
     * @return null
     */
    public function addRoute($name, $route)
    {
        $this->routes[$name] = $route;
    }

    /**
     * Get the specified route information
     *
     * @param string $name The name of the route to retrieve
     *
     * @return array The route information
     */
    public function getRoute($name)
    {
        return array_key_exists($name, $this->routes) ? $this->routes[$name] : null;
    }

    /**
     * Resolve the route for the specified Request
     *
     * @param \Backend\Core\Request $request The request to check
     *
     * @return \Backend\Core\RoutePath The matched RoutePath
     */
    public function resolve(\Backend\Core\Request $request)
    {
        //Setup and split the query
        try {
            $routePath = $this->checkDefinedRoutes($request);
            return $routePath;
        } catch (Exceptions\UnknownRouteException $exception) {
            return $this->checkGeneratedRoutes($request);
        }
    }

    /**
     * Check if we can match a Defined Route
     *
     * @param \Backend\Core\Request $request The request to check
     *
     * @return \Backend\Core\RoutePath The matched RoutePath
     */
    protected function checkDefinedRoutes($request)
    {
        foreach ($this->routes['routes'] as $routeInfo) {
            $routePath = new Utilities\RoutePath($routeInfo);
            if ($routePath->checkRequest($request)) {
                return $routePath;
            }
        }
        throw new Exceptions\UnknownRouteException($request);
    }

    /**
     * Check if we can match a Generated Route
     *
     * This method uses a basic REST to CRUD mapping, along with a simple
     * URL to Controller mapping to try and generate a route.
     *
     * @param \Backend\Core\Request $request The request to check
     *
     * @return \Backend\Core\RoutePath The matched RoutePath
     */
    protected function checkGeneratedRoutes($request)
    {
        $query    = ltrim($request->getQuery(), '/');
        if (empty($query)) {
            throw new Exceptions\UnknownRouteException($request);
        }
        $queryArr = explode('/', $query);

        //Resolve the controller
        $controller = $queryArr[0];
        if (!empty($this->routes['controllers']) &&
            array_key_exists($controller, $this->routes['controllers'])
        ) {
            $controller  = $this->routes['controllers'][$controller];
        } else {
            $controller = Utilities\Strings::className($queryArr[0]);
        }

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

        $options = array(
            'route'     => $request->getQuery(),
            'callback'  => $controller . '::' . $action,
            'arguments' => array_slice($queryArr, 1),
        );

        $routePath = new Utilities\RoutePath($options);
        if ($routePath->checkRequest($request)) {
            return $routePath;
        }
        throw new Exceptions\UnknownRouteException($request);
    }
}
