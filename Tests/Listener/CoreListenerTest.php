<?php
namespace Backend\Core\Tests\Listener;
use Backend\Core\Listener\CoreListener;
use Backend\Core\Utilities\Config;
use Backend\Core\Utilities\DependencyInjectionContainer;

class CoreListenerTest extends \PHPUnit_Framework_TestCase

{
    protected $container = null;

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
        $config = new Config($parser, __DIR__ . '/../auxiliary/configs/application.testing.yml');
        $this->container = new DependencyInjectionContainer($config);
    }

    /**
     * @covers Backend\Core\Listener\CoreListener::__construct
     * @covers Backend\Core\Listener\CoreListener::getContainer
     */
    public function testConstructor()
    {
        $transformer = new CoreListener($this->container);
        $this->assertSame($this->container, $transformer->getContainer());
    }

    /**
     * @covers Backend\Core\Listener\CoreListener::coreInitEvent
     * @return void
     */
    public function testHandleInitEvent()
    {
        $event = $this->getMock('Symfony\Component\EventDispatcher\Event');
        $event
            ->expects($this->never())
            ->method('stopPropagation');
        $transformer = new CoreListener($this->container);
        $transformer->coreInitEvent($event);
    }

    /**
     * @covers Backend\Core\Listener\CoreListener::coreResultEvent
     * @return void
     */
    public function testResultEventWithoutFormatting()
    {
        $event = $this->getMock(
            'Backend\Core\Event\ResultEvent',
            null,
            array(true)
        );
        $event
            ->expects($this->never())
            ->method('stopPropagation');

        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $this->container->set('request', $request);

        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->once())
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue(false));
        $this->container->set('router', $router);

        $transformer = new CoreListener($this->container);
        $transformer->coreResultEvent($event);

        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );

        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->once())
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue($callback));
        $this->container->set('router', $router);

        $transformer = new CoreListener($this->container);
        $transformer->coreResultEvent($event);
    }

    /**
     * @return void
     * @covers Backend\Core\Listener\CoreListener::coreResultEvent
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Unsupported format requested
     */
    public function testUnsupportedFormatResultEvent()
    {
        $event = $this->getMock(
            'Backend\Core\Event\ResultEvent',
            null,
            array(true)
        );
        $event
            ->expects($this->never())
            ->method('stopPropagation');

        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $this->container->set('request', $request);

        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );
        $callback
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('actionFormat'));

        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->once())
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue($callback));
        $this->container->set('router', $router);

        $transformer = new CoreListener($this->container);
        $transformer->coreResultEvent($event);
    }

    /**
     * @return void
     * @covers Backend\Core\Listener\CoreListener::coreResultEvent
     */
    public function testUnsupportedFormatWithResponseResultEvent()
    {
        $result = $this->getMockForAbstractClass('\Backend\Interfaces\ResponseInterface');

        $event = $this->getMock(
            'Backend\Core\Event\ResultEvent',
            null,
            array($result)
        );
        $event
            ->expects($this->never())
            ->method('stopPropagation');

        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $this->container->set('request', $request);

        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );
        $callback
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('actionFormat'));

        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->once())
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue($callback));
        $this->container->set('router', $router);

        $transformer = new CoreListener($this->container);
        $transformer->coreResultEvent($event);

        $this->assertSame($result, $event->getResponse());
    }

    /**
     * @return void
     * @covers Backend\Core\Listener\CoreListener::coreResultEvent
     */
    public function testResultEventWithFormatting()
    {
        $result = new \stdClass;

        $event = $this->getMock(
            'Backend\Core\Event\ResultEvent',
            null,
            array($result)
        );
        $event
            ->expects($this->never())
            ->method('stopPropagation');

        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $this->container->set('request', $request);

        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );
        $callback
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('actionFormat'));
        $callback
            ->expects($this->once())
            ->method('execute')
            ->with(array($result))
            ->will($this->returnValue($result));

        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->once())
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue($callback));
        $this->container->set('router', $router);

        $response = $this->getMockForAbstractClass('\Backend\Interfaces\ResponseInterface');

        $formatter = $this->getMockForAbstractClass('\Backend\Interfaces\FormatterInterface');
        $formatter
            ->expects($this->once())
            ->method('transform')
            ->with($result)
            ->will($this->returnValue($response));

        $this->container->set('formatter', $formatter);

        $transformer = new CoreListener($this->container);
        $transformer->coreResultEvent($event);

        $this->assertSame($response, $event->getResponse());
    }

    /**
     * @return void
     * @covers Backend\Core\Listener\CoreListener::coreResultEvent
     */
    public function testAddsBufferResultEvent()
    {
        $result = new \stdClass;

        $event = $this->getMock(
            'Backend\Core\Event\ResultEvent',
            null,
            array($result)
        );
        $event
            ->expects($this->never())
            ->method('stopPropagation');

        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $this->container->set('request', $request);

        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );
        $callback
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('actionFormat'));
        $callback
            ->expects($this->once())
            ->method('execute')
            ->with(array($result))
            ->will($this->returnValue($result));

        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->once())
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue($callback));
        $this->container->set('router', $router);

        $response = $this->getMockForAbstractClass('\Backend\Interfaces\ResponseInterface');

        $formatter = $this->getMock(
            '\Backend\Interfaces\FormatterInterface',
            array('setValue', 'transform')
        );
        $formatter
            ->expects($this->once())
            ->method('setValue')
            ->with('buffered');

        $response = $this->getMockForAbstractClass('\Backend\Interfaces\ResponseInterface');
        $formatter
            ->expects($this->once())
            ->method('transform')
            ->with($result)
            ->will($this->returnValue($response));


        $this->container->set('formatter', $formatter);

        ob_start();
        echo 'buffer';
        $transformer = new CoreListener($this->container);
        $transformer->coreResultEvent($event);
    }

    /**
     * @return void
     * @covers Backend\Core\Listener\CoreListener::coreResultEvent
     */
    public function testDoesNotAddGZippedBufferResultEvent()
    {
        $result = new \stdClass;

        $event = $this->getMock(
            'Backend\Core\Event\ResultEvent',
            null,
            array($result)
        );
        $event
            ->expects($this->never())
            ->method('stopPropagation');

        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $this->container->set('request', $request);

        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );
        $callback
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('actionFormat'));
        $callback
            ->expects($this->once())
            ->method('execute')
            ->with(array($result))
            ->will($this->returnValue($result));

        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->once())
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue($callback));
        $this->container->set('router', $router);

        $response = $this->getMockForAbstractClass('\Backend\Interfaces\ResponseInterface');

        $formatter = $this->getMock(
            '\Backend\Interfaces\FormatterInterface',
            array('setValue', 'transform')
        );
        $formatter
            ->expects($this->never())
            ->method('setValue');

        $response = $this->getMockForAbstractClass('\Backend\Interfaces\ResponseInterface');
        $formatter
            ->expects($this->once())
            ->method('transform')
            ->with($result)
            ->will($this->returnValue($response));


        $this->container->set('formatter', $formatter);

        ob_start('ob_gzhandler');
        $transformer = new CoreListener($this->container);
        $transformer->coreResultEvent($event);
    }

    /**
     * @return void
     * @covers Backend\Core\Listener\CoreListener::coreCallbackEvent
     * @covers Backend\Core\Listener\CoreListener::transformCallback
     */
    public function testHandleCallbackEvent()
    {
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
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('home'));
        $callback
            ->expects($this->once())
            ->method('setMethod')
            ->with('homeAction');

        $event = $this->getMock(
            'Backend\Core\Event\CallbackEvent',
            null,
            array($callback)
        );

        $transformer = new CoreListener($this->container);
        $transformer->coreCallbackEvent($event);
   }
}