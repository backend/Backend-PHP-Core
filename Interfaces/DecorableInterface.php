<?php
/**
 * File defining Core\Interfaces\Decorable
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Interfaces
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Interfaces;
/**
 * Interface for all classes that are decorable
 *
 * @category   Backend
 * @package    Core
 * @subpackage Interfaces
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
interface DecorableInterface
{
    /**
     * Get an array of decorators for the class
     *
     * @return array The decorators to apply to the class
     */
    public function getDecorators();

    /**
     * Add a decorator to the class
     *
     * @param string $decorator The name of the decorator class to add
     *
     * @return null
     */
    public function addDecorator($decorator);

    /**
     * Remove a decorator from the class
     *
     * @param string $decorator The name of the decorator class to remove
     *
     * @return null
     */
    public function removeDecorator($decorator);

    /**
     * Decorate the given object
     *
     * @param Object $object The object to decorate
     *
     * @return The decorated object
     */
    public static function decorate($object);
}
