<?php
/**
 * File defining \Backend\Core\Tests\Utilities\RouterTest
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
use Backend\Core\Utilities\Router;
use Backend\Interfaces\CallbackFactoryInterface;
use Backend\Core\Utilities\Config;
/**
 * Class to test the \Backend\Core\Utilities\Router class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    protected $config;

    public function setUp()
    {
        $parser = $this->getMockForAbstractClass('\Backend\Interfaces\ParserInterface');
        $this->config = new Config($parser);
    }
    /**
     * Test the constructor
     *
     * @return void
     */
    public function testConstructor()
    {
        $config = $this->getMock('\Backend\Interfaces\ConfigInterface');
        $factory = $this->getMock('Backend\Interfaces\CallbackFactoryInterface');

        $router = new Router($config, $factory);
        $this->assertSame($config, $router->getConfig());
        $this->assertSame($factory, $router->getCallbackFactory());
    }

    /**
     * Test the check method for Routes.
     *
     * @return void
     */
    public function testCheckForNoMatchOnVerb()
    {
        //Test no match on the verb
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $route  = array('verb' => 'get');

        $configArr = array('routes' => array('/' => $route));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');

        $router = new Router($this->config, $factory);
        $this->assertFalse($router->inspect($request));
    }

    /**
     * Test the check method for Routes.
     *
     * @return void
     */
    public function testCheckForMatchOnRoute()
    {
        //Try matching the route
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));
        $route  = array('route' => '/', 'callback' => 'Some::callback');

        $configArr = array('routes' => array('/' => $route));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with($route['callback'])
            ->will($this->returnValue(true));

        $router = new Router($this->config, $factory);
        $this->assertTrue($router->inspect($request));
    }

    /**
     * Test the check method for Routes.
     *
     * @return void
     */
    public function testCheckForNoMatchOnRoute()
    {
        //Try mismatching the route
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere/nothere'));
        $route  = array('route' => '/<here>');

        $configArr = array('routes' => array('/' => $route));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');

        $router = new Router($this->config, $factory);
        $this->assertFalse($router->inspect($request));
    }

    /**
     * Test the check method for Routes.
     *
     * @return void
     */
    public function testCheckForMatchRegex()
    {
        //Try matching the route with a regex
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere'));
        $route  = array('route' => '/<something>', 'callback' => 'Some::callback');

        $configArr = array('routes' => array('/' => $route));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with($route['callback'], array('something' => 'somewhere'))
            ->will($this->returnValue(true));
        $router = new Router($this->config, $factory);
        $this->assertEquals(true, $router->inspect($request));
    }

    /**
     * Test the check method for Routes.
     *
     * @return void
     */
    public function testCheckForMatchRegexWithDefault()
    {
        //Try matching the route with a regex
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere/nothere'));
        $route  = array(
            'route' => '/<something>/<another>', 'callback' => 'Some::callback',
            'defaults' => array('another' => 'default')
        );

        $configArr = array('routes' => array('/' => $route));
        $this->config->setAll($configArr);

        $arguments = array('something' => 'somewhere', 'another' => 'nothere');
        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with($route['callback'], $this->identicalTo($arguments))
            ->will($this->returnValue(true));
        $router = new Router($this->config, $factory);
        $this->assertEquals(true, $router->inspect($request));
    }

    /**
     * Test the check method for Controllers.
     *
     * @return void
     */
    public function testCheckForControllers()
    {
        //Test an empty path
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));

        $configArr = array('controllers' => array('somewhere' => '/Some/Controller'));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');

        $router = new Router($this->config, $factory);
        $this->assertFalse($router->inspect($request));

        //Test no match on the controller
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/nothere'));

        $configArr = array('controllers' => array('somewhere' => '/Some/Controller'));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');

        $router = new Router($this->config, $factory);
        $this->assertFalse($router->inspect($request));
    }

    /**
     * Data provider for testCheckForControllersMatches
     *
     * @return array
     */
    public function dataCheckForControllersMatches()
    {
        $this->setUp();
        $return = array();

        //Test a match on the controller: GET (list)
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere'));
        $request
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('get'));

        $configArr = array('controllers' => array('somewhere' => '/Some/Controller'));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::list')
            ->will($this->returnValue(true));

        $router = new Router($this->config, $factory);
        $return[] = array($router, $request);

        //Test a match on the controller: GET (list - default)
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere'));

        $configArr = array('controllers' => array('somewhere' => '/Some/Controller'));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::list')
            ->will($this->returnValue(true));

        $router = new Router($this->config, $factory);
        $return[] = array($router, $request);

        //Test a match on the controller: GET (read)
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere/1'));
        $request
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('get'));

        $configArr = array('controllers' => array('somewhere' => '/Some/Controller'));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::read')
            ->will($this->returnValue(true));

        $router = new Router($this->config, $factory);
        $return[] = array($router, $request);

        //Test a match on the controller: POST
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere'));
        $request
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('post'));

        $configArr = array('controllers' => array('somewhere' => '/Some/Controller'));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::create')
            ->will($this->returnValue(true));

        $router = new Router($this->config, $factory);
        $return[] = array($router, $request);

        //Test a match on the controller: PUT
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere'));
        $request
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('put'));

        $configArr = array('controllers' => array('somewhere' => '/Some/Controller'));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::update')
            ->will($this->returnValue(true));

        $router = new Router($this->config, $factory);
        $return[] = array($router, $request);

        //Test a match on the controller: DELETE
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere'));
        $request
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('delete'));

        $configArr = array('controllers' => array('somewhere' => '/Some/Controller'));
        $this->config->setAll($configArr);

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::delete')
            ->will($this->returnValue(true));

        $router = new Router($this->config, $factory);
        $return[] = array($router, $request);

        return $return;
    }

    /**
     * Test the check method for Controller with matches
     *
     * @param \Backend\Interfaces\RouterInterface  $router  Router to check.
     * @param \Backend\Interfaces\RequestInterface $request The request to inspect
     *
     * @dataProvider dataCheckForControllersMatches
     * @return void
     */
    public function testCheckForControllersMatches($router, $request)
    {
        $this->assertTrue($router->inspect($request));
    }

    /**
     * Test the resolve method.
     *
     * @return void
     */
    public function testResolve()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test the Callback Factory setter and getter.
     *
     * @return void
     */
    public function testCallbackFactoryAccessors()
    {
        $config = $this->getMock('\Backend\Interfaces\ConfigInterface');
        $factory = $this->getMock('Backend\Interfaces\CallbackFactoryInterface');
        $router = new Router($config, $factory);
        $this->assertSame($factory, $router->getCallbackFactory());
        $router->setCallbackFactory($factory);
        $this->assertSame($factory, $router->getCallbackFactory());
    }
}
