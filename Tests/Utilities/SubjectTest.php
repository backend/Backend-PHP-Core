<?php
/**
 * File defining ObservableTest
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
use \Backend\Core\Application;
use \Backend\Core\Utilities\Config;
use \Backend\Core\Utilities\Logger;
use \Backend\Core\Utilities\LogMessage;
use \Backend\Core\Utilities\Subject;
/**
 * Class to test the \Backend\Core\Utilities\Observable class
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class SubjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set up the test
     *
     * Setup a Config and a Logger Tool
     *
     * @return void
     */
    public function setUp()
    {
        Application::addTool('Config', new Config());
        Application::addTool('Logger', new Logger());
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
     * Test the constructor
     *
     * @return void
     */
    public function testConstructor()
    {
        $subject = new Subject();
        $this->assertEquals(0, count($subject->getObservers()));

        $subject = new LogMessage('Some Message', LogMessage::LEVEL_INFORMATION);
        $this->assertContains(Application::getTool('Logger'), $subject->getObservers());
    }

    /**
     * Test if the defined observers match the implemented ones
     *
     * @todo Fix the test!
     * @return void
     */
    public function testGetObservers()
    {
        $subject = new Subject();
        $config  = new Config();
        $observer = $this->getMock('SplObserver', array('update'));
        $subject->attach($observer);

        $result   = array_values($subject->getObservers());

        $this->assertEquals(array($observer), $result);
    }

    /**
     * Test the attach function
     *
     * @return void
     */
    public function testAttach()
    {
        $subject = new Subject();
        $observer = $this->getMock('SplObserver', array('update'));
        $subject->attach($observer);
        $this->assertContains($observer, $subject->getObservers());
    }

    /**
     * Test the detach function
     *
     * @return void
     */
    public function testDetach()
    {
        $subject = new Subject();
        $observer = $this->getMock('SplObserver', array('update'));
        $subject->attach($observer);
        $count = count($subject->getObservers());
        $this->assertContains($observer, $subject->getObservers());
        $subject->detach($observer);
        $this->assertEquals($count - 1, count($subject->getObservers()));
    }

    /**
     * Test the update function
     *
     * @return void
     */
    public function testUpdate()
    {
        $subject = new Subject();
        $observer = $this->getMock('SplObserver', array('update'));
        $subject->attach($observer);
        $observer->expects($this->once())
            ->method('update')
            ->with($this->equalTo($subject));
        $subject->notify();
    }
}
