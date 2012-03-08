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
use \Backend\Core\Interfaces\DecorableInterface;
/**
 * Class that gives basic Decorator functionality
 *
 * @category Backend
 * @package  Core/Decorators
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class Decorator implements \Backend\Core\Interfaces\DecoratorInterface
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
        if (!is_callable(array($this->object, $method))) {
            throw new \Exception('Undefined method - ' . get_class($this->object) . '::' . $method);
        }
        return call_user_func_array(array($this->object, $method), $args);
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
        if (property_exists($this->object, $property)) {
            return $this->object->$property;
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
        $this->object->$property = $value;
        return $this;
    }
}
