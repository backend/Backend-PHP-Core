<?php
/**
 * File defining Core\Decorators\Decorator
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Core/Decorators
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core\Decorators;
use \Backend\Core\Decorable;
use \Backend\Core\Interfaces\DecoratorInterface;
use \Backend\Core\Interfaces\DecorableInterface;
use \Backend\Core\Exceptions\UncallableMethodException;
/**
 * Class that gives basic Decorator functionality
 *
 * @category Backend
 * @package  Core/Decorators
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Decorator extends Decorable implements DecoratorInterface
{
    /**
     * @var Object The object this class is decorating
     */
    protected $object;

    /**
     * The constructor for the decorator
     *
     * @param \Backend\Core\Interfaces\DecorableInterface $decorable The object to decorate
     */
    function __construct(DecorableInterface $object)
    {
        $this->object = $object;
    }

    /**
     * The magic __call function to pass on calls to decorated object
     *
     * This is used to call the specified function on the original object
     *
     * For an example, see {@link http://stackoverflow.com/questions/3857644/php-decorator-writer-script}
     *
     * @param string $method The name of the method to call
     * @param array  $args   The arguments to pass to the method
     *
     * @return mixed The result of the called method
     */
    public function __call($method, $args)
    {
        if ($object = $this->isCallable($method)) {
            return call_user_func_array(array($object, $method), $args);
        }
        throw new UncallableMethodException(
            'Undefined method - ' . get_class($this->getOriginalObject()) . '::' . $method
        );
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
        $object = $this->getOriginalObject();
        if (property_exists($object, $property)) {
            return $object->$property;
        }
        return null;
    }

    /**
     * The magic __set function to set the properties of a decorated object
     *
     * @param string $property The name of the property to set
     * @param mixed  $value    The value of the property being set
     *
     * @return object The current object
     */
    public function __set($property, $value)
    {
        $object = $this->getOriginalObject();
        $object->$property = $value;
        return $this;
    }

    /**
     * Get the original, undecorated object
     *
     * @return mixed The original undecorated object
     */
    public function getOriginalObject()
    {
        $object = $this->object;
        while ($object instanceof \Backend\Core\Interfaces\DecoratorInterface) {
            $object = $object->getOriginalObject();
        }
        return $object;
    }

    /**
     * Check if the specified method is executable on the original object and
     * its decorators
     *
     * @param string  $method    The name of the method to check
     * @param boolean $checkSelf If the current decorator should be included in the check
     *
     * @todo Test this
     * @return object The object on which the method can be executed
     */
    public function isCallable($method, $checkSelf = false)
    {
        //Check the original object
        $object = $this->getOriginalObject();
        if (is_callable(array($object, $method))) {
            return $object;
        }
        //Check Decorators
        $object = $checkSelf ? $this : $this->object;
        while ($object instanceof \Backend\Core\Interfaces\DecoratorInterface) {
            if (is_callable(array($object, $method))) {
                return $object;
            }
            $object = $this->object;
        }
        return false;
    }
}
