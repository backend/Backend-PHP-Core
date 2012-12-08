<?php
/**
 * File defining Backend\Core\Tests\Listener\CoreListenerTest
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
namespace Backend\Core\Tests\Listener;

use Backend\Core\Listener\CoreListener;
use Backend\Core\Utilities\Config;
use Backend\Core\Utilities\DependencyInjectionContainer;

/**
 * Class to test the \Backend\Core\Listener\CoreListener class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
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
        $config = new Config($parser, __DIR__ . '/../auxiliary/configs/application.yml');
        $this->container = new DependencyInjectionContainer($config);
    }

    /**
     * @covers Backend\Core\Listener\CoreListener::__construct
     * @covers Backend\Core\Listener\CoreListener::getContainer
     */
    public function testConstructor()
    {
        $listener = new CoreListener($this->container);
        $this->assertSame($this->container, $listener->getContainer());
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

        $listener = new CoreListener($this->container);
        $listener->coreCallbackEvent($event);
    }

    /**
     * @return void
     * @covers Backend\Core\Listener\CoreListener::coreResultEvent
     */
    public function testHandleResultEvent()
    {
        $result = 'string';

        $event = $this->getMock(
            'Backend\Core\Event\ResultEvent',
            null,
            array($result)
        );

        $listener = new CoreListener($this->container);
        $listener->coreResultEvent($event);

        $this->assertInstanceOf('Backend\Interfaces\ResponseInterface', $event->getResponse());
    }

    /**
     * @return void
     * @covers Backend\Core\Listener\CoreListener::coreResultEvent
     */
    public function testResponseResultEvent()
    {
        $result = $this->getMockForAbstractClass('Backend\Interfaces\ResponseInterface');

        $event = $this->getMock(
            'Backend\Core\Event\ResultEvent',
            null,
            array($result)
        );

        $listener = new CoreListener($this->container);
        $listener->coreResultEvent($event);

        $this->assertSame($result, $event->getResponse());
    }

    /**
     * @return void
     * @covers Backend\Core\Listener\CoreListener::coreExceptionEvent
     */
    public function testCorrectResponseCodeInExceptionEvent()
    {
        $listener = new CoreListener($this->container);

        // Too High
        $exception = new \Exception('Message', 600);
        $event = $this->getMock(
            'Backend\Core\Event\ExceptionEvent',
            null,
            array($exception)
        );
        $event
            ->expects($this->never())
            ->method('stopPropagation');

        $listener->coreExceptionEvent($event);

        $response = $event->getResponse();
        $this->assertInstanceOf('\Backend\Core\Response', $response);
        $this->assertEquals(500, $response->getStatusCode());

        // Too Low
        $exception = new \Exception('Message', 99);
        $event = $this->getMock(
            'Backend\Core\Event\ExceptionEvent',
            null,
            array($exception)
        );
        $event
            ->expects($this->never())
            ->method('stopPropagation');

        $listener->coreExceptionEvent($event);

        $response = $event->getResponse();
        $this->assertInstanceOf('\Backend\Core\Response', $response);
        $this->assertEquals(500, $response->getStatusCode());

        // Correct
        $exception = new \Exception('Message', 400);
        $event = $this->getMock(
            'Backend\Core\Event\ExceptionEvent',
            null,
            array($exception)
        );
        $event
            ->expects($this->never())
            ->method('stopPropagation');

        $listener->coreExceptionEvent($event);

        $response = $event->getResponse();
        $this->assertInstanceOf('\Backend\Core\Response', $response);
        $this->assertEquals(400, $response->getStatusCode());
    }
}
