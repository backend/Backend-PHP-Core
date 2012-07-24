<?php
/**
 * File defining TestContainer.
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Auxiliary
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */

/**
 * Class to test the autoloading of classes in the include path and other controller
 * related functions.
 *
 * @category   Backend
 * @package    CoreTests
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class TestContainer
{
    public $param = array();

    public $container;

    public static function factory($container, $param)
    {
        return new static($container, $param);
    }

    public function __construct($container, $param)
    {
        $this->container = $container;
        $this->param = array($param);
    }

    public function addParam($param)
    {
        $this->param[] = $param;
    }
}
