<?php
/**
 * File defining Core\Decorators\ModelDecorator
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
abstract class ModelDecorator
    extends \Backend\Core\Model
    implements \Backend\Core\Interfaces\ModelInterface, \Backend\Core\Interfaces\DecoratorInterface
{
    /**
     * @var ModelInterface The model this class is decorating
     */
    protected $decoratedModel;

    /**
     * The constructor for the class
     *
     * @param ModelInterface $model The model to decorate
     */
    function __construct(\Backend\Core\Interfaces\DecorableInterface $model)
    {
        $this->decoratedModel = $model;
    }

    /**
     * Magic method to implement undefined or inherited method calls
     *
     * @param string $method The name of the method being called
     * @param array  $args   The arguments for the called method
     *
     * @return mixed The result from the called method
     */
    public function __call($method, $args)
    {
        return call_user_func_array(
            array($this->decoratedModel, $method),
            $args
        );
    }
}
