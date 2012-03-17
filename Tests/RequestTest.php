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
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testNoIndex()
    {
        $request = new Request('http://backend-php.net/', 'GET');
        $this->assertEquals('/', $request->getQuery());
        $this->assertEquals('http://backend-php.net/index.php/', $request->getSiteUrl());
    }

    public function testEmptyQuery()
    {
        $request = new Request('http://backend-php.net/index.php', 'GET');
        $this->assertEquals('/', $request->getQuery());
    }

    public function testSimpleQuery()
    {
        $request = new Request('http://backend-php.net/index.php/something', 'GET');
        $this->assertEquals('/something', $request->getQuery());
    }

    public function testSiteURl()
    {
        $request = new Request('http://backend-php.net/index.php/something/else', 'POST');
        $this->assertEquals('http://backend-php.net/index.php/', $request->getSiteUrl());
    }

    public function testCustomPort()
    {
        $request = new Request('http://backend-php.net:8080/index.php/something');
        $serverInfo = $request->getServerInfo();
        $this->assertEquals(8080, $serverInfo['SERVER_PORT']);
    }

    public function testSitePath()
    {
        //TODO
    }
}
