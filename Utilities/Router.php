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
            return PROJECT_FOLDER . 'configs/default.yaml';
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
