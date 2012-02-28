<?php
/**
 * File defining \Backend\Core\RestControllerInterface
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Interfaces
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Interfaces;
/**
 * A Controller class that provides basic REST rest functions
 *
 * @category   Backend
 * @package    Core
 * @subpackage Interfaces
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
interface RestControllerInterface extends ControllerInterface
{
    /**
     * Create function called by the POST HTTP verb
     *
     * @param mixed $identifier The identifier. Set to 0 to reference the collection
     * @param array $arguments  An array of arguments
     *
     * @return null
     * @todo Determine the actual return value
     */
    public function createAction($identifier, array $arguments = array());

    /**
     * Read function called by the GET HTTP verb
     *
     * @param mixed $identifier The identifier. Set to 0 to reference the collection
     * @param array $arguments  An array of arguments
     *
     * @return null
     * @todo Determine the actual return value
     */
    public function readAction($identifier, array $arguments = array());

    /**
     * Update function called by the PUT HTTP verb
     *
     * @param mixed $identifier The identifier. Set to 0 to reference the collection
     * @param array $arguments  An array of arguments
     *
     * @return null
     * @todo Determine the actual return value
     */
    public function updateAction($identifier, array $arguments = array());

    /**
     * Delete function called by the DELETE HTTP verb
     *
     * @param mixed $identifier The identifier. Set to 0 to reference the collection
     * @param array $arguments  An array of arguments
     *
     * @return null
     * @todo Determine the actual return value
     */
    public function deleteAction($identifier, array $arguments = array());
}
