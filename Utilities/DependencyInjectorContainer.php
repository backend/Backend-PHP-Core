<?php
/**
 * File defining Backend\Core\Utilities\DependencyInjectorContainer
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
use Backend\Interfaces\DependencyInjectorContainerInterface;
use Backend\Core\Exception as CoreException;
/**
 * A Dependency Injection Container.
 *
 * @category Backend
 * @package  Utilities
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class DependencyInjectorContainer implements DependencyInjectorContainerInterface
{
    /**
     * The collection of Implementated Components.
     *
     * @var array
     */
    protected $components = array();

    /**
     * Register an Implementation of a Component.
     *
     * The implementation can either be the class of the implementation, or a two
     * element array, with the first element being the class implementation, and the
     * second an array of parameters to be passed to the constructor of the
     * implementation.
     *
     * @param string $component      The unique identifier for the component
     * @param mixed  $implementation The implementation. Either pass the class of the
     * implementation, or a two element array containing the class of the
     * implementation and the parameters to be passed to the constructor of the
     * class.
     *
     * @return void
     */
    public function register($component, $implementation)
    {
        if (!is_object($implementation)) {
            $implementation = static::implement($implementation);
        }
        $component = empty($component) || is_numeric($component)
            ? get_class($implementation) : $component;
        $this->components[$component] = $implementation;
    }

    /**
     * Implement a Component
     *
     * @param mixed $implementation The details of the component to implement.
     *
     * @return object The constructed service
     * @throws \Backend\Core\Exception
     */
    public static function implement($implementation)
    {
        if (is_object($implementation)) {
            return $implementation;
        }
        if (is_string($implementation)) {
            if ($this->has($implementation)) {
                return $this->get($implementation);
            }
            $implementation = array($implementation, array());
        }
        if (!is_array($implementation) || count($implementation) != 2) {
            throw new CoreException('Incorrect Component Definition');
        }
        if (class_exists($implementation[0], true) === false) {
            throw new CoreException(
                'Undefined Implementation: ' . $implementation[0]
            );
        }
        if (count($implementation[1]) > 0) {
            try {
                $reflection = new \ReflectionClass($implementation[0]);
                $implementation = call_user_func(
                    array($reflection, 'newInstance'), $implementation[1]
                );
            } catch (\ErrorException $e) {
                //TODO Log it? Throw the exception?
                return false;
            }
        } else {
            $implementation = new $implementation[0];
        }
        return $implementation;
    }

    /**
     * Check if there is an Implementation of the specified Component.
     *
     * @param string $component The name of the Component to check for.
     *
     * @return boolean
     */
    public function has($component)
    {
        return array_key_exists($component, $this->components);
    }

    /**
     * Remove the Implementation of the specified Component.
     *
     * @param string $component The name of the Component to remove.
     *
     * @return void
     */
    public function remove($component)
    {
        if ($this->has($component)) {
            unset($this->components[$component]);
        }
    }

    /**
     * Get the Implementation of the specified Component.
     *
     * @param string $component The name of the Component to get. Will return all of
     * the implemented components when omitted.
     *
     * @return object|array
     * @throws \Backend\Core\Exception
     */
    public function get($component)
    {
        if ($component) {
            if ($this->has($component)) {
                return $this->components[$component];
            } else {
                throw new CoreException('Undefined Implementation for ' . $component);
            }
        } else {
            return $this->components;
        }
    }

    /**
     * Reset and Empty the DependencyInjectorContainer collection
     *
     * @return void
     */
    public function reset()
    {
        $this->components = array();
    }
}
