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
use \Backend\Core\Utilities\Router;
use \Backend\Interfaces\CallbackFactoryInterface;
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

        $expected = array('some' => 'value');
        $router = new Router($expected);
        $this->assertEquals($expected, $router->getConfig()->get());
    }

    /**
     * Tet the getFileName method.
     * 
     * @return void
     */
    public function testGetFileName()
    {
        $router = new Router();
        $this->assertTrue(is_string($router->getFileName()));
    }


    /**
     * Test the check method for Routes.
     *
     * @return void
     */
    public function testCheckForRoutes()
    {
        //Test no match on the verb
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $route  = array('verb' => 'get');
        $config = array('routes' => array('/' => $route));
        $router = new Router($config);
        $this->assertFalse($router->inspect($request));

        //Try matching the route
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));
        $route  = array('route' => '/', 'callback' => 'Some::callback');
        $config = array('routes' => array('/' => $route));
        $router = new Router($config);
        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with($route['callback'])
            ->will($this->returnSelf());
        $router->setCallbackFactory($factory);
        $this->assertSame($factory, $router->inspect($request));

        //Try mismatching the route
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere/nothere'));
        $route  = array('route' => '/<here>');
        $config = array('routes' => array('/' => $route));
        $router = new Router($config);
        $this->assertFalse($router->inspect($request));

        //Try matching the route with a regex
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere'));
        $route  = array('route' => '/<something>', 'callback' => 'Some::callback');
        $config = array('routes' => array('/' => $route));
        $router = new Router($config);
        $expected = array('Some::callback', array('something' => 'somewhere'));
        $this->assertEquals($expected, $router->inspect($request));
    }

    /**
     * Test the check method for Controllers.
     *
     * @return void
     */
    public function testCheckForControllers()
    {
        $this->markTestIncomplete();
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
        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $router = new Router();
        $router->setCallbackFactory($factory);
        $this->assertSame($factory, $router->getCallbackFactory());

        $router = new Router();
        $this->assertInstanceOf(
            '\Backend\Interfaces\CallbackFactoryInterface',
            $router->getCallbackFactory()
        );
    }
}
