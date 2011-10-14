<?php
require_once('lib/core/BEApplication.obj.php');
require_once('lib/core/BERequest.obj.php');
require_once('lib/exceptions/UnsupportedMethodException.obj.php');
class BERequestTest extends PHPUnit_Framework_TestCase
{
    private $_request;
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function providerUrlFormat()
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
     * @dataProvider providerUrlFormat
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

    public function providerRequestFormat()
    {
        return array(
            array(array('home.json' => ''), 'json'),
            array(array('home_json' => ''), 'json'),
            array(array('home_camp.json' => ''), 'json'),
            array(array('home_camp_json' => ''), 'json'),
            array(array('home_camp/read.json' => ''), 'json'),
            array(array('home_camp/read_json' => ''), 'json'),

            array(array('home_camp' => ''), 'cli'),
        );
    }

    /**
     * @dataProvider providerRequestFormat
     */
    public function testRequestFormat($query, $result)
    {
        $request = new BERequest($query, 'POST');
        $this->assertEquals($result, $request->getFormat());

    }
}
