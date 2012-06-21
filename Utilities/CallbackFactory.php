<?php
/**
 * File defining Backend\Core\Utilities\CallbackFactory.
 *
 * PHP Version 5.3
 *
 * @category  Backend
 * @package   Interfaces
 * @author    J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright 2011 - 2012 Jade IT (cc)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      http://backend-php.net
 */
namespace Backend\Core\Utilities;
use Backend\Modules\Callback;
use Backend\Core\Exception as CoreException;
/**
 * Class to create callbacks from strings and arrays.
 *
 * @category Backend
 * @package  Interfaces
 * @author   J Jurgens du Toit <jrgns@backend-php.net>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://backend-php.net
 */
class CallbackFactory
{
    /**
     * Convert a string to a callback.
     *
     * If the
     *
     * @param string $string    The string representation of the callback.
     * @param array  $arguments The arguments for the callback.
     *
     * @return CallbackInterface
     */
    public function fromString($string, array $arguments = array())
    {
        $arr = explode('::', $string);
        $callback = new Callback();
        if (count($arr) == 1) {
            $callback->setFunction($arr[0]);
        } else if (count($arr == 2)) {
            $callback->setClass($arr[0]);
            $callback->setMethod($arr[1]);
        } else {
            throw new CoreException('Invalid callback string: ' . $string);
        }
        $callback->setArguments($arguments);

        return $callback;
    }
}
