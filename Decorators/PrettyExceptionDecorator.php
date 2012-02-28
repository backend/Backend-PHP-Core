<?php
/**
 * File defining Core\Decorators\PrettyExceptionDecorator
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Decorators
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Decorators;
/**
 * Abstract base class for Model decorators
 *
 * @category   Backend
 * @package    Core
 * @subpackage Decorators
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class PrettyExceptionDecorator extends \Exception
{
    protected $exception;

    /**
     * The constructor for the class
     *
     * @param Exception $exception The exception to decorate
     * @param string    $message   The exception message
     * @param integer   $code      The exception code
     */
    function __construct(\Exception $exception, $message = null, $code = 0)
    {
        $this->exception = $exception;
        parent::__construct($message, $code);
    }

    /**
     * The magic __call function.
     *
     * This is used to call the specified function on the original object
     *
     * @param string $method The name of the method to call
     * @param array  $args   The arguments to pass to the method
     *
     * @return mixed The result of the called method
     */
    public function __call($method, $args)
    {
        if (method_exists($this->exception, $method)) {
            return call_user_func_array(
                array($this->exception, $method),
                $args
            );
        }
        return null;
    }

    /**
     * The magic __get function to retrieve properties from decorated object
     *
     * @param string $property The name of the property to retrieve
     *
     * @return mixed The value of the property
     */
    public function __get($property)
    {
        if (property_exists($this->exception, $property)) {
            return $this->exception->$property;
        }
        return null;
    }

    /**
     * The magic __get function to retrieve properties from decorated object
     *
     * @param string $property The name of the property to set
     * @param mixed  $value    The value of the property being set
     *
     * @return object The current object
     */
    public function __set($property, $value)
    {
        $this->exception->$property = $value;
        return $this;
    }

    /**
     * Return the exception as a string
     *
     * @return string The exception as a string
     * @todo Use the kohana code to format the exception properly.
     * * /kohana/system/classes/kohana/kohana/exception.php
     * * /kohana/system/views/kohana/error.php
     */
    public function __toString()
    {
        return sprintf(
            '%s [ %s ]: %s' . PHP_EOL . '%s [ %d ]',
            get_class($this->exception),
            $this->exception->getCode(),
            strip_tags($this->exception->getMessage()),
            $this->exception->getFile(),
            $this->exception->getLine()
        );
    }
}
