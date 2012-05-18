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
    /**
     * collection 
     * 
     * @var array The collection of services
     */
    protected static $collection = array();

    /**
     * The object constructor. Will throw an exception, as this is a static only class
     * 
     * @return void
     * @throws \Backend\Core\Exceptions\BackendException
     */
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
     * @param string $id      The unique identifier for the service
     * @param mixed  $service The service, or the definition of the service
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

    /**
     * Construct a Service.
     * 
     * @param mixed $service The details of the service to construct.
     *
     * @return object The constructed service
     * @throws \Backend\Core\Exceptions\BackendException
     */
    public static function constructService($service)
    {
        if (is_object($service)) {
            return $service;
        }
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
            if (count($service[1])) {
                $reflection = new \ReflectionClass($service[0]);
                $service    = call_user_func(array($reflection, 'newInstanceArgs'), $service[1]);
            } else {
                $service = new $service[0];
            }
        } else {
            new ApplicationEvent('Undefined Service: ' . $service[0], ApplicationEvent::SEVERITY_DEBUG);
            throw new BackendException('Undefined Service: ' . $service[0]);
        }
        return $service;
    }

    /**
     * Add Services from a Config array
     * 
     * @param array $services An array containing the service details
     *
     * @return void
     */
    public static function addFromConfig(array $services)
    {
        foreach ($services as $id => $service) {
            self::add($id, $service);
        }
    }

    /**
     * Check if the ServiceLocator has the specified Service.
     * 
     * @param string $id The unique identifier of the Service.
     *
     * @return boolean If the specified Service is present.
     */
    public static function has($id)
    {
        return array_key_exists($id, self::$collection);
    }

    /**
     * Remove the specified Service.
     * 
     * @param string $id The unique identifier of the Service to remove.
     *
     * @return void
     */
    public static function remove($id)
    {
        if (self::has($id)) {
            unset(self::$collection[$id]);
        }
    }

    /**
     * Get the specified Service.
     * 
     * @param string $id The unique identifier of the Service to get.
     * 
     * @return object The requested Service
     * @throws \Backend\Core\Exceptions\BackendException
     */
    public static function get($id)
    {
        if (self::has($id)) {
            return self::$collection[$id];
        } else {
            throw new BackendException('Undefined Service: ' . $id);
        }
    }

    /**
     * Get all the services
     * 
     * @return array An array of all the services
     */
    public static function getAll()
    {
        return self::$collection;
    }

    /**
     * Reset and Empty the ServiceLocator collection
     * 
     * @return void
     */
    public static function reset()
    {
        self::$collection = array();
    }
}
