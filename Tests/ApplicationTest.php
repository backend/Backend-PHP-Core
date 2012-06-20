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
use \Backend\Core\Application;
use \Backend\Core\Request;
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

    /**
     * Set up the test
     *
     * Set the debugging level to 1, set a Request
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
     * Test the constructor
     *
     * @return void
     */
    public function testConstructor()
    {
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $formatter = $this->getMock('\Backend\Interfaces\FormatterInterface');
        $application = new Application($router, $formatter);
        $this->assertSame($router, $application->getRouter());
        $this->assertSame($formatter, $application->getFormatter());
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
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->exactly(2))
            ->method('inspect')
            ->with($request)
            ->will($this->onConsecutiveCalls($request, $callback));
        $response  = $this->getMockForAbstractClass(
            '\Backend\Interfaces\ResponseInterface'
        );
        $formatter = $this->getMock('\Backend\Interfaces\FormatterInterface');
        $formatter
            ->expects($this->once())
            ->method('transform')
            ->with(null)
            ->will($this->returnValue($response));
        $application = new Application($router, $formatter);
        $result = $application->main($request);

        //Asserts
        $this->assertSame($response, $result);
        $this->assertSame($request, $application->getRequest());
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
        $application = new Application($router);
        $result = $application->main($request);
    }

    /**
     * Test the main function with no route for the request 
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Unsupported format requested
     */
    public function testMainWith415()
    {
        //Setup
        $request = $this->getMockForAbstractClass(
            '\Backend\Interfaces\RequestInterface'
        );
        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );
        $router = $this->getMock('\Backend\Interfaces\RouterInterface');
        $router
            ->expects($this->once())
            ->method('inspect')
            ->with($request)
            ->will($this->returnValue($callback));
        $application = new Application($router);
        $result = $application->main($request);
    }

    /**
     * Test an invalid callback
     *
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Invalid Callback
     * @return void
     */
    public function testInvalidCallback()
    {
        $application = new Application();
        $application->checkCallback(array(0, 1, 2));
    }

    /**
     * Test the checking of callbacks
     *
     * @return void
     */
    public function testCheckCallback()
    {
        $application = new Application();
        $controller = $this->getMockForAbstractClass(
            '\Backend\Interfaces\ControllerInterface'
        );
        $callback = $this->getMockForAbstractClass(
            '\Backend\Interfaces\CallbackInterface'
        );
        $callback
            ->expects($this->once())
            ->method('getClass');
        $callback
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('some'));
        $callback
            ->expects($this->once())
            ->method('setMethod')
            ->with('someAction');
        $application->checkCallback($callback);
    }
}
