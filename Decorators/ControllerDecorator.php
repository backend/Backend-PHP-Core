<?php
/**
 * File defining Core\Decorators\ControllerDecorator
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
abstract class ControllerDecorator
    extends \Backend\Core\Controller
        implements \Backend\Core\Interfaces\ControllerInterface, \Backend\Core\Interfaces\DecoratorInterface
{
    /**
     * @var ControllerInterface The controller this class is decorating
     */
    protected $decoratedController;

    /**
     * The constructor for the class
     *
     * @param Decorable $controller The controller to decorate
     * @param Response  $response   The reponse for the controller
     */
    function __construct(\Backend\Core\Interfaces\DecorableInterface $controller, Response $response = null)
    {
        $this->decoratedController = $controller;
        parent::__construct($response);
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
            array($this->decoratedController, $method),
            $args
        );
    }
}
