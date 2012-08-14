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
     */
    public function testDefinitions()
    {
        $config = array(
            'services' => array(
                'backend.exception' => '\Backend\Core\Exception',
                'backend.test' => array(
                    'class' => 'TestContainer',
                    'factory_class' => 'TestContainer',
                    'factory_method' => 'factory',
                    'calls' => array(
                        'addParam' => array('two'),
                    ),
                    'arguments' => array(
                        '@service_container',
                        '%name%'
                    ),
                ),
            ),
            'parameters' => array(
                'name' => 'one',
            ),
        );
        $container = new DependencyInjectionContainer($config);
        $test = $container->get('backend.test');
        //Test class
        $this->assertInstanceOf('\TestContainer', $test);
        //Test factory and arguments
        $this->assertSame($container, $test->container);
        //Test calls
        $this->assertEquals(array('one', 'two'), $test->param);
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
                'backend.test' => array()
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
     * Test getting and setting services.
     *
     * @return void
     */
    public function testAccessors()
    {
        $container = new DependencyInjectionContainer;
        $container->set('test', $this);
        $this->assertSame($this, $container->get('test'));
    }

    /**
     * Test trying to get an invalid service.
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Undefined Implementation
     */
    public function testInvalidService()
    {
        $container = new DependencyInjectionContainer;
        $container->get('test');
    }
}
