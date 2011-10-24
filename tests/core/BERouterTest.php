<?php
require_once('lib/core/BEApplication.obj.php');
require_once('lib/core/BERequest.obj.php');
require_once('lib/core/BERouter.obj.php');
require_once('lib/core/exceptions/UnsupportedMethodException.obj.php');
class BERouterTest extends PHPUnit_Framework_TestCase
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
        $request = new BERequest($query, $method);
        $router  = new BERouter($request);
        $this->assertEquals($result, $router->getQuery());
    }

    /**
     * @expectedException UnsupportedMethodException
     */
    public function testRequestMethod()
    {
        $request = new BERequest(array(), 'UPDATE');
    }
}
