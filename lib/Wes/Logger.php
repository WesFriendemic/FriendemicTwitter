<?php
namespace Wes;

class Logger {
    const FATAL = 0;
    const ERROR = 1;
    const WARNING = 2;
    const INFO = 3;
    const DEBUG = 4;

    protected static $LogWriter;

    protected static $level = self::INFO;

    public static function SetLevel($level) {
        self::$level = $level;
    }

    public static function fatal($message) {
        self::log($message, self::FATAL);
    }

    public static function error($message) {
        self::log($message, self::ERROR);
    }

    public static function warning($message) {
        self::log($message, self::WARNING);
    }

    public static function info($message) {
        self::log($message, self::INFO);
    }

    public static function debug($message) {
        self::log($message, self::DEBUG);
    }

    public static function SetWriter($writer) {
        if(!($writer instanceof LogWriter)) {
            throw new \Exception("Invalid type passed to Logger::SetWriter");
        }

        self::$LogWriter = $writer;
    }

    public static function log($message, $level = self::FATAL) {
        if($level <= self::$level) {
            if(!self::$LogWriter) {
                self::$LogWriter = new ErrorLogWriter();
            }

            self::$LogWriter->write($message);
        }
    }
}

interface LogWriter {
    public function write($message);
}

class EchoWriter implements LogWriter {
    public function write($message) {
        echo $message . "\n";
    }
}

class PreWriter implements LogWriter {
    public function write($message) {
        echo "<pre>" . $message . "</pre>";
    }

}

class ErrorLogWriter implements LogWriter {
    public function write($message) {
        error_log($message);
    }
}


