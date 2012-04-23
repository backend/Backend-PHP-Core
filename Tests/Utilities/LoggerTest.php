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
use \Backend\Core\Utilities\ApplicationEvent;
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
    /**
     * Set up the test
     *
     * @return void
     */
    public function setUp()
    {
    }

    /**
     * Tear down the test
     *
     * @return void
     */
    public function tearDown()
    {
    }

    /**
     * Provide data for the testMessages function
     *
     * @return array
     */
    public function providerMessages()
    {
        return array(
            array(ApplicationEvent::SEVERITY_CRITICAL,    '(CRITICAL) '),
            array(ApplicationEvent::SEVERITY_WARNING,     '(WARNING) '),
            array(ApplicationEvent::SEVERITY_IMPORTANT,   '(IMPORTANT) '),
            array(ApplicationEvent::SEVERITY_DEBUG,       '(DEBUG) '),
            array(ApplicationEvent::SEVERITY_INFORMATION, '(INFORMATION) '),
        );
    }

    /**
     * Check if the correct message is generated for an event
     *
     * @param string $severity   The severity of the event
     * @param string $messageStr The message for the event
     *
     * @dataProvider providerMessages
     * @return void
     */
    public function testMessages($severity, $messageStr)
    {
        $logger = new Logger();
        ob_start();
        $message = $this->getMock('\Backend\Core\Utilities\ApplicationEvent', array('update'), array('Some Message', $severity));
        $logger->update($message);
        $result = ob_get_clean();
        $this->assertContains($messageStr, $result);
    }

    /**
     * Check for Invalid Messages
     *
     * @return void
     */
    public function testInvalidMessage()
    {
        $logger = new Logger();
        ob_start();
        $logger->update($this->getMock('SplSubject'));
        $result = ob_get_clean();
        $this->assertEmpty($result);
    }

    /**
     * Check for invalid Levels
     *
     * @return void
     */
    public function testInvalidLevel()
    {
        $logger = new Logger();
        ob_start();
        $message = $this->getMock('\Backend\Core\Utilities\ApplicationEvent', array('update'), array('Some Message', 99));
        $logger->update($message);
        $result = ob_get_clean();

        $this->assertContains(' (OTHER - 99)', $result);
    }
 
    /**
     * Test passing the Application to the Logger
     *
     * @return void
     */
    public function testApplication()
    {
        $request     = new \Backend\Core\Request('http://www.google.com', 'GET');
        $application = new \Backend\Core\Application($request);
        $application->setState('something');
        $logger = new Logger();
        ob_start();
        $logger->update($application);
        $result = ob_get_clean();

        $this->assertContains('Backend\Core\Application entered state [something]', $result);
    }
}
