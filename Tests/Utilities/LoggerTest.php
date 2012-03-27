<?php
/**
 * File defining LoggerTest
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Tests\Utilities;
use \Backend\Core\Utilities\Logger;
use \Backend\Core\Utilities\LogMessage;
/**
 * Class to test the \Backend\Core\Utilities\Logger class
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function providerMessages()
    {
        return array(
            array(LogMessage::LEVEL_CRITICAL,    '(CRITICAL) '),
            array(LogMessage::LEVEL_WARNING,     '(WARNING) '),
            array(LogMessage::LEVEL_IMPORTANT,   '(IMPORTANT) '),
            array(LogMessage::LEVEL_DEBUGGING,   '(DEBUG) '),
            array(LogMessage::LEVEL_INFORMATION, '(INFORMATION) '),
        );
    }

    /**
     * Check if the correct message is generated
     *
     * @dataProvider providerMessages
     */
    public function testMessages($level, $message)
    {
        $logger = new Logger();
        ob_start();
        $message = $this->getMock('\Backend\Core\Utilities\LogMessage', array('update'), array('Some Message', $level));
        $logger->update($message);
        $result = ob_get_clean();
        $this->assertContains((string)$message, $result);
    }

    public function testInvalidMessage()
    {
        $logger = new Logger();
        ob_start();
        $logger->update($this->getMock('SplSubject'));
        $result = ob_get_clean();
        $this->assertEmpty($result);
    }

    public function testInvalidLevel()
    {
        $logger = new Logger();
        ob_start();
        $message = $this->getMock('\Backend\Core\Utilities\LogMessage', array('update'), array('Some Message', 99));
        $logger->update($message);
        $result = ob_get_clean();

        $this->assertContains(' (OTHER - 99)', $result);
    }
}
