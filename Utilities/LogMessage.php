<?php
/**
 * File defining LogMessage
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
 * LogMessage
 *
 * Log levels:
 * 1. Critical Messages
 * 2. Warning | Alert Messages
 * 3. Important Messages
 * 4. Debugging Messages
 * 5. Informative Messages
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class LogMessage extends Subject
{
    /**
     * @var int Log level for Critical Messages
     */
    const LEVEL_CRITICAL    = 1;

    /**
     * @var int Log level for Warning or Alert Messages
     */
    const LEVEL_WARNING     = 2;

    /**
     * @var int Log level for Important Messages
     */
    const LEVEL_IMPORTANT   = 3;

    /**
     * @var int Log level for Debugging Messages
     */
    const LEVEL_DEBUGGING   = 4;

    /**
     * @var int Log level for Informational Messages
     */
    const LEVEL_INFORMATION = 5;

    /**
     * @var string The log message level
     */
    protected $level;

    /**
     * @var string The log message
     */
    protected $message;

    /**
     * Constructor for the class
     *
     * @param string $message The log message
     * @param string $level   The log message level
     */
    public function __construct($message, $level)
    {
        $this->message = $message;

        $this->level   = $level;

        parent::__construct();

        $this->notify();
    }

    /**
     * Accessor for message
     *
     * @return string The contents of the message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Accessor for level
     *
     * @return int The level of the message
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Return a string representation of the class
     *
     * @return string The message
     */
    function __toString()
    {
        return $this->message;
    }
}
