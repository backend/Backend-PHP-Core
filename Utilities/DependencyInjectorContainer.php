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
     * @param string $component      The unique identifier for the component
     * @param mixed  $implementation The class name of the component to implement.
     *
     * @return void
     */
    public function register($component, $implementation)
    {
        if (!is_object($implementation)) {
            $implementation = $this->implement($implementation);
        }
        $component = empty($component) || is_numeric($component)
            ? get_class($implementation) : $component;
        $this->components[$component] = $implementation;
    }

    /**
     * Implement a Component
     *
     * @param string $className The class name of the component to implement.
     *
     * @return object The constructed service
     * @throws \Backend\Core\Exception
     */
    public function implement($className)
    {
        if (is_object($className)) {
            return $className;
        }
        if (class_exists($className, true) === false) {
            throw new CoreException(
                'Undefined Implementatio Class: ' . $className
            );
        }
        try {
            $reflection  = new \ReflectionClass($className);
        } catch (\ErrorException $e) {
            //TODO Log it? Throw the exception?
            return false;
        }
        $parameters = self::getParameters($reflection);
        if (empty($parameters) === false) {
            $implementation = call_user_func(
                array($reflection, 'newInstanceArgs'), $parameters
            );
        } else {
            $implementation = new $className;
        }
        return $implementation;
    }

    /**
     * Get the parameters for the constructor of a class
     *
     * @param  \ReflectionClass $reflection The ReflectionClass of the class we're
     * inspecting.
     *
     * @return array
     * @todo Allow the passing of default / non service parameters
     * @todo Try to get named configs for ConfigInterface parameters
     */
    protected function getParameters(\ReflectionClass $reflection)
    {
        $constructor = $reflection->getConstructor();
        if (empty($constructor)) {
            return false;
        }
        $parameters = array();
        foreach($constructor->getParameters() as $parameter) {
            if ($parameter->isOptional()) {
                break;
            }
            $class = $parameter->getClass();
            if ($class === null) {
                //TODO We don't know what to do with non defined parameters for now
                break;
            }
            $component = $class->getName();
            if ($this->has($component) === false) {
                //TODO We don't know how to handle undefined components yet.
                break;
            }
            $parameters[] = $this->get($component);
        }
        return $parameters;
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
