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
        $request = new Request;
        $this->assertEquals('/', $request->getPath());
        $this->assertEquals('GET', $request->getMethod());
        $this->assertInternalType('array', $request->getBody());
    }

    /**
     * Test an URL without the index.php part
     *
     * @return void
     */
    public function testNoIndex()
    {
        //Without Trailing Slash
        /*$request = new Request('http://backend-php.net', 'GET');
        $this->assertEquals(
            'http://backend-php.net', $request->getUrl()
        );

        //With Trailing Slash
        $request = new Request('http://backend-php.net/', 'GET');
        $this->assertEquals(
            'http://backend-php.net', $request->getUrl()
        );*/

        //With Filename
        $request = new Request('http://backend-php.net/index.php', 'GET');
        $this->assertEquals(
            'http://backend-php.net/index.php', $request->getUrl()
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
     * Test Method getter and setter.
     *
     * @return void
     */
    public function testMethodAccessors()
    {
        //Default to GET for non CLI and non Request
        $request = new Request;
        $request->setServerInfo('argv', array());
        $request->setServerInfo('REQUEST_METHOD', null);
        $this->assertEquals('GET', $request->getMethod());

        //Default to REQUEST_METHOD for Requests
        $request = new Request;
        $request->setServerInfo('REQUEST_METHOD', 'GET');
        $this->assertEquals('GET', $request->getMethod());

        //Default to GET for CLI
        $request = new Request;
        $this->assertEquals('GET', $request->getMethod());

        //Set in CLI
        $request = new Request;
        $request->setServerInfo('argv', array('script', 'POST'));
        $this->assertEquals('POST', $request->getMethod());

        //Check that it's set correctly
        $request = new Request(null, 'post');
        $request->setServerInfo('REQUEST_METHOD', 'GET');
        $this->assertEquals('POST', $request->getMethod());
        $request->setMethod('options');
        $this->assertEquals('OPTIONS', $request->getMethod());

        //Check setting the method in the body
        $request = new Request;
        $request->setServerInfo('REQUEST_METHOD', 'GET');
        $request->setBody(array('_method' => 'put'));
        $this->assertEquals('PUT', $request->getMethod());

        //Check setting the method in the headers
        $request = new Request;
        $request->setHeader('REQUEST_METHOD', 'GET');
        $request->setHeader('METHOD_OVERRIDE', 'delete');
        $this->assertEquals('DELETE', $request->getMethod());

        //Check the default set in REQUEST_METHOD
        $request = new Request;
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
     * Data provider for testPaths.
     *
     * @return array
     */
    public function dataPaths()
    {
        return array(
            array('http://backend-php.net', '/'),
            array('http://backend-php.net/', '/'),
            array('http://backend-php.net/index.php', '/index.php'),
            array('http://backend-php.net/path.json', '/path.json'),
            array('http://backend-php.net/index.html', '/index.html'),
            array('http://backend-php.net/index.php/path', '/index.php/path'),
            array('http://backend-php.net/index.php/path.json', '/index.php/path.json'),
        );
    }

    /**
     * Test the parsing of the path.
     *
     * @return void
     * @dataProvider dataPaths
     */
    public function testPaths($url, $expected)
    {
        $request = new Request($url, 'GET');
        $this->assertEquals($expected, $request->getPath());
    }

    /**
     * Test Path getter and setter
     *
     * @return void
     */
    public function testPathAccessors()
    {
        // Clean up the path
        $request = new Request;
        $request->setPath('/somewhere/');
        $this->assertEquals('/somewhere', $request->getPath());

        // Empty path equals root path
        $request = new Request;
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
        // With php file
        $request = new Request('https://backend-php.net/index.php');
        $this->assertEquals(
            'https://backend-php.net/index.php', $request->getUrl()
        );

        // With Path
        $request = new Request('https://backend-php.net/something/else');
        $this->assertEquals(
            'https://backend-php.net/something/else', $request->getUrl()
        );

        // With php file and Path
        $request = new Request('https://backend-php.net/something/index.php/else');
        $this->assertEquals(
            'https://backend-php.net/something/index.php/else', $request->getUrl()
        );

        // With php file, Path and query
        $request = new Request('https://backend-php.net/something/index.php/else?test=this');
        $this->assertEquals(
            'https://backend-php.net/something/index.php/else', $request->getUrl()
        );
        $this->assertEquals(array('test' => 'this'), $request->getQuery());
    }

    /**
     * Test the URL Accessors.
     *
     * @return void
     */
    public function testUrlAccessors()
    {
        $request = new Request;
        $this->assertSame($request, $request->setUrl('http://backend-php.net'));
        $this->assertEquals('http://backend-php.net', $request->getUrl());
    }

    /**
     * Test the Query Accessors.
     *
     * @return void
     */
    public function testQueryAccessors()
    {
        $request = new Request;
        $this->assertSame($request, $request->setQuery('some=value'));
        $this->assertEquals(array('some' => 'value'), $request->getQuery());

        $request = new Request;
        $query = new \stdClass;
        $query->some = 'value';
        $this->assertSame($request, $request->setQuery($query));
        $this->assertEquals(array('some' => 'value'), $request->getQuery());
    }

    /**
     * Test the getMimeType method for the CLI or empty mime types.
     *
     * @return void
     */
    public function testCliOrEmptyGetMimeType()
    {
        $request = new Request;
        $request->setServerInfo('argc', 1);
        $request->setServerInfo('argv', array('/usr/bin/phpunit'));
        $this->assertEquals('cli', $request->getMimeType());

        $request = new Request;
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
        $request = new Request;
        $request->setServerInfo('request_method', 'get');
        $request->setHeader('accept', $acceptHeader);
        $this->assertEquals($expected, $request->getMimeType());

        //And check it again
        $this->assertEquals($expected, $request->getMimeType());
    }

    /**
     * Test the Mime Type Accessors.
     *
     * @return void
     */
    public function testMimeTypeAccessors()
    {
        $request = new Request;
        $this->assertSame($request, $request->setMimeType('text/html'));
        $this->assertEquals('text/html', $request->getMimeType());
    }

    /**
     * Test the getSpecifiedFormat method.
     *
     * @return void
     */
    public function testGetSpecifiedFormat()
    {
        $request = new Request;
        $request->setServerInfo('argv', array('index.php', 'GET', 'home', 'xml'));
        $this->assertEquals('xml', $request->getSpecifiedFormat());

        $request = new Request(null, null, array('format' => 'json'));
        $this->assertEquals('json', $request->getSpecifiedFormat());

        //And check it again
        $this->assertEquals('json', $request->getSpecifiedFormat());

        $request = new Request;
        $this->assertNull($request->getSpecifiedFormat());
    }

    /**
     * Test the Specified Format Accessors.
     *
     * @return void
     */
    public function testSpecifiedFormatAccessors()
    {
        $request = new Request;
        $this->assertSame($request, $request->setSpecifiedFormat('html'));
        $this->assertEquals('html', $request->getSpecifiedFormat());
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
     * Test the Extension Accessors.
     *
     * @return void
     */
    public function testExtensionAccessors()
    {
        $request = new Request;
        $this->assertSame($request, $request->setExtension('html'));
        $this->assertEquals('html', $request->getExtension());
    }

    /**
     * Test setting a server info value.
     *
     * @return void
     */
    public function testServerInfoAccessors()
    {
        $request = new Request;
        $this->assertSame($request, $request->setServerInfo('Some', 'Value'));
        $this->assertEquals('Value', $request->getServerInfo('some'));

        //argv is a special case
        $this->assertSame($request, $request->setServerInfo('argv', array('one' => 'two')));
        $this->assertEquals(array('one' => 'two'), $request->getServerInfo('argv'));

        //Check deprecated X_ values
        $this->assertSame($request, $request->setServerInfo('X_VALUE', 'XValue'));
        $this->assertEquals('XValue', $request->getServerInfo('VALUE'));
        $this->assertEquals('XValue', $request->getServerInfo('X_VALUE'));

        //Return null on failure
        $this->assertNull($request->getServerInfo('random_value'));
    }

    /**
     * Test building headers.
     *
     * @return void
     */
    public function testBuildHeaders()
    {
        $request = new Request;
        $request->setServerInfo('HTTP_HOST', 'backend-php.net');
        $this->assertSame($request, $request->buildHeaders(true));
        $this->assertEquals(array('Host: backend-php.net'), $request->getHeaders());
    }

    /**
     * Test setting and getting a specific header.
     *
     * @return void
     */
    public function testHeaderAccessors()
    {
        $request = new Request;
        $this->assertSame($request, $request->setHeader('Some', 'Header'));
        $this->assertEquals('Header', $request->getHeader('Some'));

        $this->assertSame($request, $request->setHeader(null, 'Unnamed'));
        $this->assertEquals('Unnamed', $request->getHeader(0));
    }

    /**
     * Test setting and getting the headers.
     *
     * @return void
     */
    public function testHeadersAccessors()
    {
        $headers = array(
            'Some' => 'Header',
        );
        $request = new Request;
        $this->assertSame($request, $request->setHeaders($headers));
        $this->assertEquals(array('Some: Header'), $request->getHeaders());
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
        // Test json body
        $result[] = array(
            'application/json', dirname(__FILE__) . '/auxiliary/payload.json',
        );
        $result[] = array(
            'text/json', dirname(__FILE__) . '/auxiliary/payload.json',
        );
        $result[] = array(
            'text/javascript', dirname(__FILE__) . '/auxiliary/payload.json',
        );
        // Test www form body
        $result[] = array(
            'application/x-www-form-urlencoded',
            dirname(__FILE__) . '/auxiliary/payload.txt',
        );
        $result[] = array(
            'text/plain', dirname(__FILE__) . '/auxiliary/payload.txt',
        );
        // Test xml body
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
        $request = new Request;
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
        $request = new Request;
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
        $request = new Request;
        $request->parseContent('unknown/content');
    }

    /**
     * Data provider for testBody.
     *
     * @return array
     */
    public function dataBody()
    {
        $result = array();
        $body = new \StdClass();
        $body->var = 'value';
        $result[] = array($body);
        $result[] = array('var=value');
        $result[] = array(array('var' => 'value'));

        return $result;
    }

    /**
     * Test setting the body.
     *
     * @param mixed $body The body to test.
     *
     * @dataProvider dataBody
     * @return void
     */
    public function testBody($body)
    {
        $request = new Request(null, null, $body);
        $this->assertEquals(array('var' => 'value'), $request->getBody());
    }

    /**
     * Test getting the body.
     *
     * @return void
     */
    public function testGetBody()
    {
        // Test empty body
        $request = new Request(null, 'delete');
        $this->assertEquals(array(), $request->getBody());

        // Test GET and POST/PUT
        $request = new Request(null, 'get');
        $old = $_GET;
        $_GET['one'] = 'two';
        $this->assertEquals($_GET, $request->getBody());
        $_GET = $old;

        $request = new Request(null, 'post');
        $old = $_POST;
        $_POST['one'] = 'two';
        $this->assertEquals($_POST, $request->getBody());

        $request = new Request(null, 'put');
        $this->assertEquals($_POST, $request->getBody());
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
        $this->assertEquals(array('var' => 'value'), $request->getBody());
    }

    /**
     * Test getting the body from the CLI
     *
     * @return void
     */
    public function testGetBodyFromCli()
    {
        $request = new Request;
        $argv = $request->getServerInfo('argv');
        $argv = array_pad($argv, 5, null);
        $argv[4] = 'var=value';
        $request->setServerInfo('argv', $argv);
        $this->assertEquals(array('var' => 'value'), $request->getBody());

        $request = new Request;
        $argv = $request->getServerInfo('argv');
        $argv = array_pad($argv, 5, null);
        $argv[4] = 'jannie verjaar';
        $this->assertEquals(array(), $request->getBody());
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
     * Test the Input Strean setter and getter.
     *
     * @return void
     */
    public function testInputStreamAccessors()
    {
        $request = new Request;

        $this->assertSame($request, $request->setInputStream('somestream://'));
        $this->assertEquals('somestream://', $request->getInputStream());
    }
}
