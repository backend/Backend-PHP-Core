<?php
/**
 * File defining Observable
 *
 * PHP Version 5.3
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@backend-php.net>
 * @copyright  2011 - 2012 Jade IT (cc)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
namespace Backend\Core\Utilities;
/**
 * Class to automatically attach observers to subjects
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class Observable
{
    /**
     * Check for observers for the given subject
     *
     * An observer is only eligible if it implements \SplObserver, and can be
     * retrieved using \Backend\Core\getTool
     *
     * @param SplSubject $subject The subject that should be checked
     *
     * @return boolean If the various subjects where checked or not
     */
    public static function execute(\SplSubject $subject)
    {
        $config = \Backend\Core\Application::getTool('Config');
        if (!$config) {
            return false;
        }
        //Attach Observers to Subjects
        $config = $config->get('subjects', get_class($subject));
        if (!empty($config['observers'])) {
            foreach ($config['observers'] as $observerName) {
                $observer = \Backend\Core\Application::getTool($observerName);
                if ($observer instanceof \SplObserver) {
                    $subject->attach($observer);
                }
            }
        }
        return true;
    }
}
