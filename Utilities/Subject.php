<?php
/**
 * File defining Subject
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
use Backend\Core\Utilities\ServiceLocator;
/**
 * Base Subject (as in Observer / Subject) class
 *
 * @category   Backend
 * @package    Core
 * @subpackage Utilities
 * @author     J Jurgens du Toit <jrgns@jrgns.net>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @link       http://backend-php.net
 */
class Subject implements \SplSubject
{
    /**
     * @var array A set of observers for the subject
     */
    protected $observers = array();

    /**
     * Check the config for configured observers
     *
     * An observer is only eligible if it implements \SplObserver, and can be
     * retrieved using the Service Locator
     *
     * @param \Backend\Utilities\Config $config The config file to check for observers
     *
     * @return boolean If the various subjects where checked or not
     */
    public function __construct(Config $config = null)
    {
        if (is_null($config)) {
            if (!ServiceLocator::has('backend.Config')) {
                return false;
            }
            $config = ServiceLocator::get('backend.Config');
        }

        //Attach Observers to Subjects
        $config = $config->get('subjects', get_class($this));
        if (!empty($config['observers'])) {
            foreach ($config['observers'] as $observerName) {
                $observer = ServiceLocator::get($observerName);
                if ($observer instanceof \SplObserver) {
                    $this->attach($observer);
                }
            }
        }
        return true;
    }

    //SplSubject functions
    /**
     * Attach an observer to the class
     *
     * @param SplObserver $observer The observer to attach
     *
     * @return null
     */
    public function attach(\SplObserver $observer)
    {
        $hash = spl_object_hash($observer);
        $this->observers[$hash] = $observer;
    }

    /**
     * Detach an observer from the class
     *
     * @param SplObserver $observer The observer to detach
     *
     * @return null
     */
    public function detach(\SplObserver $observer)
    {
        $hash = spl_object_hash($observer);
        unset($this->observers[$hash]);
    }

    /**
     * Notify observers of an update to the class
     *
     * @return null
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    /**
     * Return all the observers for the subject
     *
     * @return array
     */
    public function getObservers()
    {
        return $this->observers;
    }
}
