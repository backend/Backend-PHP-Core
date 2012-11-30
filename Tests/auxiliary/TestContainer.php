<?php
/**
 * File defining \Backend\Core\TestContainer.
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
namespace Backend\Core;

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

    public static function factory($container)
    {
        $result = new static($container);
        if (func_num_args() > 2) {
            for ($i = 1; $i < func_num_args(); $i++) {
                $result->addParam(func_get_arg($i));
            }
        }

        return $result;
    }

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function addParam($param)
    {
        $this->param[] = $param;
    }
}
