<?php
/**
 * File defining \Backend\Core\Tests\DependencyInjectionContainerTest
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
namespace Backend\Core\Utilities\Tests;
use \Backend\Core\Utilities\DependencyInjectionContainer;
require_once dirname(__FILE__) . '/../auxiliary/TestContainer.php';
/**
 * Class to test the \Backend\Core\Utilities\DependencyInjectionContainer class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class DependencyInjectionContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the constructor
     *
     * @return void
     * @covers \Backend\Core\Utilities\DependencyInjectionContainer::__construct
     */
    public function testConstructor()
    {
        $configArr = array('one' => 'two');
        $config = $this->getMock('\Backend\Interfaces\ConfigInterface');
        $config
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($configArr));
        $container = new DependencyInjectionContainer($config);

        $container = new DependencyInjectionContainer($configArr);

        $container = new DependencyInjectionContainer((object) $configArr);
    }

    /**
     * Test the adding of components through a definition.
     *
     * @return void
     * @covers \Backend\Core\Utilities\DependencyInjectionContainer::addComponent
     * @covers \Backend\Core\Utilities\DependencyInjectionContainer::resolve
     */
    public function testDefinitions()
    {
        $config = array(
            'services' => array(
                'exception' => '\Backend\Core\Exception',
                'test' => array(
                    'class' => 'TestContainer',
                    'factory_class' => 'TestContainer',
                    'factory_method' => 'factory',
                    'calls' => array(
                        'addParam' => array('call'),
                    ),
                    'arguments' => array(
                        '@service_container',
                        '%name%',
                        'BACKEND_SITE_STATE',
                        false,
                    ),
                    'tags' => array(
                        array('name' => 'tag')
                    ),
                ),
            ),
            'parameters' => array(
                'name' => 'one',
            ),
        );
        $container = new DependencyInjectionContainer($config);
        $test = $container->get('test');
        //Test class
        $this->assertInstanceOf('\TestContainer', $test);
        //Test factory and arguments
        $this->assertSame($container, $test->container);
        //Test calls
        $this->assertEquals(array('one', BACKEND_SITE_STATE, false, 'call'), $test->param);
    }

    /**
     * Test for invalid definitions.
     *
     * @return void
     * @expectedException \Backend\Core\Exceptions\ConfigException
     * @expectedExceptionMessage Invalid Service Definition
     */
    public function testInvalidDefinition()
    {
        $config = array(
            'services' => array(
                'test' => array()
            )
        );
        $container = new DependencyInjectionContainer($config);
    }

    /**
     * Test an invalid DependencyInjectionContainer config.
     *
     * @return void
     * @expectedException \Backend\Core\Exceptions\ConfigException
     * @expectedExceptionMessage Invalid DIC Configuration
     */
    public function testInvalidConfig()
    {
        new DependencyInjectionContainer(true);
    }

    /**
     * Test an invalid DependencyInjectionContainer Tag config.
     *
     * @return void
     * @expectedException \Backend\Core\Exceptions\ConfigException
     * @expectedExceptionMessage No Tag name defined
     */
    public function testInvalidTag()
    {
        $config = array(
            'services' => array(
                'test' => array(
                    'class' => '\TestContainer',
                    'tags' => array(
                        array()
                    ),
                )
            )
        );
        $container = new DependencyInjectionContainer($config);
    }

    /**
     * Test getting and setting services.
     *
     * @return void
     * @covers \Backend\Core\Utilities\DependencyInjectionContainer::get
     * @covers \Backend\Core\Utilities\DependencyInjectionContainer::set
     */
    public function testServiceAccessors()
    {
        $container = new DependencyInjectionContainer;
        $container->set('test', $this);
        $this->assertSame($this, $container->get('test'));
    }

    /**
     * Test getting and setting parameters.
     *
     * @return void
     * @covers \Backend\Core\Utilities\DependencyInjectionContainer::getParameter
     * @covers \Backend\Core\Utilities\DependencyInjectionContainer::setParameter
     * @covers \Backend\Core\Utilities\DependencyInjectionContainer::hasParameter
     */
    public function testParameterAccessors()
    {
        $container = new DependencyInjectionContainer;
        $this->assertFalse($container->hasParameter('test'));
        $container->setParameter('test', 'value');
        $this->assertEquals('value', $container->getParameter('test'));
        $this->assertTrue($container->hasParameter('test'));
    }

    /**
     * Test trying to get an invalid service.
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Undefined Implementation
     * @covers \Backend\Core\Utilities\DependencyInjectionContainer::get
     */
    public function testInvalidService()
    {
        $container = new DependencyInjectionContainer;
        $container->get('test');
    }
}
