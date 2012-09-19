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
use Backend\Core\Exception as CoreException;
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
     * Object constructor
     *
     * @param mixed $class The class (string) , object or function of the
     * callback. If it's a function, the second parameter should be omitted.
     * @param mixed $method The method of the callback. If given, the first
     * parameter must be either a class or an object.
     * @param array $arguments The arguments for the callback.
     */
    public function __construct($class = null, $method = null, array $arguments = array())
    {
        if ($class === null) {
            return;
        }
        if ($method !== null) {
            $this->setMethod($method);
            if (is_object($class)) {
                $this->setObject($class);
            } else {
                $this->setClass($class);
            }
        } else {
            $this->setFunction($class);
        }
        $this->setArguments($arguments);
    }

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
            throw new CoreException(
                'Invalid type for class name, string expected, got '
                . gettype($class)
            );
        }
        if ($class[0] !== '\\') {
            $class = '\\' . $class;
        }
        if (!class_exists($class, true)) {
            throw new CoreException(
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
            throw new CoreException(
                'Invalid type for object, object expected, got '
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
        if (is_string($method) === false) {
            throw new CoreException(
                'Invalid type for method, string expected, got '
                . gettype($method)
            );
        }
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
     * @return CallbackInterface       The current callback.
     * @throws \Backend\Core\Exception
     * @todo Allow for closures
     */
    public function setFunction($function)
    {
        if (is_string($function) === false) {
            throw new CoreException(
                'Invalid type for function, string expected, got '
                . gettype($function)
            );
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
        if ($this->isValid() === false) {
            throw new CoreException('Unexecutable Callback');
        }
        $arguments = $arguments ?: $this->arguments;
        $arguments = array_values($arguments);
        if ($this->method) {
            $callable = array();
            if ($this->object) {
                switch (count($arguments)) {
                case 0:
                    return $this->object->{$this->method}();
                case 1:
                    return $this->object->{$this->method}($arguments[0]);
                case 2:
                    return $this->object->{$this->method}(
                        $arguments[0], $arguments[1]
                    );
                case 3:
                    return $this->object->{$this->method}(
                        $arguments[0], $arguments[1], $arguments[2]
                    );
                default:
                    $callable[] = $this->object;
                    break;
                }
            } elseif ($this->class) {
                $callable[] = $this->class;
            }
            $callable[] = $this->method;
        } elseif ($this->function) {
            $function = $this->function;
            switch (count($arguments)) {
            case 0:
                return $function();
            case 1:
                return $function($arguments[0]);
            case 2:
                return $function($arguments[0], $arguments[1]);
            case 3:
                return $function($arguments[0], $arguments[1], $arguments[2]);
            default:
                $callable = $this->function;
                break;
            }
        }

        return call_user_func_array($callable, $arguments);
    }

    /**
     * Check if it is a valid callback.
     *
     * @return string The string representation of callback. Will return false
     * if all the callback components aren't set.
     * @throws \Backend\Core\Exception When the set callback is incomplete.
     */
    public function isValid()
    {
        $callable = array();
        if ($this->method) {
            if ($this->class) {
                $callable[] = $this->class;
            } elseif ($this->object) {
                $callable[] = $this->object;
            } else {
                return false;
            }
            $callable[] = $this->method;
        } elseif ($this->function) {
            $callable = $this->function;
        } else {
            return false;
        }
        if (is_callable($callable, false, $callableName)) {
            return $callableName;
        }
        if (is_callable($callable, true, $callableName)) {
            throw new CoreException('Unexecutable Callback: ' . $callableName);
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
        try {
            $string = $this->isValid();
        } catch (CoreException $e) {
        }
        if (empty($string) === false) {
            return $string;
        }
        if ($this->method) {
            if ($this->class) {
                return $this->class . '::' . $this->method;
            } elseif ($this->object) {
                return get_class($this->object) . '::' . $this->method;
            } else {
                return '(null)::' . $this->method;
            }
            $callable = $this->method;
        } elseif ($this->function) {
            return $this->function;
        }

        return '(Invalid Callback)';
    }
}
