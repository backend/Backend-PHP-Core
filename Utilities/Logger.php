<?php
/**
 * File defining Logger
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
 * A Basic Logging Observer
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class Logger implements \Backend\Core\Interfaces\LoggingObserverInterface
{
    /**
     * Function to receive an update from a subject
     *
     * @param SplSubject $subject The subject to log
     *
     * @return void
     */
    public function update(\SplSubject $subject)
    {
        switch (true) {
        case $subject instanceof \Backend\Core\Application:
            $message = ' (DEBUG) ' . get_class($subject) . ' entered state [' . $subject->getState() . ']';
            break;
        case $subject instanceof ApplicationEvent:
            switch ($subject->getSeverity()) {
            case ApplicationEvent::SEVERITY_CRITICAL:
                $message = ' (CRITICAL) ' . $subject->getName();
                break;
            case ApplicationEvent::SEVERITY_WARNING:
                $message = ' (WARNING) ' . $subject->getName();
                break;
            case ApplicationEvent::SEVERITY_IMPORTANT:
                $message = ' (IMPORTANT) ' . $subject->getName();
                break;
            case ApplicationEvent::SEVERITY_DEBUG:
                $message = ' (DEBUG) ' . $subject->getName();
                break;
            case ApplicationEvent::SEVERITY_INFORMATION:
                $message = ' (INFORMATION) ' . $subject->getName();
                break;
            default:
                $message = ' (OTHER - ' . $subject->getSeverity() . ') ' . $subject->getName();
                break;
            }
            break;
        default:
            //Unknown Subject. Do Nothing
            return;
            break;
        }
        $message = date('Y-m-d H:i:s ') . $message;
        echo $message . '<br>' . PHP_EOL;
    }
}
