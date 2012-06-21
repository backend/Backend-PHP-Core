<?php
/**
 * File defining Backend\Core\Utilities\Callback .
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
use Backend\Interfaces\CallbackInterface;
/**
 * Class to handle application configs.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class Callback implements CallbackInterface
{

    /**
     * The class of the callback. Used for static method calls.
     *
     * @var string
     */
    protected $class;

    /**
     * The object of the callback.
     *
     * @var object.
     */
    protected $object;

    /**
     * The name of the method to execute. Used by both class and object.
     *
     * @var string
     */
    protected $method;

    /**
     * The function to use as a callback.
     *
     * @var callable
     */
    protected $function;

    /**
     * The arguments to be used as parameters for the callback
     *
     * @array
     */
    protected $arguments = array();

    /**
     * Set the class name for a static method call.
     *
     * @param string $class The name of the class of the callback.
     *
     * @return CallbackInterface The current callback.
     */
    public function setClass($class)
    {
        if (!is_string($class)) {
            throw new \Exception(
                'Invalid type for class name, string expected, got '
                . gettype($class)
            );
        }
        if (!class_exists($class, true)) {
            throw new \Exception(
                'Trying to set non-existant class in Callback: ' . $class
            );
        }
        $this->class = $class;
        $this->function = null;
        $this->object = null;
        return $this;
    }

    /**
     * Get the class name of the static method call.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the object for a method call.
     *
     * @param object $object The object of the callback.
     *
     * @return CallbackInterface The current callback.
     */
    public function setObject($object)
    {
        if (!is_object($object)) {
            throw new \Exception(
                'Invalid type for class name, object expected, got '
                . gettype($object)
            );
        }
        $this->object = $object;
        $this->function = null;
        $this->class = null;
        return $this;
    }

    /**
     * Get the object of the method call.
     *
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set the method name for a method call.
     *
     * @param string $method The method name of the callback.
     *
     * @return CallbackInterface The current callback.
     */
    public function setMethod($method)
    {
        $this->method = $method;
        $this->function = null;
        return $this;
    }

    /**
     * Get the method name of the method call.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the function as the callback.
     *
     * @param callable $function The function.
     *
     * @return CallbackInterface The current callback.
     */
    public function setFunction($function)
    {
        if (!is_callable($function)) {
            throw new \Exception('Trying to set an uncallable function');
        }
        $this->function = $function;
        $this->method = null;
        $this->class = null;
        $this->object = null;
        return $this;
    }

    /**
     * Get the callback function.
     *
     * @return callable
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set the arguments for the callback.
     *
     * @param array $arguments The arguments for the callback.
     *
     * @return CallbackInterface The current callback.
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * Get the arguments of the callback.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Execute the callback.
     *
     * The precedence is class, object, function.
     *
     * @param array $arguments The arguments with which to execute the callback.
     *
     * @return mixed The result of the callback.
     */
    public function execute(array $arguments = array())
    {
        $arguments = $arguments ?: $this->arguments;
        $arguments = array_values($arguments);
        if ($this->method) {
            if ($this->class) {
                if (!is_callable(array($this->class, $this->method))) {
                    throw new \Exception('Invalid Callback: ' . (string)$this);
                }
                $callable = array($this->class, $this->method);
            } else if ($this->object) {
                if (!is_callable(array($this->object, $this->method))) {
                    throw new \Exception('Invalid Callback: ' . (string)$this);
                }
                switch (count($arguments)) {
                case 1:
                    return $this->object->{$this->method}($arguments[0]);
                    break;
                case 2:
                    return $this->object->{$this->method}(
                        $arguments[0], $arguments[1]
                    );
                    break;
                case 3:
                    return $this->object->{$this->method}(
                        $arguments[0], $arguments[1], $arguments[2]
                    );
                    break;
                default:
                    $callable = array($this->object, $this->method);
                    break;
                }
            }
        } else if ($this->function) {
            if (!is_callable($this->function)) {
                throw new \Exception('Invalid Callback: ' . (string)$this);
            }
            switch (count($arguments)) {
            case 1:
                return $this->function($arguments[0]);
                break;
            case 2:
                return $this->function($arguments[0], $arguments[1]);
                break;
            case 3:
                return $this->function($arguments[0], $arguments[1], $arguments[2]);
                break;
            default:
                $callable = $this->function;
                break;
            }
        }
        if (empty($callable)) {
            throw new \Exception('Call to an unexecutable Callback');
        } else {
            return call_user_func_array($callable, $arguments);
        }
    }

    /**
     * Convert the callback to a string.
     *
     * This function is the logical inverse of {@see fromString}
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->method) {
            if ($this->class) {
                return $this->class . '::' . $this->method;
            } else if ($this->object) {
                return get_class($object) . '::' . $this->method;
            }
        } else if ($this->function) {
            return $this->function;
        }
        throw new \Exception('Cannot convert invalid callback to string');
    }
}
