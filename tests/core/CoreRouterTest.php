<?php
require_once('lib/Core/Application.obj.php');
require_once('lib/Core/Request.obj.php');
require_once('lib/Core/Router.obj.php');
require_once('lib/Core/exceptions/UnsupportedMethodException.obj.php');
class CoreRouterTest extends PHPUnit_Framework_TestCase
{
    private $_request;
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function provider()
    {
        return array(
            array(array('resources' => ''), 'GET', 'resources/read/0'),
            array(array('resources' => ''), 'PUT', 'resources/update/0'),
            array(array('resources' => ''), 'POST', 'resources/create/0'),
            array(array('resources' => ''), 'DELETE', 'resources/delete/0'),
            array(array('resources/id' => ''), 'GET', 'resources/read/id'),
            array(array('resources/id' => ''), 'PUT', 'resources/update/id'),
            array(array('resources/id' => ''), 'POST', 'resources/create/id'),
            array(array('resources/id' => ''), 'DELETE', 'resources/delete/id'),

            array(array('resources/some%252Fid' => ''), 'DELETE', 'resources/delete/some%2Fid'),
        );
    }

    /**
     * @dataProvider provider
     */
    public function testRESTTranslations($query, $method, $result)
    {
        $request = new \Core\Request($query, $method);
        $router  = new \Core\Router($request);
        $this->assertEquals($result, $router->getQuery());
    }

    /**
     * @expectedException \Core\UnsupportedMethodException
     */
    public function testRequestMethod()
    {
        $request = new \Core\Request(array(), 'UPDATE');
    }
}
