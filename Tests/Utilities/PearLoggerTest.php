<?php
/**
 * File defining PearLoggerTest
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
use \Backend\Core\Utilities\PearLogger;
use \Backend\Core\Utilities\ApplicationEvent;
include_once 'Log.php';
/**
 * Class to test the \Backend\Core\Utilities\PearLogger class
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class PearLoggerTest extends \PHPUnit_Framework_TestCase
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

    public function testContructor()
    {
        $filename = '/tmp/test-backend.log';
        $logger   = new PearLogger($filename);
        $this->assertInstanceOf("Log_file", $logger->getLogger());
        $logger->update(new ApplicationEvent('Some Message', ApplicationEvent::SEVERITY_INFORMATION));
        $this->assertFileExists($filename);
    }

    /**
     * Provide data for the testMessages function
     *
     * @return array
     */
    public function providerMessages()
    {
        return array(
            array(ApplicationEvent::SEVERITY_CRITICAL,    \PEAR_LOG_EMERG),
            array(ApplicationEvent::SEVERITY_WARNING,     \PEAR_LOG_CRIT),
            array(ApplicationEvent::SEVERITY_IMPORTANT,   \PEAR_LOG_WARNING),
            array(ApplicationEvent::SEVERITY_DEBUG,       \PEAR_LOG_DEBUG),
            array(ApplicationEvent::SEVERITY_INFORMATION, \PEAR_LOG_INFO),
        );
    }

    /**
     * Check if the correct message is generated
     *
     * @return void
     * @dataProvider providerMessages
     */
    public function testMessages($severity, $pearLevel)
    {
        $pearLogger = $this->getMock('Log_file', array('log'), array(), '', false);
        $pearLogger->expects($this->once())
            ->method('log')
            ->with($this->equalTo('Some Message'), $this->equalTo($pearLevel));
        $options    = array(
            'logger'   => $pearLogger,
            'filename' => '/tmp/test-backend.log',
        );
        $logger = new PearLogger($options);
        $logger->update(new ApplicationEvent('Some Message', $severity));
    }

    public function testUndefinedSeverity()
    {
        $pearLogger = $this->getMock('Log_file', array('log'), array(), '', false);
        $pearLogger->expects($this->once())
            ->method('log')
            ->with($this->equalTo('Some Message'), $this->equalTo(999));
        $options    = array(
            'logger'   => $pearLogger,
            'filename' => '/tmp/test-backend.log',
        );
        $logger = new PearLogger($options);
        $logger->update(new ApplicationEvent('Some Message', 999));
    }

    public function testNonMessageObject()
    {
        $pearLogger = $this->getMock('Log_file', array('log'), array(), '', false);
        $options    = array(
            'logger'   => $pearLogger,
            'filename' => '/tmp/test-backend.log',
        );
        $logger = new PearLogger($options);
        $this->assertFalse($logger->update(new \Backend\Core\Utilities\Subject()));
    }

    public function testApplication()
    {
        $pearLogger = $this->getMock('Log_file', array('log'), array(), '', false);
        $pearLogger->expects($this->once())
            ->method('log')
            ->with($this->equalTo('Backend\Core\Application entered state [something]'), $this->equalTo(\PEAR_LOG_DEBUG));
        $options    = array(
            'logger'   => $pearLogger,
            'filename' => '/tmp/test-backend.log',
        );
        $logger      = new PearLogger($options);
        $request     = new \Backend\Core\Request('http://www.google.com', 'GET');
        $application = new \Backend\Core\Application($request);
        $application->setState('something');
        $logger->update($application);
    }
}
