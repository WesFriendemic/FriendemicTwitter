<?php

namespace Wes\Config;

class Config {
    private static $config;

    public static function GetConfig($location=null) {
        if(!$location && self::$config === null) {
            throw new \Exception("Config not initialized, and no location provided");
        }

        if(!self::$config) {
            self::$config = require($location);
        }

        return self::$config;
    }
}
