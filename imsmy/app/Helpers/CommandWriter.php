<?php

namespace App\Helpers;

use Monolog\Logger;

use App\Helpers\CommandStreamHandler;

class CommandWriter
{

    /**
     * The Log commands.
     *
     * @var array
     */
    protected $commands = [
        'event' => [
            'path' => 'logs/command.log',
            'level' => Logger::INFO
        ],
        'error' => [
            'path' => 'logs/command.log',
            'level' => Logger::ERROR
        ]
    ];

    /**
     * The Log levels.
     *
     * @var array
     */
    protected $levels = [
        'debug'     => Logger::DEBUG,
        'info'      => Logger::INFO,
        'notice'    => Logger::NOTICE,
        'warning'   => Logger::WARNING,
        'error'     => Logger::ERROR,
        'critical'  => Logger::CRITICAL,
        'alert'     => Logger::ALERT,
        'emergency' => Logger::EMERGENCY,
    ];

    public function __construct() {}

    /**
     * Write to log based on the given command and log level set
     *
     * @param type $command
     * @param type $message
     * @param array $context
     * @throws InvalidArgumentException
     */
//    public function writeLog($command, $level, $message, array $context = [])
//    {
//        //check command exist
//        if( !in_array($command, array_keys($this->commands)) ){
//            throw new \InvalidArgumentException('Invalid command used.');
//        }
//
//        //lazy load logger
//        if( !isset($this->commands[$command]['_instance']) ){
//            //create instance
//            $this->commands[$command]['_instance'] = new Logger($command);
//            //add custom handler
//            $this->commands[$command]['_instance']->pushHandler(
//                new CommandStreamHandler(
//                    $command,
//                    storage_path() .'/'. $this->commands[$command]['path'],
//                    $this->commands[$command]['level']
//                )
//            );
//        }
//
//        //write out record
//        $this->commands[$command]['_instance']->{$level}($message, $context);
//    }
    public function writeLog($command, $level, $message, array $context = [])
    {
        //check command exist
        if( !in_array($command, array_keys($this->commands)) ){
            throw new \InvalidArgumentException('Invalid command used.');
        }

        //lazy load logger
        if( !isset($this->commands[$command]['_instance']) ){
            //create instance
            $this->commands[$command]['_instance'] = new Logger($command);
            //add custom handler
            $this->commands[$command]['_instance']->pushHandler(
                new CommandStreamHandler(
                    $command,
                    storage_path() .'/'. 'logs/command-'.date('Y-m').'.log', // 按月进行切割日志
                    $this->commands[$command]['level']
                )
            );
        }

        //write out record
        $this->commands[$command]['_instance']->{$level}($message, $context);
    }

    public function write($command, $message, array $context = []){
        //get method name for the associated level
        $level = array_flip( $this->levels )[$this->commands[$command]['level']];
        //write to log
        $this->writeLog($command, $level, $message, $context);
    }

    //alert('event','Message');
    function __call($func, $params){
        if(in_array($func, array_keys($this->levels))){
            return $this->writeLog($params[0], $func, $params[1]);
        }
    }

}