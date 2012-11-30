<?php
/**
 * File defining \Backend\Core\Tests\Utilities\RequestContextTest
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

use Backend\Core\Utilities\RequestContext;

/**
 * Class to test the \Backend\Core\Utilities\RequestContext class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class RequestContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the class constructor and default values.
     *
     * @return void
     */
    public function testConstructor()
    {
        $request = $this->getMockForAbstractClass('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue(''));
        $context = new RequestContext($request);

        $this->assertEquals('http', $context->getScheme());
        $this->assertEquals(gethostname(), $context->getHost());
        $this->assertEquals('', $context->getFolder());
        $this->assertEquals('http://' . gethostname(), $context->getLink());
    }

    /**
     * Test the accessors.
     *
     * @return void
     */
    public function testAccessors()
    {
        $request = $this->getMockForAbstractClass('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue('https://backend-php.net/subfolder/index.php'));
        $context = new RequestContext($request);

        $this->assertEquals('https', $context->getScheme());
        $this->assertEquals('backend-php.net', $context->getHost());
        $this->assertEquals('/subfolder', $context->getFolder());
        $this->assertEquals('https://backend-php.net/subfolder', $context->getLink());
    }

    /**
     * Test an invalid URL.
     *
     * @return void
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unparsable URL Requested
     */
    public function testInvalidUrl()
    {
        $request = $this->getMockForAbstractClass('\Backend\Interfaces\RequestInterface');
        $request
            ->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue("http:///example"));
        $context = new RequestContext($request);
    }
}
