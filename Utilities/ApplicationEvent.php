<?php
/**
 * File defining ApplicationEvent
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Utilities;
/**
 * ApplicationEvent
 *
 * Event Severity:
 * 1. Critical
 * 2. Warning
 * 3. Important
 * 4. Debugging
 * 5. Informative
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class ApplicationEvent extends Subject
{
    /**
     * @var int Event severity for Critical Messages
     */
    const SEVERITY_CRITICAL    = 1;

    /**
     * @var int Event severity for Warning or Alert Messages
     */
    const SEVERITY_WARNING     = 2;

    /**
     * @var int Event severity for Important Messages
     */
    const SEVERITY_IMPORTANT   = 3;

    /**
     * @var int Event severity for Debugging Messages
     */
    const SEVERITY_DEBUG       = 4;

    /**
     * @var int Event severity for Informational Messages
     */
    const SEVERITY_INFORMATION = 5;

    /**
     * @var array A set of observers for the event
     */
    protected $observers = array();

    /**
     * @var string The event severity
     */
    protected $severity;

    /**
     * @var string The event name
     */
    protected $name;

    /**
     * Constructor for the class
     *
     * @param string $name     The event name
     * @param string $severity The event severity
     */
    public function __construct($name, $severity)
    {
        $this->name = $name;

        $this->severity   = $severity;


        parent::__construct();

        $this->notify();
    }

    /**
     * Accessor for event
     *
     * @return string The name of the event
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Accessor for severity
     *
     * @return int The severity of the event
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * Return a string representation of the class
     *
     * @return string The event
     */
    function __toString()
    {
        return $this->event . ' [' . $this->severity . ']';
    }
}
