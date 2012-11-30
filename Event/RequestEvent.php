<?php
/**
 * File defining \Backend\Core\Event\RequestEvent
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
use Backend\Interfaces\RequestInterface;

/**
 * The RequestEvent Event.
 *
 * This event is triggered when the Application has received the Request to handle.
 * It gives applications the opportunity to transform and check the Request before
 * it's inspected.
 *
 * @category   Backend
 * @package    Core
 * @subpackage Events
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class RequestEvent extends Event
{
    /**
     * The Request associated with the event.
     *
     * @var Backend\Interfaces\RequestInterface
     */
    private $request;

    /**
     * The object constructor.
     *
     * @param Backend\Interfaces\RequestInterface $request The request associated with the event.
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Get the current Request.
     *
     * @return Backend\Interfaces\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the Request for the Event.
     *
     * @param  Backend\Interfaces\RequestInterface $request
     * @return Backend\Core\Event\RequestEvent     The current object.
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }
}
