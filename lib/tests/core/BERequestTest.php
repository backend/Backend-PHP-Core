<?php
require('lib/core/BEApplication.obj.php');
require('lib/core/BERequest.obj.php');
require('lib/exceptions/UnsupportedMethodException.obj.php');
class BERequestTest extends PHPUnit_Framework_TestCase
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
            array(array('home' => ''), 'home'),
            array(array('home/' => ''), 'home'),
            array(array('home%2Fread' => ''), 'home/read'),
            array(array('home/read/some%252Fthing' => ''), 'home/read/some%2Fthing'),
            array(array('home%2Fread/some%252Fthing' => ''), 'home/read/some%2Fthing'),
        );
    }

    /**
     * @dataProvider provider
     */
    public function testURLFormats($query, $result)
    {
        $request = new BERequest($query, 'GET');
        $this->assertEquals($result, $request->getQuery());
    }

    /**
     * @expectedException UnsupportedMethodException
     */
    public function testRequestMethod()
    {
        $request = new BERequest(array(), 'UPDATE');
    }
}
