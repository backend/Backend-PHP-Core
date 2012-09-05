<?php
/**
 * File defining ResponseTest
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
use \Backend\Core\Response;
/**
 * Class to test the \Backend\Core\Response class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test setting and getting the status code
     *
     * @return void
     */
    public function testStatusCodeAccessors()
    {
        $statusCode = 403;
        $response = new Response();
        $response->setStatusCode($statusCode);
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    /**
     * Test getting the status text
     *
     * @return void
     */
    public function testGetStatusText()
    {
        $response = new Response('', 404);
        //Test the set code
        $this->assertEquals('Not Found', $response->getStatusText());

        //Test the passed code
        $this->assertEquals('Forbidden', $response->getStatusText(403));

        //Test an unknown code
        $this->assertEquals('Unknown Status', $response->getStatusText(600));
    }

    /**
     * Test setting and getting the body
     *
     * @return void
     */
    public function testBodyAccessors()
    {
        $body = 'Some Body';
        $response = new Response();
        $response->setBody($body);
        $this->assertEquals($body, $response->getBody());
    }

    /**
     * Test adding a header
     *
     * @return void
     */
    public function testAddHeader()
    {
        $response = new Response();
        $response->setHeaders(array());
        $response->addHeader('Header', 'Add');
        $this->assertEquals(array('Add: Header'), $response->getHeaders());

        $response->setHeaders(array());
        $response->addHeader('Add');
        $response->addHeader('Header');
        $this->assertEquals(array('Add', 'Header'), $response->getHeaders());
    }

    /**
     * Test setting and getting the headers
     *
     * @return void
     */
    public function testHeaderAccessors()
    {
        $headers = array(
            'Some' => 'Header',
        );
        $response = new Response();
        $response->setHeaders($headers);
        $this->assertEquals(array('Some: Header'), $response->getHeaders());
    }

    /**
     * Test the object constructor
     *
     * @return void
     */
    public function testConstructor()
    {
        $body = 'Some Body';
        $code = 201;
        $headers = array('Construct' => 'Header');
        $response = new Response($body, $code, $headers);
        $this->assertEquals($body, $response->getBody());
        $this->assertEquals($code, $response->getStatusCode());
        $this->assertEquals(array('Construct: Header'), $response->getHeaders());
    }

    /**
     * Test the output method.
     *
     * @return void
     */
    public function testOutput()
    {
        $response = $this->getMock(
            '\Backend\Core\Response',
            array('sendHeaders', 'sendBody')
        );
        $response
            ->expects($this->once())
            ->method('sendHeaders')
            ->will($this->returnSelf());
        $response
            ->expects($this->once())
            ->method('sendBody');
        $response->output();
    }

    /**
     * Test the sendHeaders method
     *
     * @return void
     */
    public function testSendHeaders()
    {
        if (headers_sent()) {
            return;
        }
        $response = $this->getMock(
            '\Backend\Core\Response',
            array('writeHeader')
        );
        $response
            ->expects($this->at(0))
            ->method('writeHeader')
            ->with('HTTP/1.1 200 OK');
        $response
            ->expects($this->at(1))
            ->method('writeHeader')
            ->with('Name: with');
        $response
            ->expects($this->at(2))
            ->method('writeHeader')
            ->with('Without: Name');
        $response->addHeader('with', 'Name');
        $response->addHeader('Without: Name');
        $response->sendHeaders();
    }

    /**
     * Test the check for headers already sent
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Headers already sent in
     */
    public function testHeadersAlreadySentCheck()
    {
        echo ' ';
        $response = new Response();
        $response->sendHeaders();
    }

    /**
     * Test the writeHeader method
     *
     * @return void
     */
    public function testWriteHeader()
    {
    }

    /**
     * Test the sendBody method
     *
     * @return void
     */
    public function testSendBody()
    {
        $body = 'Some Body';
        $response = new Response($body);
        ob_start();
        $response->sendBody();
        $result = ob_get_clean();
        $this->assertEquals($body, $result);
    }

    /**
     * Test the __toString method
     *
     * @return void
     */
    public function testToString()
    {
        $body = 'Some Body';
        $response = new Response($body);
        $this->assertEquals($body, (string) $response);
    }
}
