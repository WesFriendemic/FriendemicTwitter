<?php

namespace Wes\Db;

use Wes\Logger;

class Db {
    private static $db;

    public static function GetInstance($dbHost=null, $dbUser=null, $dbPass=null, $dbName=null) {
        if(self::$db instanceof \PDO) return self::$db;

        try {
            $db = new \PDO(self::getDsn($dbName, $dbHost), $dbUser, $dbPass, array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            self::$db = $db;
            return $db;
        } catch(\PDOException $ex) {
            Logger::fatal("Error while connecting to database: " . $ex->getMessage());
        }
    }

    private static function getDsn($dbName, $dbHost) {
        return "mysql:dbname=$dbName;host=$dbHost";
    }
}
