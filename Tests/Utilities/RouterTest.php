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
<<<<<<< HEAD
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


=======
        $configArr = array('one' => 'two');
        $config = $this->getMock('\Backend\Interfaces\ConfigInterface');
        $config
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($configArr));
        $factory = $this->getMock('Backend\Interfaces\CallbackFactoryInterface');
        $router = new Router($config, $factory);
        $this->assertEquals($configArr, $router->getConfig());
        $this->assertSame($factory, $router->getCallbackFactory());

        $router = new Router($configArr, $factory);
        $this->assertEquals($configArr, $router->getConfig());

        $router = new Router((object)$configArr, $factory);
        $this->assertEquals($configArr, $router->getConfig());
    }

    /**
     * Test an invalid Router config.
     *
     * @return void
     * @expectedException \Backend\Core\Exceptions\ConfigException
     * @expectedExceptionMessage Invalid Router Configuration
     */
    public function testInvalidConfig()
    {
        $factory = $this->getMock('Backend\Interfaces\CallbackFactoryInterface');
        $router = new Router(false, $factory);
    }

>>>>>>> 6a080a46cbd8bbfb8b709b34769d539c1c0aa188
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
<<<<<<< HEAD
        $router = new Router($config);
=======

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');

        $router = new Router($config, $factory);
>>>>>>> 6a080a46cbd8bbfb8b709b34769d539c1c0aa188
        $this->assertFalse($router->inspect($request));

        //Try matching the route
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));
        $route  = array('route' => '/', 'callback' => 'Some::callback');
        $config = array('routes' => array('/' => $route));
<<<<<<< HEAD
        $router = new Router($config);
=======

>>>>>>> 6a080a46cbd8bbfb8b709b34769d539c1c0aa188
        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with($route['callback'])
<<<<<<< HEAD
            ->will($this->returnSelf());
        $router->setCallbackFactory($factory);
        $this->assertSame($factory, $router->inspect($request));
=======
            ->will($this->returnValue(true));

        $router = new Router($config, $factory);
        $this->assertTrue($router->inspect($request));
>>>>>>> 6a080a46cbd8bbfb8b709b34769d539c1c0aa188

        //Try mismatching the route
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere/nothere'));
        $route  = array('route' => '/<here>');
        $config = array('routes' => array('/' => $route));
<<<<<<< HEAD
        $router = new Router($config);
=======

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');

        $router = new Router($config, $factory);
>>>>>>> 6a080a46cbd8bbfb8b709b34769d539c1c0aa188
        $this->assertFalse($router->inspect($request));

        //Try matching the route with a regex
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere'));
        $route  = array('route' => '/<something>', 'callback' => 'Some::callback');
        $config = array('routes' => array('/' => $route));
<<<<<<< HEAD
        $router = new Router($config);
=======

        $factory = $this->getMock('Backend\Interfaces\CallbackFactoryInterface');
        $router = new Router($config, $factory);
>>>>>>> 6a080a46cbd8bbfb8b709b34769d539c1c0aa188
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
<<<<<<< HEAD
        $this->markTestIncomplete();
=======
        //Test an empty path
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));
        $config = array('controllers' => array('somewhere' => '/Some/Controller'));

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');

        $router = new Router($config, $factory);
        $this->assertFalse($router->inspect($request));

        //Test no match on the controller
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/nothere'));
        $config = array('controllers' => array('somewhere' => '/Some/Controller'));

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');

        $router = new Router($config, $factory);
        $this->assertFalse($router->inspect($request));
    }

    /**
     * Data provider for testCheckForControllersMatches
     *
     * @return array
     */
    public function dataCheckForControllersMatches()
    {
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
        $config = array('controllers' => array('somewhere' => '/Some/Controller'));

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::list')
            ->will($this->returnValue(true));

        $router = new Router($config, $factory);
        $return[] = array($router, $request);

        //Test a match on the controller: GET (list - default)
        $request = $this->getMock('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/somewhere'));
        $config = array('controllers' => array('somewhere' => '/Some/Controller'));

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::list')
            ->will($this->returnValue(true));

        $router = new Router($config, $factory);
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
        $config = array('controllers' => array('somewhere' => '/Some/Controller'));

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::read')
            ->will($this->returnValue(true));

        $router = new Router($config, $factory);
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
        $config = array('controllers' => array('somewhere' => '/Some/Controller'));

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::create')
            ->will($this->returnValue(true));

        $router = new Router($config, $factory);
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
        $config = array('controllers' => array('somewhere' => '/Some/Controller'));

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::update')
            ->will($this->returnValue(true));

        $router = new Router($config, $factory);
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
        $config = array('controllers' => array('somewhere' => '/Some/Controller'));

        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $factory
            ->expects($this->once())
            ->method('fromString')
            ->with('/Some/Controller::delete')
            ->will($this->returnValue(true));

        $router = new Router($config, $factory);
        $return[] = array($router, $request);
        return $return;
    }

    /**
     * Test the chec method for Controller with matches
     *
     * @dataProvider dataCheckForControllersMatches
     * @return void
     */
    public function testCheckForControllersMatches($router, $request)
    {
        $this->assertTrue($router->inspect($request));
>>>>>>> 6a080a46cbd8bbfb8b709b34769d539c1c0aa188
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
<<<<<<< HEAD
        $factory = $this->getMock('\Backend\Interfaces\CallbackFactoryInterface');
        $router = new Router();
        $router->setCallbackFactory($factory);
        $this->assertSame($factory, $router->getCallbackFactory());

        $router = new Router();
        $this->assertInstanceOf(
            '\Backend\Interfaces\CallbackFactoryInterface',
            $router->getCallbackFactory()
        );
=======
        $config = $this->getMock('\Backend\Interfaces\ConfigInterface');
        $factory = $this->getMock('Backend\Interfaces\CallbackFactoryInterface');
        $router = new Router($config, $factory);
        $this->assertSame($factory, $router->getCallbackFactory());
        $router->setCallbackFactory($factory);
        $this->assertSame($factory, $router->getCallbackFactory());
>>>>>>> 6a080a46cbd8bbfb8b709b34769d539c1c0aa188
    }
}
