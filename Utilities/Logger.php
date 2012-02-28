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
     * @param SplSubject $message The message to log
     *
     * @return string The logged message
     */
    public function update(\SplSubject $message)
    {
        if (!($message instanceof LogMessage)) {
            return false;
        }
        switch ($message->getLevel()) {
        case LogMessage::LEVEL_CRITICAL:
            $message = ' (CRITICAL) ' . $message;
            break;
        case LogMessage::LEVEL_WARNING:
            $message = ' (WARNING) ' . $message;
            break;
        case LogMessage::LEVEL_IMPORTANT:
            $message = ' (IMPORTANT) ' . $message;
            break;
        case LogMessage::LEVEL_DEBUGGING:
            $message = ' (DEBUG) ' . $message;
            break;
        case LogMessage::LEVEL_IMPORTANT:
            $message = ' (INFORMATION) ' . $message;
            break;
        default:
            $message = ' (OTHER - ' . $level . ') ' . $message;
            break;
        }

        $message = date('Y-m-d H:i:s ') . $message;
        echo $message . '<br>' . PHP_EOL;
    }
}
