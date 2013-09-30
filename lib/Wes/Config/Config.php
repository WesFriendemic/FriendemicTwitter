<?php

namespace Wes\Config;

class Config {
    private static $config;
    private static $tz;

    public static function GetConfig($location=null) {
        if(!$location && self::$config === null) {
            throw new \Exception("Config not initialized, and no location provided");
        }

        if(!self::$config) {
            self::$config = require($location);
        }

        return self::$config;
    }

    public static function GetDefaultTimezone() {
        if(!self::$tz) {
            self::$tz = new \DateTimeZone(date_default_timezone_get());
        }

        return self::$tz;
    }
}
