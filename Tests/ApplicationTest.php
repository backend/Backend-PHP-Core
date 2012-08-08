<?php
/**
 * File defining ApplicationTest
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
use Backend\Core\Application;
use Backend\Core\Request;
use Backend\Core\Utilities\Config;
use Backend\Core\Utilities\DependencyInjectionContainer;
/**
 * Class to test the \Backend\Core\Application class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    protected $request = null;

    protected $container = null;

    protected $application = null;

    /**
     * Set up the test
     *
     * Set the debugging level to 1, set a Request
     *
     * @return void
     */
    public function setUp()
    {
        $parser = new \Symfony\Component\Yaml\Parser;
        $config = new Config($parser, __DIR__ . '/auxiliary/configs/application.testing.yml');
        $this->container   = new DependencyInjectionContainer($config);
        $this->application = new Application($config, $this->container);
    }

    /**
     * Tear down the test
     *
     * @return void
     */
    public function tearDown()
    {
        $this->application = null;
        $this->container = null;
    }

    /**
     * Test the application constructor.
     *
     * @return void
     */
    public function testConstructor()
    {
        $config = $this->getMockForAbstractClass(
            '\Backend\Interfaces\ConfigInterface'
        );
        $container = $this->getMockForAbstractClass(
            '\Backend\Interfaces\DependencyInjectionContainerInterface'
        );
        $application = new Application($config, $container);
        $this->assertSame($config, $application->getConfig());
        $this->assertSame($container, $application->getContainer());
    }

    /**
     * Test the main function
     *
     * @return void
     */
    public function testMain()
    {
        //Setup
        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );
        $callback
            ->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue('\Backend\Core\Controller'));
        $callback
            ->expects($this->once())
            ->method('setObject')
            ->with($this->isInstanceOf('\Backend\Core\Controller'));
        $callback
            ->expects($this->once())
            ->method('setMethod')
            ->with($this->stringEndsWith('Action'));
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->exactly(2))
            ->method('inspect')
            ->with($request)
            ->will($this->onConsecutiveCalls($request, $callback));
        $this->container->set('backend.router', $router);
        $response  = $this->getMockForAbstractClass(
            '\Backend\Interfaces\ResponseInterface'
        );
        $formatter = $this->getMock('\Backend\Interfaces\FormatterInterface');
        $formatter
            ->expects($this->once())
            ->method('transform')
            ->with(null)
            ->will($this->returnValue($response));
        $this->application->setFormatter($formatter);
        $result = $this->application->main($request);

        //Asserts
        $this->assertSame($response, $result);
        $this->assertSame($request, $this->application->getRequest());
    }

    /**
     * Test the main function with no route for the request
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Unknown route requested
     */
    public function testMainWith404()
    {
        //Setup
        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->once())
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue(false));
        $this->container->set('backend.router', $router);
        $result = $this->application->main($request);
    }

    /**
     * Test the Formatter getter and setter.
     *
     * @return void
     */
    public function testFormatterAccessors()
    {
        $formatter = $this->getMock('\Backend\Interfaces\FormatterInterface');
        $this->application->setFormatter($formatter);
        $this->assertSame($formatter, $this->application->getFormatter());

        $this->application->setFormatter(null);
        $this->container->set('backend.formatter', $formatter);
        $this->assertSame($formatter, $this->application->getFormatter());

        $this->application->setFormatter(null);
        $this->container->set('backend.formatter', null);
    }

    /**
     * Test requesting an unknown formatter.
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Unsupported format requested
     */
    public function testUnknownFormat()
    {
        $this->application->getFormatter();
    }

    /**
     * Test the Container getter and setter.
     *
     * @return void
     */
    public function testContainerAccessors()
    {
        $container = $this->getMock('\Backend\Interfaces\DependencyInjectionContainerInterface');
        $this->application->setContainer($container);
        $this->assertSame($container, $this->application->getContainer());
    }

    /**
     * Test the Router getter and setter.
     *
     * @return void
     */
    public function testRouterAccessors()
    {
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $this->application->setRouter($router);
        $this->assertSame($router, $this->application->getRouter());
    }

    /**
     * Run the error code.
     *
     * @return void
     */
    public function testError()
    {
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $result = $this->application->error(0, 'Some Error', __FILE__, __LINE__, true);
        $this->assertInstanceOf('\Exception', $result);
        $this->assertEquals(500, $result->getCode());
        $this->assertEquals('Some Error', $result->getMessage());
    }

    /**
     * Run the Exception Code.
     *
     * @return void
     */
    public function testException()
    {
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $result = $this->application->exception(new \Exception('Message', 500), true);
        $this->assertInstanceOf('\Backend\Interfaces\ResponseInterface', $result);
    }

    /**
     * Check that the exception code is a valid HTTP status code.
     *
     * @return void
     */
    public function testExceptionCode()
    {
        $response = $this->application->exception(new \Exception('Message', 10), true);
        $this->assertEquals(500, $response->getStatusCode());
        $response = $this->application->exception(new \Exception('Message', 610), true);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Run the shutdown code.
     *
     * @return void
     */
    public function testShutdown()
    {
        $this->application->shutdown();
    }
}
