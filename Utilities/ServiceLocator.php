<?php
/**
 * File defining Backend\Core\Utilities\ServiceLocator
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Utilities
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core\Utilities;
use \Backend\Core\Exceptions\BackendException;
/**
 * A Service Locator class as described in http://www.martinfowler.com/articles/injection.html
 *
 * @category Backend
 * @package  Utilities
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class ServiceLocator
{
    protected static $collection = array();

    public function __construct()
    {
        throw new BackendException('Cannot instansiate the ' . get_class($this) . ' Class');
    }

    /**
     * Add a Service
     *
     * The definition of the service can either be the name of the class of the
     * service, or a two element array, with the first element being the class
     * of the service, and the second the parameters to be passed to the
     * constructor
     * 
     * @param string $id The unique identifier for the service
     * @param mixed $service The service, or the definition of the service
     *
     * @access public
     * @return void
     */
    public static function add($id, $service)
    {
        if (!is_object($service)) {
            $service = self::constructService($service);
        }
        $id = empty($id) || is_numeric($id) ? get_class($service) : $id;
        self::$collection[$id] = $service;
    }

    public static function constructService($service)
    {
        if (is_string($service)) {
            if (self::has($service)) {
                return self::get($service);
            }
            $service = array($service, array());
        }
        if (!is_array($service) || count($service) != 2) {
            new ApplicationEvent('Incorrect Service Definition', ApplicationEvent::SEVERITY_DEBUG);
            throw new BackendException('Incorrect Service Definition');
        }
        if (class_exists($service[0], true)) {
            $reflection = new \ReflectionClass($service[0]);
            $service    = $reflection->newInstanceArgs($service[1]);
        } else {
            new ApplicationEvent('Undefined Service: ' . $service[0], ApplicationEvent::SEVERITY_DEBUG);
            throw new BackendException('Undefined Service: ' . $service[0]);
        }
        return $service;
    }

    public static function addFromConfig(array $services)
    {
        foreach ($services as $id => $service) {
            self::add($id, $service);
        }
    }

    public static function has($id)
    {
        return array_key_exists($id, self::$collection);
    }

    public static function remove($id)
    {
        if (self::has($id)) {
            unset(self::$collection[$id]);
        }
    }

    public static function get($id)
    {
        if (self::has($id)) {
            return self::$collection[$id];
        } else {
            throw new BackendException('Undefined Service: ' . $id);
        }
    }

    public static function getAll()
    {
        return self::$collection;
    }

    public static function reset()
    {
        self::$collection = array();
    }
}
