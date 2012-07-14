<?php
/**
 * File defining \Backend\Core\Tests\Utilities\CallbackFactoryTest
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
use \Backend\Core\Utilities\CallbackFactory;
use \Backend\Interfaces\CallbackFactoryInterface;
/**
 * Class to test the \Backend\Core\Utilities\CallbackFactory class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class CallbackFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the fromString method
     *
     * @return void
     */
    public function testFromString()
    {
        $callback = CallbackFactory::fromString('preg_match');
        $this->assertEquals('preg_match', (string)$callback);

        $callback = CallbackFactory::fromString('DateInterval::createFromDateString');
        $this->assertEquals('DateInterval::createFromDateString', (string)$callback);
    }

    /**
     * Test for invalid callbacks
     *
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Invalid callback string: some::invalid::callback
     */
    public function testInvalidCallback()
    {
        $callback = CallbackFactory::fromString('some::invalid::callback');
    }
}