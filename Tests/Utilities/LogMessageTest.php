<?php
/**
 * File defining LogMessageTest
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
use \Backend\Core\Utilities\LogMessage;
/**
 * Class to test the \Backend\Core\Utilities\LogMessage class
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class LogMessageTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $message = $this->getMock(
            '\Backend\Core\Utilities\LogMessage',
            array('notify'),
            array(),
            '',
            false
        );

        $message->expects($this->once())->method('notify');
        $message->__construct('Some Message', LogMessage::LEVEL_CRITICAL);

        $this->assertEquals('Some Message', $message->getMessage());

        $this->assertEquals(LogMessage::LEVEL_CRITICAL, $message->getLevel());
    }

    /**
     * Test the __toString for the Subject
     */
    public function testToString()
    {
        $message = new LogMessage('Some Message', LogMessage::LEVEL_CRITICAL);
        $result  = (string)$message;
        $this->assertEquals('Some Message', $result);
    }
}
