<?php
/**
 * File defining ServiceLocatorTest
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   CoreTests
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core\Tests;
use \Backend\Core\Utilities\ServiceLocator as SL;
use \Backend\Core\Utilities\Logger;
/**
 * Class to test the \Backend\Core\Utilities\ServiceLocator class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class ServiceLocatorTest extends \PHPUnit_Framework_TestCase
{
    protected $request = null;

    /**
     * Set up the test
     *
     * @return void
     */
    public function setUp()
    {
        SL::reset();
    }

    /**
     * Tear down the test
     *
     * @return void
     */
    public function tearDown()
    {
        \Backend\Core\Utilities\ServiceLocator::reset();
    }

    /**
     * Test the constructor
     *
     * @expectedException Backend\Core\Exceptions\BackendException
     * @expectedExceptionMessage Cannot instansiate the Backend\Core\Utilities\ServiceLocator Class
     * @return void
     */
    public function testConstructor()
    {
        $serviceLocator = new SL();
    }

    /**
     * Test adding a Service
     *
     * @return void
     */
    public function testAddService()
    {
        $service = SL::constructService('\Backend\Core\Utilities\Logger');
        SL::add('Service', $service);
        $this->assertContains($service, SL::getAll());
    }

    /**
     * Test Adding a Service Using the class name
     * 
     * @access public
     * @return void
     */
    public function testAddServiceFromString()
    {
        SL::add('Logger', '\Backend\Core\Utilities\Logger');
        $this->assertInstanceOf('\Backend\Core\Utilities\Logger', SL::get('Logger'));
    }

    /**
     * Test Adding an Existing Service using an Alias
     * 
     * @access public
     * @return void
     */
    public function testAddExistingServiceAlias()
    {
        SL::add('Logger', '\Backend\Core\Utilities\Logger');
        $service = SL::constructService('Logger');
        $this->assertInstanceOf('\Backend\Core\Utilities\Logger', $service);
    }

    /**
     * Test an incorrect Service definition
     *
     * @expectedException Backend\Core\Exceptions\BackendException
     * @expectedExceptionMessage Incorrect Service Definition
     * @return void
     */
    public function testIncorrectServiceDefinitionArray()
    {
        SL::constructService(array('one', 'two', 'three'));
    }

    /**
     * Test an incorrect Service definition
     *
     * @expectedException Backend\Core\Exceptions\BackendException
     * @expectedExceptionMessage Incorrect Service Definition
     * @return void
     */
    public function testIncorrectServiceDefinitionNonArray()
    {
        SL::constructService(false);
    }

    /**
     * Test adding an undefined Service
     *
     * @expectedException Backend\Core\Exceptions\BackendException
     * @expectedExceptionMessage Undefined Service: UndefinedClass
     * @return void
     */
    public function testUndefinedService()
    {
        SL::constructService('UndefinedClass');
    }

    /**
     * Test Adding services From a Config 
     * 
     * @return void
     */
    public function testAddFromConfig()
    {
        $services = array(
            0 => '\Backend\Core\Utilities\Logger',
            'someService' => '\Backend\Core\Utilities\Logger',
        );
        SL::addFromConfig($services);
        $this->assertArrayHasKey('Backend\Core\Utilities\Logger', SL::getAll());
        $this->assertArrayHasKey('someService', SL::getAll());
    }

    /**
     * Test removing a Service
     *
     * @return void
     */
    public function testRemoveService()
    {
        $logger = new Logger();
        SL::add('Logger', $logger);
        SL::remove('Logger');
        $this->assertNotContains($logger, SL::getAll());
    }

    /**
     * Test checking a Service
     * 
     * @return void
     */
    public function testHasService()
    {
        $logger = new Logger();
        SL::add('Logger', $logger);
        $this->assertTrue(SL::has('Logger'));
        $this->assertFalse(SL::has('Something'));
    }

    /**
     * Test getting a Service
     *
     * @return void
     */
    public function testGetService()
    {
        $logger = new Logger();
        SL::add('Logger', $logger);
        $this->assertEquals($logger, SL::get('Logger'));
    }

    /**
     * Test getting an undefined Service
     *
     * @expectedException Backend\Core\Exceptions\BackendException
     * @expectedExceptionMessage Undefined Service: Something
     * @return void
     */
    public function testGetUndefinedService()
    {
        SL::get('Something');
    }
}
