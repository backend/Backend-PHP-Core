<?php
/**
 * File defining \Backend\Core\Tests\Utilities\UrlGeneratorTest
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
namespace Backend\Core\Tests\Utilities;
use Backend\Core\Utilities\UrlGenerator;
/**
 * Class to test the \Backend\Core\Utilities\UrlGenerator class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class UrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the class constructor and default values.
     *
     * @return void
     */
    public function testConstructor()
    {
        $context = $this->getMockForAbstractClass('\Backend\Interfaces\RequestContextInterface');
        $config  = $this->getMockForAbstractClass('\Backend\Interfaces\ConfigInterface');

        $generator = new UrlGenerator($context, $config);
        $this->assertSame($context, $generator->getContext());
        $this->assertSame($config, $generator->getConfig());
    }

    /**
     * Test the generator method for routes.
     *
     * @return void
     */
    public function testGeneratorForRoute()
    {
        $context = $this->getMockForAbstractClass('\Backend\Interfaces\RequestContextInterface');
        $context
            ->expects($this->once())
            ->method('getLink')
            ->will($this->returnValue('http://backend-php.net'));

        $routes = array(
            'home' => array(
                'route' => '/home/<id>'
            )
        );
        $config  = $this->getMockForAbstractClass('\Backend\Interfaces\ConfigInterface');
        $config
            ->expects($this->at(0))
            ->method('get')
            ->with('routes')
            ->will($this->returnValue($routes));
        $config
            ->expects($this->at(1))
            ->method('get')
            ->with('controllers')
            ->will($this->returnValue(null));

        $generator = new UrlGenerator($context, $config);

        $this->assertEquals('http://backend-php.net/home/1', $generator->generate('home', array('id' => 1)));
    }

    /**
     * Test the generator method for controllers.
     *
     * @return void
     */
    public function testGeneratorForControllers()
    {
        $context = $this->getMockForAbstractClass('\Backend\Interfaces\RequestContextInterface');
        $context
            ->expects($this->once())
            ->method('getLink')
            ->will($this->returnValue('http://backend-php.net'));

        $controllers = array(
            'value' => 'ValueCallback',
        );
        $config = $this->getMockForAbstractClass('\Backend\Interfaces\ConfigInterface');
        $config
            ->expects($this->at(0))
            ->method('get')
            ->with('routes')
            ->will($this->returnValue(null));
        $config
            ->expects($this->at(1))
            ->method('get')
            ->with('controllers')
            ->will($this->returnValue($controllers));

        $generator = new UrlGenerator($context, $config);

        $this->assertEquals('http://backend-php.net/value', $generator->generate('value'));
    }

    /**
     * Test if an exception is thrown for unknown routes.
     *
     * @return void
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Undefined Route
     */
    public function testInvalidRoute()
    {
        $context = $this->getMockForAbstractClass('\Backend\Interfaces\RequestContextInterface');
        $config  = $this->getMockForAbstractClass('\Backend\Interfaces\ConfigInterface');

        $generator = new UrlGenerator($context, $config);
        $generator->generate('test');
    }

    /**
     * Test the context getter and setter
     *
     * @return void
     */
    public function testContextAccessors()
    {
        $context = $this->getMockForAbstractClass('\Backend\Interfaces\RequestContextInterface');
        $config  = $this->getMockForAbstractClass('\Backend\Interfaces\ConfigInterface');

        $generator = new UrlGenerator($context, $config);

        $context  = $this->getMockForAbstractClass('\Backend\Interfaces\RequestContextInterface');
        $this->assertSame($generator, $generator->setContext($context));
        $this->assertSame($context, $generator->getContext());
    }

    /**
     * Test the config getter and setter
     *
     * @return void
     */
    public function testConfigAccessors()
    {
        $context = $this->getMockForAbstractClass('\Backend\Interfaces\RequestContextInterface');
        $config  = $this->getMockForAbstractClass('\Backend\Interfaces\ConfigInterface');

        $generator = new UrlGenerator($context, $config);

        $config  = $this->getMockForAbstractClass('\Backend\Interfaces\ConfigInterface');
        $this->assertSame($generator, $generator->setConfig($config));
        $this->assertSame($config, $generator->getConfig());
    }
}
