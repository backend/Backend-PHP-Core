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
use Backend\Core\Exception as CoreException;
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
     * @covers \Backend\Core\Application::__construct
     * @covers \Backend\Core\Application::init
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
     * Test the application init.
     *
     * @return void
     * @covers \Backend\Core\Application::init
     */
    public function testInitRanOnlyOnce()
    {
        $config = $this->getMockForAbstractClass(
            '\Backend\Interfaces\ConfigInterface'
        );
        $container = $this->getMockForAbstractClass(
            '\Backend\Interfaces\DependencyInjectionContainerInterface'
        );
        $application = new Application($config, $container);
        $this->assertFalse($application->init());
    }

    /**
     * Test the main function
     *
     * @return void
     * @covers Backend\Core\Application::main
     */
    public function testPlainMain()
    {
        // Setup the callback and the response
        $response  = $this->getMockForAbstractClass(
            '\Backend\Interfaces\ResponseInterface'
        );

        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );

        $callback
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($response));

        // Setup the Request and Router
        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->exactly(1))
            ->method('inspect')
            ->with($request)
            ->will($this->onConsecutiveCalls($callback));
        $this->container->set('router', $router);

        $result = $this->application->main($request);

        //Asserts
        $this->assertSame($response, $result);
        $this->assertSame($request, $this->application->getRequest());
    }

    /**
     * Test the main function with all the events
     *
     * @return void
     * @covers Backend\Core\Application::main
     */
    public function testMainWithEvents()
    {
        // Setup the Formatter and Response
        $response = $this->getMockForAbstractClass(
            '\Backend\Interfaces\ResponseInterface'
        );

        // Setup the callback
        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );

        $callback
            ->expects($this->at(0))
            ->method('execute')
            ->with()
            ->will($this->returnValue($response));

        // Setup the Request and Router
        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->exactly(1))
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue($callback));
        $this->container->set('router', $router);

        $eventDispatcher = $this->getMockForAbstractClass('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with('core.request', $this->isInstanceOf('\Backend\Core\Event\RequestEvent'));
        $eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with('core.callback', $this->isInstanceOf('\Backend\Core\Event\CallbackEvent'));
        $eventDispatcher
            ->expects($this->at(2))
            ->method('dispatch')
            ->with('core.result', $this->isInstanceOf('\Backend\Core\Event\ResultEvent'));
        $eventDispatcher
            ->expects($this->at(3))
            ->method('dispatch')
            ->with('core.response', $this->isInstanceOf('\Backend\Core\Event\ResponseEvent'));

        $this->container->set('event_dispatcher', $eventDispatcher);


        $result = $this->application->main($request);

        //Asserts
        $this->assertSame($request, $this->application->getRequest());
        $this->assertSame($response, $result);
    }

    /**
     * Test the main function with Callback Chaining
     *
     * @return void
     * @covers Backend\Core\Application::main
     */
    public function testCallbackChainingMain()
    {
        $response = $this->getMockForAbstractClass(
            '\Backend\Interfaces\ResponseInterface'
        );

        // Setup the callback
        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );

        $callback
            ->expects($this->at(0))
            ->method('execute')
            ->with()
            ->will($this->returnSelf());

        $callback
            ->expects($this->at(1))
            ->method('execute')
            ->with()
            ->will($this->returnValue($response));

        // Setup the Request and Router
        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->exactly(1))
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue($callback));
        $this->container->set('router', $router);

        $eventDispatcher = $this->getMockForAbstractClass('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with('core.request');
        $eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with('core.callback');

        $this->container->set('event_dispatcher', $eventDispatcher);


        $result = $this->application->main($request);

        //Asserts
        $this->assertSame($request, $this->application->getRequest());
        $this->assertSame($response, $result);
    }

    /**
     * Test the main function with no route for the request
     *
     * @return void
     * @covers Backend\Core\Application::main
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
        $this->container->set('router', $router);
        $result = $this->application->main($request);
    }

    /**
     * Test the Request getter and setter.
     *
     * @return void
     * @covers \Backend\Core\Application::getRequest
     * @covers \Backend\Core\Application::setRequest
     */
    public function testRequestAccessors()
    {
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $this->container->set('request', $request);
        $this->assertSame($request, $this->application->getRequest());

        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $this->application->setRequest($request);
        $this->assertSame($request, $this->application->getRequest());
    }

    /**
     * Test the Container getter and setter.
     *
     * @return void
     * @covers \Backend\Core\Application::getContainer
     * @covers \Backend\Core\Application::setContainer
     */
    public function testContainerAccessors()
    {
        $container = $this->getMock('\Backend\Interfaces\DependencyInjectionContainerInterface');
        $this->application->setContainer($container);
        $this->assertSame($container, $this->application->getContainer());
    }

    /**
     * Test the Config getter and setter.
     *
     * @return void
     * @covers \Backend\Core\Application::getConfig
     * @covers \Backend\Core\Application::setConfig
     */
    public function testConfigAccessors()
    {
        $config = $this->getMock('\Backend\Interfaces\ConfigInterface');
        $this->application->setConfig($config);
        $this->assertSame($config, $this->application->getConfig());
    }

    /**
     * Test the Router getter and setter.
     *
     * @return void
     * @covers \Backend\Core\Application::getRouter
     * @covers \Backend\Core\Application::setRouter
     */
    public function testRouterAccessors()
    {
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');

        $container = $this->getMock('\Backend\Interfaces\DependencyInjectionContainerInterface');
        $container
            ->expects($this->once())
            ->method('get')
            ->with('router')
            ->will($this->returnValue($router));
        $this->application->setContainer($container);
        $this->assertSame($router, $this->application->getRouter());

        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $this->application->setRouter($router);
        $this->assertSame($router, $this->application->getRouter());
    }

    /**
     * @return void
     * @covers \Backend\Core\Application::raiseEvent
     */
    public function testRaiseEvent()
    {
        $event = $this->getMock('Symfony\Component\EventDispatcher\Event');
        $dispatcher = $this->getMockForAbstractClass('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with('name', $event);

        $this->container->set('event_dispatcher', $dispatcher);

        $this->assertSame($this->application, $this->application->raiseEvent('name', $event));
    }

    /**
     * Run the error code.
     *
     * @return void
     * @covers \Backend\Core\Application::error
     */
    public function testError()
    {
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $result = $this->application->error(0, 'Some Error', __FILE__, __LINE__, array(), true);
        $this->assertInstanceOf('\Exception', $result);
        $this->assertEquals(500, $result->getCode());
        $this->assertEquals('Some Error', $result->getMessage());
    }

    /**
     * Run the Exception Code.
     *
     * @return void
     * @covers \Backend\Core\Application::exception
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Message
     */
    public function testException()
    {
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $result = $this->application->exception(new CoreException('Message', 500), true);
        $this->assertInstanceOf('\Backend\Interfaces\ResponseInterface', $result);
    }

    /**
     * Run the shutdown code.
     *
     * @return void
     * @covers \Backend\Core\Application::shutdown
     */
    public function testShutdown()
    {
        $dispatcher = $this->getMockForAbstractClass('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with('core.shutdown', null);

        $this->container->set('event_dispatcher', $dispatcher);

        $this->application->shutdown();
    }
}
