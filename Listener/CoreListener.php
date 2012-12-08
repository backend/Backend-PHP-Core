<?php
/**
 * File defining \Backend\Core\Listener\CoreListener
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Listeners
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Listener;

use Backend\Core\Exception as CoreException;
use Backend\Interfaces\DependencyInjectionContainerInterface;
use Backend\Interfaces\CallbackInterface;

/**
 * The Core Listener.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Listeners
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class CoreListener
{
    /**
     * The DI Container for the Listener
     *
     * @var \Backend\Interfaces\DependencyInjectionContainerInterface
     */
    private $container;

    /**
     * The object constructor.
     *
     * @param \Backend\Interfaces\DependencyInjectionContainerInterface $container
     * The DI Container.
     */
    public function __construct(DependencyInjectionContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Method to handle core.callback Events.
     *
     * It applies a couple of transforms on the object, ensuring consistency.
     *
     * @param  \Backend\Core\Event\CallbackEvent $event The event to handle
     * @return void
     */
    public function coreCallbackEvent(\Backend\Core\Event\CallbackEvent $event)
    {
        $callback = $event->getCallback();
        $callback = $this->transformCallback($callback);
        $this->container->set('callback', $callback);
        $event->setCallback($callback);
    }

    /**
     * Method to handle core.exception Events.
     *
     * It will try to format the exception as a response.
     *
     * @param  \Backend\Core\Event\CallbackEvent $event The event to handle
     * @return void
     */
    public function coreExceptionEvent(\Backend\Core\Event\ExceptionEvent $event)
    {
        $response  = $event->getResponse();
        if (empty($response)) {
            $exception = $event->getException();
            $code = $exception->getCode();
            if ($code < 100 || $code > 599) {
                $code = 500;
            }
            $responseClass = $this->container->getParameter('response.class');
            $response = new $responseClass(
                $exception->getMessage(),
                $code
            );
        }
        $event->setResponse($response);
    }

    /**
     * Transform the Callback.
     *
     * Transform any ControllerInterface classes into objects. Add Action to
     * ControllerInterface methods.
     *
     * @param  \Backend\Interfaces\CallbackInterface $callback The callback to transform.
     * @return \Backend\Interfaces\CallbackInterface The transformed callback.
     */
    private function transformCallback(CallbackInterface $callback)
    {
        //Transform the callback a bit if it's a controller
        $class = $callback->getClass();
        if ($class) {
            // Check for a ControllerInterface, and adjust accordingly
            $interfaces = class_implements($class);
            $implements = array_key_exists(
                'Backend\Interfaces\ControllerInterface',
                $interfaces
            );
            if ($implements === true) {
                $controller = new $class(
                    $this->container,
                    $this->container->get('request')
                );
                $callback->setObject($controller);
                //Set the method name as actionAction
                if (substr($callback->getMethod(), -6) !== 'Action') {
                    $callback->setMethod($callback->getMethod() . 'Action');
                }
            }
        }

        return $callback;
    }

    /**
     * Get the DI Container.
     *
     * @return \Backend\Interfaces\DependencyInjectionContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
