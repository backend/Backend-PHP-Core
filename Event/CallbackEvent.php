<?php
/**
 * File defining \Backend\Core\Event\CallbackEvent
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Events
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Event;

use Symfony\Component\EventDispatcher\Event;
use Backend\Interfaces\CallbackInterface;

/**
 * The Callback Event.
 *
 * This event is triggered when the Application has determined what Callback
 * should be executed after inspecting the current Request. It gives applications
 * the opportunity to transform and check the Callback before it's executed.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Events
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class CallbackEvent extends Event
{
    /**
     * The callback associated with the event.
     *
     * @var Backend\Interfaces\CallbackInterface
     */
    private $callback;

    /**
     * The object constructor.
     *
     * @param Backend\Interfaces\CallbackInterface $callback The callback associated with the event.
     */
    public function __construct(CallbackInterface $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Get the current Callback.
     *
     * @return Backend\Interfaces\CallbackInterface
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Set the Callback for the Event.
     *
     * @param  Backend\Interfaces\CallbackInterface $callback
     * @return Backend\Core\Event\CallbackEvent     The current object.
     */
    public function setCallback(CallbackInterface $callback)
    {
        $this->callback = $callback;

        return $this;
    }
}
