<?php
/**
 * File defining Routes
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
use Backend\Modules\Config;
use Backend\Core\Exceptions\ConfigException;
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
     * The class constructor.
     *
     * @param mixed $config The routes config or path to the routes file.
     */
    public function __construct($config = null)
    {
        $config = $config ?: $this->getFileName();
        if (!($config instanceof ConfigInterface)) {
            $config = new Config($config);
        }
        if (!($config instanceof ConfigInterface)) {
            throw new ConfigException(
                'Invalid Configuration for ' . get_class($this)
            );
        }
        $this->config = $config;
    }

    /**
     * Method to find the appropriate routes config file.
     *
     * @return string
     */
    public function getFileName()
    {
        if (file_exists(PROJECT_FOLDER . 'configs/routes.' . BACKEND_SITE_STATE . '.yaml')) {
            return PROJECT_FOLDER . 'configs/routes.' . BACKEND_SITE_STATE . '.yaml';
        } else if (file_exists(PROJECT_FOLDER . 'configs/routes.yaml')) {
            return PROJECT_FOLDER . 'configs/routes.yaml';
        } else {
            $string = 'Could not find Routes Configuration file. . Add one to '
                . PROJECT_FOLDER . 'configs';
            throw new ConfigException($string);
        }
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
        foreach($this->config->routes as $key => $route) {
            if ($this->check($request, $route)) {
                return 'something';
            }
        }
        var_dump($request); die;
    }

    protected function check(RequestInterface $request, $route)
    {
        //If the verb is defined, and it doesn't match, skip
        if (!empty($route['verb']) && $route['verb'] != $request->getMethod()) {
            return false;
        }

        //Try to match the route
        if ($route['route'] == $request->getPath()) {
            //Straight match, no arguments
            return $this->createCallback($route['callback']);
        } else if (preg_match_all('/\/<([a-zA-Z][a-zA-Z0-9_-]*)>/', $this->route, $matches)) {
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
                return $this->createCallback($route['callback'], $arguments);
            }
        }
        return false;
    }

    /**
     * Construct the callback with the given arguments
     *
     * @param array $arguments The arguments to check against
     *
     * @return array The parameters for the callback
     */
    protected function createCallback($callback, $arguments = array())
    {
        //TODO Construct the callback? Or just send back the string and arguments
        //so that the Application can construct it?
        /*if (is_array($callback)) {
            $object = $this->callback[0];
            $refMethod = new \ReflectionMethod($object, $this->callback[1]);
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
        return $parameters;*/
    }

    /**
     * Determine what request will result in the specified callback.
     *
     * @param mixed $callback Either a callback or a string representation of
     * a callback.
     *
     * @return RequestInterface
     */
    public function resolve($callback)
    {
    }
}
