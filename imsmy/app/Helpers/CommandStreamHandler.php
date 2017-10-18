<?php

namespace App\Helpers;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Use commands to log into separate files
 *
 * @author Peter Feher
 */
class CommandStreamHandler extends StreamHandler
{
    /**
     * Channel name
     *
     * @var String
     */
    protected $command;

    /**
     * @param String $command Channel name to write
     * @see parent __construct for params
     */
    public function __construct($command, $stream, $level = Logger::DEBUG, $bubble = true, $filePermission = null, $useLocking = false)
    {
        $this->command = $command;

        parent::__construct($stream, $level, $bubble);
    }

    /**
     * When to handle the log record.
     *
     * @param array $record
     * @return type
     */
    public function isHandling(array $record)
    {
        //Handle if Level high enough to be handled (default mechanism)
        //AND command MATCHING!
        if( isset($record['command']) ){
            return (
                $record['level'] >= $this->level &&
                $record['command'] == $this->command
            );
        } else {
            return (
                $record['level'] >= $this->level
            );
        }
    }

}