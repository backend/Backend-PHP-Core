<?php
/**
 * File defining ApplicationEventTest
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
use \Backend\Core\Utilities\ApplicationEvent;
/**
 * Class to test the \Backend\Core\Utilities\ApplicationEvent class
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class ApplicationEventTest extends \PHPUnit_Framework_TestCase
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
     * Test if the constructor function works correctly
     *
     * @return void
     */
    public function testConstructor()
    {
        $event = new ApplicationEvent('name', ApplicationEvent::SEVERITY_DEBUG);
        $this->assertEquals('name', $event->getName());
        $this->assertEquals(ApplicationEvent::SEVERITY_DEBUG, $event->getSeverity());
        $this->assertEquals('name [' . ApplicationEvent::SEVERITY_DEBUG . ']', (string)$event);
    }
}
