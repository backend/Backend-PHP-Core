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
        Application::setDebugLevel(1);
        $this->request = new Request('http://www.google.com/', 'GET', array('format' => 'html'));
        $this->request->setQuery('/');
    }

    protected function getApplication($request = false)
    {
        return new Application($request ?: $this->request, '../configs/testing.yaml');
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
     * @todo Make sure we use the test log file, even if we set site state to dev
     * @return void
     */
    public function testConstructor()
    {
        //Asserts
        $application = $this->getApplication();
        $this->assertTrue($application->getConstructed());
        $this->assertEquals($this->request, $application->getRequest());

        $this->assertNotEmpty(Application::getSiteState());

        Application::setSiteState('development');
        Application::setConstructed(false);
        $application = $this->getApplication();
        $this->assertEquals(5, Application::getDebugLevel());

        $_SERVER['DEBUG_LEVEL'] = 13;
        Application::setConstructed(false);
        $application = $this->getApplication();
        $this->assertEquals(13, Application::getDebugLevel());

        Application::setDebugLevel(1);
    }

    /**
     * Test the main function
     *
     * @return void
     */
    public function testMain()
    {
        //Setup
        $this->request->setQuery('/');
        $application = $this->getApplication();
        $result = $application->main();

        //Asserts
        $this->assertInstanceOf('\Backend\Core\Response', $result);
        $this->assertSame(200, $result->getStatusCode());
    }

    /**
     * Test the main function with an unknown controller
     *
     * @return void
     */
    public function testMainWith404()
    {
        //Setup
        $this->request->setQuery('/unknown_controller');
        $application = $this->getApplication();
        $result = $application->main();

        //Asserts
        $this->assertInstanceOf('\Backend\Core\Response', $result);
        $this->assertSame(404, $result->getStatusCode());
    }

    /**
     * Test the main function
     *
     * @return void
     */
    public function testMainWithRoutePath()
    {
        //Mock Objects
        $routePath = $this->getMock('\Backend\Core\Utilities\RoutePath', array(), array(), '', false);

        $route = $this->getMock('\Backend\Core\Route');
        $route->expects($this->any())->method('resolve')
            ->with($this->isInstanceOf('\Backend\Core\Request'))
            ->will($this->returnValue($routePath));

        //Setup
        $this->request->setQuery('/');
        $application = $this->getApplication();
        $result = $application->main();

        //Asserts
        $this->assertInstanceOf('\Backend\Core\Response', $result);
        $this->assertSame(200, $result->getStatusCode());
    }

    /**
     * Test the Application error handling
     *
     * @return void
     * @expectedException \ErrorException
     */
    public function dontTestError()
    {
        Application::error(1, 'SomeError', __FILE__, __LINE__);
    }

    /**
     * Test getting and setting the Debug Level
     *
     * @return void
     */
    public function testDebugLevel()
    {
        //The default debugging level for testing is 1
        $this->assertSame(1, Application::getDebugLevel());
        Application::setDebugLevel(2);
        //Test setting an integer
        $this->assertSame(2, Application::getDebugLevel());
        //Test Setting a string
        Application::setDebugLevel('4');
        $this->assertSame(4, Application::getDebugLevel());
        //Test an invalid level
        $this->assertFalse(Application::setDebugLevel('string'));
        $this->assertFalse(Application::setDebugLevel(0));


        //Reset the Debug Level
        Application::setDebugLevel(1);
    }

    /**
     * Test adding and getting Namespaces
     *
     * @return void
     */
    public function testRegisterNamespace()
    {
        $this->assertContains('\Backend\Core', Application::getNamespaces());
        Application::registerNamespace('\Some\Namespace');
        $this->assertContains('\Some\Namespace', Application::getNamespaces());
    }

    /**
     * testHandleResult
     *
     * @expectedException \Backend\Core\Exceptions\BackendException
     * @expectedExceptionMessage No View to work with
     * @return void
     */
    public function testHandleResultInvalidView()
    {
        $request = new Request('http://www.google.com/', 'GET', array('format' => 'invalid'));
        $request->setQuery('/');

        $application = $this->getApplication($request);
        $application->handleResult('');
    }

    /**
     * testHandleResult
     *
     * @expectedException \Backend\Core\Exceptions\BackendException
     * @expectedExceptionMessage Unrecognized Response
     * @return void
     */
    public function testHandleResultInvalidResponse()
    {
        $view = new Views\Test($this->request);
        $view->setResponse(false);
        $application = $this->getApplication();
        $application->handleResult('');
    }

    /**
     * Test Application::getViewMethod
     *
     * @return void
     */
    public function testGetViewMethod()
    {
        $callback = array(
            new \Backend\Base\Controllers\ExamplesController(),
            'homeAction'
        );
        $request     = new \Backend\Core\Request('http://www.google.com', 'GET');
        $application = $this->getApplication($request);
        $view        = new \Backend\Base\Views\Cli($request);
        $method      = $application->getViewMethod($callback, $view);
        $this->assertSame('homeCli', $method);
    }
}
