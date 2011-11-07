<?php
namespace Backend\Base\Utilities;
require_once('Log.php');
class PearLogger implements \Backend\Core\Interfaces\LoggingObserver
{
    protected $_logger;

    public function __construct()
    {
        $this->_logger = \Log::factory('file', '/tmp/out.log', 'TEST');
    }

    public function update(\SplSubject $message)
    {
        switch ($message->getLevel()) {
            case $message::LEVEL_CRITICAL:    $level = \PEAR_LOG_EMERG;      break;
            case $message::LEVEL_WARNING:     $level = \PEAR_LOG_CRIT;       break;
            case $message::LEVEL_IMPORTANT:   $level = \PEAR_LOG_WARNING;    break;
            case $message::LEVEL_DEBUGGING:   $level = \PEAR_LOG_DEBUG;      break;
            case $message::LEVEL_INFORMATION: $level = \PEAR_LOG_INFO;       break;
            default:                          $level = $message->getLevel(); break;
        }
        $this->_logger->log($message->getMessage(), $level);
    }
}
