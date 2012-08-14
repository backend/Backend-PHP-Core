<?php
/**
 * File defining \Backend\Core\Tests\Utilities\FormatterTest
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
use \Backend\Core\Utilities\Formatter;
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '../auxiliary/TestFormat.php';
/**
 * Class to test the \Backend\Core\Utilities\Formatter class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class FormatterTest extends \PHPUnit_Framework_TestCase
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
        Formatter::setFormats(null);
    }

    /**
     * Test the constructor.
     *
     * @return void
     */
    public function testConstructor()
    {
        $request = $this->getMock('Backend\Interfaces\RequestInterface');

        $formatter = new Formatter($request);

        $this->assertSame($request, $formatter->getRequest());
    }

    /**
     * Test the transform method.
     *
     * @return void
     */
    public function testTransform()
    {
        $formatter = new Formatter();
        $response  = $formatter->transform('value');
        $this->assertInstanceOf('Backend\Interfaces\ResponseInterface', $response);
        $this->assertEquals('value', $response->getBody());
    }

    /**
     * Test the factory method.
     *
     * @return void
     */
    public function testFactory()
    {
        $request   = $this->getMock('\Backend\Interfaces\RequestInterface');
        $container = $this->getMock('\Backend\Interfaces\DependencyInjectionContainerInterface');
        $container
            ->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($request));
        $this->assertNull(Formatter::factory($container));

        Formatter::setFormats(array('\TestFormat'));

        $request = $this->getMock('Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->once())
            ->method('getSpecifiedFormat')
            ->will($this->returnValue('html'));
        $container = $this->getMock('\Backend\Interfaces\DependencyInjectionContainerInterface');
        $container
            ->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($request));
        $formatter = Formatter::factory($container);
        $this->assertNull($formatter);

        $request = $this->getMock('Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->once())
            ->method('getSpecifiedFormat')
            ->will($this->returnValue('test'));
        $container = $this->getMock('\Backend\Interfaces\DependencyInjectionContainerInterface');
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls($request, new \TestFormat));
        $formatter = Formatter::factory($container);
        $this->assertInstanceOf('Backend\Interfaces\FormatterInterface', $formatter);
    }

    /**
     * Test the Formats setter and getter.
     *
     * @return void
     */
    public function testFormatAccessors()
    {
        $formats = Formatter::getFormats();
        $this->assertTrue(is_array($formats));
        $filtered = array_filter($formats, 'class_exists');
        $this->assertEquals(count($filtered), count($formats));

        Formatter::setFormats(array('\TestFormat'));
        $this->assertEquals(array('\TestFormat'), Formatter::getFormats());
    }

    /**
     * Test the Config setter and getter.
     *
     * @return void
     */
    public function testConfigAccessors()
    {
        $config = $this->getMock('Backend\Interfaces\ConfigInterface');
        $formatter = new Formatter;
        $formatter->setConfig($config);
        $this->assertSame($config, $formatter->getConfig());
    }

    /**
     * Test the getRequestFormat method
     *
     * @return void
     */
    public function testGetRequestFormat()
    {
        $request = $this->getMock('Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->once())
            ->method('getSpecifiedFormat')
            ->will($this->returnValue('one'));
        $request
            ->expects($this->once())
            ->method('getExtension')
            ->will($this->returnValue('two'));
        $request
            ->expects($this->once())
            ->method('getMimeType')
            ->will($this->returnValue('three'));

        $actual = Formatter::getRequestFormats($request);
        $this->assertEquals(array('one', 'two', 'three'), $actual);

        $request = $this->getMock('Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->once())
            ->method('getSpecifiedFormat')
            ->will($this->returnValue('alone'));
        $request
            ->expects($this->once())
            ->method('getExtension')
            ->will($this->returnValue('alone'));
        $request
            ->expects($this->once())
            ->method('getMimeType')
            ->will($this->returnValue(false));

        $actual = Formatter::getRequestFormats($request);
        $this->assertEquals(array('alone'), $actual);
    }

    /**
     * Test the isValidFormat method
     *
     * @return void
     */
    public function testIsValidFormat()
    {
        $actual = Formatter::isValidFormat(new \TestFormat);
        $this->assertTrue($actual);

        $actual = Formatter::isValidFormat('\TestFormat');
        $this->assertTrue($actual);

        $this->assertFalse(Formatter::isValidFormat('SomeRandomClass'));

        $this->assertFalse(Formatter::isValidFormat($this));
    }

    /**
     * Test the formatClass method
     *
     * @return void
     */
    public function testFormatClass()
    {
        $original = array('Backend', 'Core', 'Formats', 'Test.php');
        $forward = VENDOR_FOLDER . implode('/', $original);
        $actual = Formatter::formatClass($forward);
        $this->assertEquals('\Backend\Core\Formats\Test', $actual);

        $back = VENDOR_FOLDER . implode('/', $original);
        $actual = Formatter::formatClass($back);
        $this->assertEquals('\Backend\Core\Formats\Test', $actual);
    }
}
