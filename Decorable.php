<?php
/**
 * File defining Core\Decorable
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Core
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core;
use Backend\Core\Interfaces\DecorableInterface;
/**
 * Base class for all classes that are decorable
 *
 * @category Backend
 * @package  Core
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
abstract class Decorable implements DecorableInterface
{
    /**
     * @var array An array of names of decorators to apply to the object
     */
    private $_decorators = array();

    /**
     * Decorate the given object
     *
     * @param Object $object The object to decorate
     *
     * @return The decorated Object
     */
    public static function decorate($object)
    {
        if (!($object instanceof Interfaces\DecorableInterface)) {
            return $object;
        }
        foreach ($object->getDecorators() as $decorator) {
            $object = new $decorator($object);
            if (!($object instanceof \Backend\Core\Interfaces\DecoratorInterface)) {
                throw new \Exception(
                    'Class ' . $decorator . ' is not an instance of \Backend\Core\Interfaces\DecoratorInterface'
                );
            }
        }
        return $object;
    }

    /**
     * Get an array of decorators for the object
     *
     * @return array The decorators to apply to the object
     */
    public function getDecorators()
    {
        return $this->_decorators;
    }

    /**
     * Add a decorator to the object
     *
     * @param string $decorator The name of the decorator object to add
     *
     * @return null
     */
    public function addDecorator($decorator)
    {
        $this->_decorators[] = $decorator;
    }

    /**
     * Remove a decorator from the object
     *
     * @param string $decorator The name of the decorator object to remove
     *
     * @return null
     */
    public function removeDecorator($decorator)
    {
        $key = array_search($decorator, $this->_decorators);
        if ($key !== false) {
            unset($this->_decorators[$key]);
        }
    }
}
