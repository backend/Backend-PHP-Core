<?php
/**
 * File defining Core\Interfaces\Decorator
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
 * Interface for all classes that are decorators
 *
 * @category   Backend
 * @package    Core
 * @subpackage Interfaces
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
interface DecoratorInterface
{
    /**
     * The constructor for the decorator
     *
     * @param ModelInterface $decorable The model to decorate
     */
    function __construct(\Backend\Core\Interfaces\DecorableInterface $decorable);

    /**
     * Function call to catch methods for the decorated instance
     *
     * For an example, see {@link http://stackoverflow.com/questions/3857644/php-decorator-writer-script}
     *
     * @param string $method The method being called
     * @param array  $args   An array of arguments for the method being called
     *
     * @return mixed The result of the method called by the decorated instance
     */
    public function __call($method, $args);
}
