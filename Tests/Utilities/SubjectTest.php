<?php
/**
 * File defining SubjectTest
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
use \Backend\Core\Utilities\Config;
use \Backend\Core\Utilities\Logger;
use \Backend\Core\Utilities\ApplicationEvent;
use \Backend\Core\Utilities\Subject;
use \Backend\Core\Utilities\ServiceLocator;
/**
 * Class to test the \Backend\Core\Utilities\Subject class
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
     * Setup a Config and a Logger Service
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
        ServiceLocator::reset();
    }

    /**
     * Test the constructor
     *
     * @return void
     */
    public function testConstructor()
    {
        /*
        $subject = new Subject(new Config('../configs/testing.yaml'));
        $this->assertEquals(array(), $subject->getObservers());*/

        $config = new Config('../configs/testing.yaml');
        ServiceLocator::add('backend.Config', $config);
        ServiceLocator::addFromConfig($config->services);

        $subject = new ApplicationEvent('Some Message', ApplicationEvent::SEVERITY_INFORMATION);
        $this->assertContains(ServiceLocator::get('backend.PearLogger'), $subject->getObservers());
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
     * Test if the defined observers match the implemented ones
     *
     * @return void
     */
    public function testGetObservers()
    {
        $subject  = new Subject();
        $observer = $this->getMock('SplObserver', array('update'));
        $subject->attach($observer);

        $result = array_values($subject->getObservers());

        $this->assertEquals(array($observer), $result);
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
