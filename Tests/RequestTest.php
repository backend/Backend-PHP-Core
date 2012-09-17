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
            'http://backend-php.net', $request->getUrl()
        );
        //With Trailing Slash
        $request = new Request('http://backend-php.net/', 'GET');
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals(
            'http://backend-php.net', $request->getUrl()
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
        $this->assertEquals('/index.php', $request->getPath());
    }

    /**
     * Test a simple Query
     *
     * @return void
     */
    public function testSimpleQuery()
    {
        $request = new Request('http://backend-php.net/index.php/something', 'GET');
        $this->assertEquals('/index.php/something', $request->getPath());
        //With Trailing Slash
        $request = new Request(
            'http://backend-php.net/base/index.php/something/', 'GET'
        );
        $this->assertEquals('/base/index.php/something', $request->getPath());
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
     * Test getMethod.
     *
     * @return void
     */
    public function testGetSetMethod()
    {
        //Default to GET for non CLI and non Request
        $request = new Request();
        $request->setServerInfo('argv', array());
        $request->setServerInfo('REQUEST_METHOD', null);
        $this->assertEquals('GET', $request->getMethod());

        //Default to REQUEST_METHOD for Requests
        $request = new Request();
        $request->setServerInfo('REQUEST_METHOD', 'GET');
        $this->assertEquals('GET', $request->getMethod());

        //Default to GET for CLI
        $request = new Request();
        $this->assertEquals('GET', $request->getMethod());

        //Set in CLI
        $request = new Request();
        $request->setServerInfo('argv', array('script', 'POST'));
        $this->assertEquals('POST', $request->getMethod());

        //Check that it's set correctly
        $request = new Request(null, 'post');
        $request->setServerInfo('REQUEST_METHOD', 'GET');
        $this->assertEquals('POST', $request->getMethod());
        $request->setMethod('options');
        $this->assertEquals('OPTIONS', $request->getMethod());

        //Check setting the method in the payload
        $request = new Request();
        $request->setServerInfo('REQUEST_METHOD', 'GET');
        $request->setPayload(array('_method' => 'put'));
        $this->assertEquals('PUT', $request->getMethod());

        //Check setting the method in the headers
        $request = new Request();
        $request->setHeader('REQUEST_METHOD', 'GET');
        $request->setHeader('METHOD_OVERRIDE', 'delete');
        $this->assertEquals('DELETE', $request->getMethod());

        //Check the default set in REQUEST_METHOD
        $request = new Request();
        $request->setServerInfo('REQUEST_METHOD', 'GET');
        $this->assertEquals('GET', $request->getMethod());
    }

    /**
     * Test the setMethod method.
     *
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Unsupported method SOMETHING
     * @return void
     */
    public function testSetMethodException()
    {
        new Request(null, 'something');
    }

    /**
     * Test getPath
     *
     * @return void
     */
    public function testSetGetPath()
    {
        // Root path given
        $request = new Request('https://backend-php.net/index.php/');
        $this->assertEquals('/index.php', $request->getPath());

        // Root path given
        $request = new Request('https://backend-php.net/');
        $this->assertEquals('/', $request->getPath());

        // Path given
        $request = new Request('https://backend-php.net/index.php/something/else');
        $this->assertEquals('/index.php/something/else', $request->getPath());

        // Other file given
        $request = new Request('https://backend-php.net/somewhere/index.html');
        $this->assertEquals('/somewhere/index.html', $request->getPath());

        // Clean up the path
        $request = new Request();
        $request->setPath('/somewhere/');
        $this->assertEquals('/somewhere', $request->getPath());

        // Empty path equals root path
        $request = new Request();
        $request->setPath('');
        $this->assertEquals('/', $request->getPath());
    }

    /**
     * Test the getUrl and prepareUrl methods
     *
     * @return void
     */
    public function testUrl()
    {
        // With index.php
        $request = new Request('https://backend-php.net/index.php/something/else');
        $this->assertEquals(
            'https://backend-php.net/index.php', $request->getUrl()
        );

        // Root index
        $request = new Request('https://backend-php.net/something/else');
        $request->setServerInfo('SCRIPT_NAME', '/index.php');
        $this->assertEquals(
            'https://backend-php.net/index.php', $request->getUrl()
        );

        // Base index
        $request = new Request('https://backend-php.net/base/here');
        $request->setServerInfo('SCRIPT_NAME', '/base/index.php');
        $this->assertEquals(
            'https://backend-php.net/base/index.php', $request->getUrl()
        );

        // Set Request URI
        $request = new Request('https://backend-php.net/base/here/');
        $request->setServerInfo('REQUEST_URI', '/base/index.php/here/');
        $this->assertEquals(
            'https://backend-php.net/base/index.php/here', $request->getUrl()
        );
    }

    /**
     * Test the getMimeType method for the CLI or empty mime types.
     *
     * @return void
     */
    public function testCliOrEmptyGetMimeType()
    {
        $request = new Request();
        $request->setServerInfo('argc', 1);
        $request->setServerInfo('argv', array('/usr/bin/phpunit'));
        $this->assertEquals('cli', $request->getMimeType());

        $request = new Request();
        $request->setServerInfo('request_method', 'get');
        $request->setServerInfo('argc', 0);
        $request->setServerInfo('argv', array());
        $this->assertNull($request->getMimeType());
    }

    /**
     * Data provider for testGetMimeType.
     *
     * @return array
     */
    public function dataGetMimeType()
    {
        $result = array();
        $result[] = array('text/html, text/*, */*', 'text/html');
        $result[] = array('*/*, text/*, text/html', 'text/html');
        $result[] = array('application/xml; q=0.2, text/html', 'text/html');
        $result[] = array('text/html; q=0.2', 'text/html');
        $result[] = array('text/html', 'text/html');
        $result[] = array('text/html, application/xml', 'text/html');

        return $result;
    }

    /**
     * Test the getMimeType method.
     *
     * @param string $acceptHeader The accept header to check.
     * @param string $expected     The expected result.
     *
     * @dataProvider dataGetMimeType();
     * @return void
     */
    public function testGetMimeType($acceptHeader, $expected)
    {
        $request = new Request();
        $request->setServerInfo('request_method', 'get');
        $request->setHeader('accept', $acceptHeader);
        $this->assertEquals($expected, $request->getMimeType());

        //And check it again
        $this->assertEquals($expected, $request->getMimeType());
    }

    /**
     * Test the getSpecifiedFormat methos.
     *
     * @return void
     */
    public function testGetSpecifiedFormat()
    {
        $request = new Request();
        $request->setServerInfo('argv', array('index.php', 'GET', 'home', 'xml'));
        $this->assertEquals('xml', $request->getSpecifiedFormat());

        $request = new Request(null, null, array('format' => 'json'));
        $this->assertEquals('json', $request->getSpecifiedFormat());

        //And check it again
        $this->assertEquals('json', $request->getSpecifiedFormat());

        $request = new Request();
        $this->assertNull($request->getSpecifiedFormat());
    }

    /**
     * Test the getExtension method.
     *
     * @return void
     */
    public function testGetExtension()
    {
        $request = new Request('http://backend-php.net/index.html');
        $this->assertEquals('html', $request->getExtension());

        $request = new Request('http://backend-php.net/index.something.html');
        $this->assertEquals('html', $request->getExtension());

        $request = new Request('http://backend-php.net/index.php/list.json');
        $this->assertEquals('json', $request->getExtension());

        //And check it again
        $this->assertEquals('json', $request->getExtension());

        $request = new Request('http://backend-php.net/');
        $this->assertNull($request->getExtension());

    }

    /**
     * Test setting a server info value.
     *
     * @return void
     */
    public function testGetSetServerInfo()
    {
        $request = new Request();
        $request->setServerInfo('Some', 'Value');
        $this->assertEquals('Value', $request->getServerInfo('some'));

        //argv is a special case
        $request->setServerInfo('argv', array('one' => 'two'));
        $this->assertEquals(array('one' => 'two'), $request->getServerInfo('argv'));

        //Check deprecated X_ values
        $request->setServerInfo('X_VALUE', 'XValue');
        $this->assertEquals('XValue', $request->getServerInfo('VALUE'));
        $this->assertEquals('XValue', $request->getServerInfo('X_VALUE'));

        //Return null on failure
        $this->assertNull($request->getServerInfo('random_value'));
    }

    /**
     * Test the different isMethod functions.
     *
     * @return void
     */
    public function testIsMethod()
    {
        $request = new Request(null, 'post');
        $this->assertTrue($request->isPost());
        $request = new Request(null, 'put');
        $this->assertTrue($request->isPut());
        $request = new Request(null, 'get');
        $this->assertTrue($request->isGet());
        $request = new Request(null, 'delete');
        $this->assertTrue($request->isDelete());
        $request = new Request(null, 'head');
        $this->assertTrue($request->isHead());
        $request = new Request(null, 'options');
        $this->assertTrue($request->isOptions());
    }

    /**
     * Data for the testParseContent method.
     *
     * @return array
     */
    public function dataParseContent()
    {
        $result = array();
        // Test json payload
        $result[] = array(
            'application/json', dirname(__FILE__) . '/auxiliary/payload.json',
        );
        $result[] = array(
            'text/json', dirname(__FILE__) . '/auxiliary/payload.json',
        );
        $result[] = array(
            'text/javascript', dirname(__FILE__) . '/auxiliary/payload.json',
        );
        // Test www form payload
        $result[] = array(
            'application/x-www-form-urlencoded',
            dirname(__FILE__) . '/auxiliary/payload.txt',
        );
        $result[] = array(
            'text/plain', dirname(__FILE__) . '/auxiliary/payload.txt',
        );
        // Test xml payload
        $result[] = array(
            'application/xml', dirname(__FILE__) . '/auxiliary/payload.xml',
        );

        return $result;
    }

    /**
     * Test the parsing of content.
     *
     * @param string $contentType The content type.
     * @param string $filename    The name of file where input should be read from.
     *
     * @dataProvider dataParseContent
     * @return void
     */
    public function testParseContent($contentType, $filename)
    {
        $request = new Request();
        $content = file_get_contents($filename);
        $result = $request->parseContent($contentType, $content);
        $this->assertEquals(array('var' => 'value'), $result);

    }

    /**
     * Test parsing content from a file
     *
     * @return void
     */
    public function testParseFileContent()
    {
        $request = new Request();
        $request->setInputStream(dirname(__FILE__) . '/auxiliary/payload.json');
        $result = $request->parseContent('application/json');
        $this->assertEquals(array('var' => 'value'), $result);
    }

    /**
     * Test unknown content
     *
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Unknown Content Type: unknown/content
     * @expectedExceptionCode 400
     * @return void
     */
    public function testUnknownContent()
    {
        $request = new Request();
        $request->parseContent('unknown/content');
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
     * Test setting the payload.
     *
     * @param mixed $payload The payload to test.
     *
     * @dataProvider dataPayload
     * @return void
     */
    public function testPayload($payload)
    {
        $request = new Request(null, null, $payload);
        $this->assertEquals(array('var' => 'value'), $request->getPayload());
    }

    /**
     * Test getting the payload.
     *
     * @return void
     */
    public function testGetPayload()
    {
        // Test empty payload
        $request = new Request(null, 'delete');
        $this->assertEquals(array(), $request->getPayload());

        // Test GET and POST/PUT
        $request = new Request(null, 'get');
        $old = $_GET;
        $_GET['one'] = 'two';
        $this->assertEquals($_GET, $request->getPayload());
        $_GET = $old;

        $request = new Request(null, 'post');
        $old = $_POST;
        $_POST['one'] = 'two';
        $this->assertEquals($_POST, $request->getPayload());

        $request = new Request(null, 'put');
        $this->assertEquals($_POST, $request->getPayload());
        $_POST = $old;

        // Test content from parseContent
        $request = $this->getMock(
            '\Backend\Core\Request',
            array('parseContent'),
            array(null, 'delete')
        );
        $request
            ->expects($this->once())
            ->method('parseContent')
            ->will($this->returnValue(array('var' => 'value')));
        $this->assertEquals(array('var' => 'value'), $request->getPayload());
    }

    /**
     * Test getting the payload from the CLI
     *
     * @return void
     */
    public function testGetPayloadFromCli()
    {
        $request = new Request();
        $argv = $request->getServerInfo('argv');
        $argv = array_pad($argv, 5, null);
        $argv[4] = 'var=value';
        $request->setServerInfo('argv', $argv);
        $this->assertEquals(array('var' => 'value'), $request->getPayload());

        $request = new Request();
        $argv = $request->getServerInfo('argv');
        $argv = array_pad($argv, 5, null);
        $argv[4] = 'jannie verjaar';
        $this->assertEquals(array(), $request->getPayload());
    }

    /**
     * Test fromCli
     *
     * @return void
     */
    public function testFromCli()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test getting and setting the input stream
     *
     * @return void
     */
    public function testInputStreamAccessors()
    {
        $this->markTestIncomplete();
    }

}
