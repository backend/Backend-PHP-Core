<?php
/**
 * File defining \Backend\Core\Tests\Utilities\CallbackTest
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
use \Backend\Core\Utilities\Callback;
/**
 * Class to test the \Backend\Core\Utilities\Callback class
 *
 * @category Backend
 * @package  CoreTests
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the Class setter and getter.
     *
     * @return void
     */
    public function testClassAccessors()
    {
        $class = __CLASS__;
        $callback = new Callback();
        $callback->setClass($class);
        $this->assertEquals($class, $callback->getClass());
        $this->assertFalse($callback->isValid());
    }

    /**
     * Test checking for invalid class type.
     *
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Invalid type for class name, string expected, got
     * object
     * @return void
     */
    public function testInvalidClassType()
    {
        $callback = new Callback();
        $callback->setClass($this);
    }

    /**
     * Test checking for a non existing class.
     *
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Trying to set non-existant class in
     * Callback: \SomeClass
     * @return void
     */
    public function testNonExistantClass()
    {
        $callback = new Callback();
        $callback->setClass('\SomeClass');
    }

    /**
     * Test the Object setter and getter.
     *
     * @return void
     */
    public function testObjectAccessors()
    {
        $callback = new Callback();
        $callback->setObject($this);
        $this->assertSame($this, $callback->getObject());
        $this->assertFalse($callback->isValid());
    }

    /**
     * Test checking for an invalid object.
     *
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Invalid type for object, object expected, got string
     * @return void
     */
    public function testInvalidObject()
    {
        $callback = new Callback();
        $callback->setObject('Class');
    }

    /**
     * Test the Method setter and getter.
     *
     * @return void
     */
    public function testMethodAccessors()
    {
        $method = 'someMethod';
        $callback = new Callback();
        $callback->setMethod($method);
        $this->assertEquals($method, $callback->getMethod());
        $this->assertFalse($callback->isValid());
    }

    /**
     * Test the Function setter and getter.
     *
     * @return void
     */
    public function testFunctionAccessors()
    {
        $function = 'preg_match';
        $callback = new Callback();
        $callback->setFunction($function);
        $this->assertEquals($function, $callback->getFunction());
        $this->assertEquals('preg_match', $callback->isValid());
    }

    /**
     * Test checking for an invalid function type.
     *
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Invalid type for function, string expected, got
     * object
     * @return void
     */
    public function testInvalidFunctionType()
    {
        $callback = new Callback();
        $callback->setFunction($this);
    }

    /**
     * Test the Arguments setter and getter.
     *
     * @return void
     */
    public function testArgumentsAccessors()
    {
        $arguments = array('one' => 'two');
        $callback = new Callback();
        $callback->setArguments($arguments);
        $this->assertEquals($arguments, $callback->getArguments());
    }

    /**
     * Data provider for testInvalidCallback
     *
     * @return array
     */
    public function dataIncompleteCallback()
    {
        $result = array();
        // Empty Everything
        $result[] = array(new Callback());
        // Empty Class and Object, Set Method
        $callback = new Callback();
        $callback->setMethod(__METHOD__);
        $result[] = array($callback);
        // Set Class, Empty Method
        $callback = new Callback();
        $callback->setClass(__CLASS__);
        $result[] = array($callback);
        // Set Object, Empty Method
        $callback = new Callback();
        $callback->setObject($this);
        $result[] = array($callback);
        return $result;
    }

    /**
     * Test checking for an incomplete callback.
     *
     * @param \Backend\Core\Utilities\Callback $callback The callback to
     * check.
     *
     * @dataProvider dataIncompleteCallback
     * @return void
     */
    public function testIncompleteCallback(Callback $callback)
    {
        $this->assertFalse($callback->isValid());
    }

    /**
     * Data provider for testInvalidCallback.
     *
     * @return array
     */
    public function dataInvalidCallback()
    {
        $result = array();
        $callback = new Callback();
        $callback->setFunction('someFunc');
        $result[] = array($callback);
        $callback = new Callback();
        $callback->setClass(__CLASS__);
        $callback->setMethod('someMethod');
        $result[] = array($callback);
        $callback = new Callback();
        $callback->setObject($this);
        $callback->setMethod('someMethod');
        $result[] = array($callback);
        return $result;
    }

    /**
     * Test checking for an invalid callback.
     *
     * @param \Backend\Core\Utilities\Callback $callback The callback to check.
     *
     * @dataProvider dataInvalidCallback
     * @return void
     * @expectedException \Backend\Core\Exception
     * @expectedExceptionMessage Unexecutable Callback
     */
    public function testInvalidCallback($callback)
    {
        $callback->isValid();
    }

    /**
     * Data provider for testValidCallback
     *
     * @return array
     */
    public function dataValidCallback()
    {
        $result = array();
        // Function
        $callback = new Callback();
        $callback->setFunction('preg_match');
        $result[] = array($callback, 'preg_match');
        // Object Method
        $callback = new Callback();
        $callback->setObject($this);
        $callback->setMethod('dataValidCallback');
        $result[] = array($callback, get_class($this) . '::dataValidCallback');
        // Class Method
        $callback = new Callback();
        $callback->setClass('\Backend\Core\Autoloader');
        $callback->setMethod('register');
        $result[] = array($callback, '\Backend\Core\Autoloader::register');

        return $result;
    }

    /**
     * Test checking for a Valid callback.
     *
     * This also tests the toString method.
     *
     * @param \Backend\Core\Utilities\Callback $callback The callback to check.
     * @param string                           $result   The string representation
     * of the callback.
     *
     * @dataProvider dataValidCallback
     * @return void
     */
    public function testValidCallback(Callback $callback, $result)
    {
        $this->assertEquals($result, $callback->isValid());
    }

}
