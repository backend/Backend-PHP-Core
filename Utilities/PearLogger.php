<?php
/**
 * File defining PearLogger
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
require_once 'Log.php';
/**
 * A Logging Observer using the PEAR::Log class
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class PearLogger implements \Backend\Core\Interfaces\LoggingObserverInterface
{
    /**
     * @var Log The instance of the PEAR Log class we'll use to log
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param mixed $options An array of options for the logger, or a string containing a filename to log to
     */
    public function __construct($options = array())
    {
        if (is_string($options)) {
            $options = array('filename' => $options);
        }
        if (array_key_exists('filename', $options)) {
            if (!array_key_exists('prepend', $options)) {
                $options['prepend'] = 'BackendCore';
            }
            $this->logger = \Log::factory('file', $options['filename'], $options['prepend']);
        }
    }

    /**
     * Update method called by subjects being observed
     *
     * @param SplSubject $subject The subject to log
     *
     * @return void
     */
    public function update(\SplSubject $subject)
    {
        if (!$this->logger) {
            return false;
        }
        switch (true) {
        case $subject instanceof \Backend\Core\Application:
            $message = get_class($subject) . ' entered state [' . $subject->getState() . ']';
            $level   = \PEAR_LOG_DEBUG;
            break;
        case $subject instanceof ApplicationEvent:
            switch ($subject->getSeverity()) {
            case ApplicationEvent::SEVERITY_CRITICAL:
                $level = \PEAR_LOG_EMERG;
                break;
            case ApplicationEvent::SEVERITY_WARNING:
                $level = \PEAR_LOG_CRIT;
                break;
            case ApplicationEvent::SEVERITY_IMPORTANT:
                $level = \PEAR_LOG_WARNING;
                break;
            case ApplicationEvent::SEVERITY_DEBUG:
                $level = \PEAR_LOG_DEBUG;
                break;
            case ApplicationEvent::SEVERITY_INFORMATION:
                $level = \PEAR_LOG_INFO;
                break;
            default:
                $level = $message->getSeverity();
                break;
            }
            $message = $subject->getName();
            break;
        default:
            //Unknown Subject. Do Nothing
            return;
            break;
        }
        $this->logger->log($message, $level);
    }

    /**
     * Get the current logger instance
     *
     * @return Log The current PEAR Log instance
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
