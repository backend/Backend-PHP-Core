<?php
/**
 * File defining RequestTest
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
use \Backend\Core\Request;
/**
 * Class to test the \Backend\Core\Controller class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 * @backupGlobals enabled
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set up the test
     *
     * @return void
     */
    protected function setUp()
    {
    }

    /**
     * Tear down the test
     *
     * @return void
     */
    protected function tearDown()
    {
    }

    /**
     * Test the bare constructor.
     *
     * @return void
     */
    public function testConstructor()
    {
        $request = new Request();
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('GET', $request->getMethod());
        $this->assertInternalType('array', $request->getPayload());
    }

    /**
     * Test an URL without the index.php part
     *
     * @return void
     */
    public function testNoIndex()
    {
        //Without Trailing Slash
        $request = new Request('http://backend-php.net', 'GET');
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals(
            'http://backend-php.net/index.php/', $request->getSiteUrl()
        );
        //With Trailing Slash
        $request = new Request('http://backend-php.net/', 'GET');
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals(
            'http://backend-php.net/index.php/', $request->getSiteUrl()
        );
    }

    /**
     * Test an URL without a query
     *
     * @return void
     */
    public function testEmptyQuery()
    {
        $request = new Request('http://backend-php.net/index.php', 'GET');
        $this->assertEquals('/', $request->getPath());
    }

    /**
     * Test a simple Query
     *
     * @return void
     */
    public function testSimpleQuery()
    {
        $request = new Request('http://backend-php.net/index.php/something', 'GET');
        $this->assertEquals('/something', $request->getPath());
        //With Trailing Slash
        $request = new Request('http://backend-php.net/index.php/something/', 'GET');
        $this->assertEquals('/something', $request->getPath());
    }

    /**
     * Tear down the test
     *
     * @return void
     */
    public function testSiteURl()
    {
        $request = new Request(
            'http://backend-php.net/index.php/something/else', 'POST'
        );
        $this->assertEquals(
            'http://backend-php.net/index.php/', $request->getSiteUrl()
        );
    }

    /**
     * Test a Custom Port
     *
     * @return void
     */
    public function testCustomPort()
    {
        $request = new Request('http://backend-php.net:8080/index.php/something');
        $serverInfo = $request->getServerInfo();
        $this->assertEquals(8080, $serverInfo['SERVER_PORT']);
    }

    /**
     * Test HTTPS
     *
     * @return void
     */
    public function testHttps()
    {
        $request = new Request('https://backend-php.net');
        $this->assertEquals('on', $request->getServerInfo('https'));
        $this->assertEquals('443', $request->getServerInfo('server_port'));
    }

    /**
     * Data provider for testPayload.
     *
     * @return array
     */
    public function dataPayload()
    {
        $result = array();
        $payload = new \StdClass();
        $payload->var = 'value';
        $result[] = array($payload);
        $result[] = array('var=value');
        $result[] = array(array('var' => 'value'));
        return $result;
    }

    /**
     * Test the payload.
     *
     * @param mixed $payload The payload to test.
     *
     * @dataProvider dataPayload
     * @return void
     */
    public function testPayload($payload)
    {
        $request = new Request(null, null, $payload);
        $this->assertInternalType('array', $request->getPayload());
        $this->assertEquals(array('var' => 'value'), $request->getPayload());
    }

    /**
     * Test the Site Path
     *
     * @return void
     */
    public function testSitePath()
    {
        //TODO
    }
}
