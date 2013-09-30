<?php

namespace Wes\Model;

use Wes\Logger;
use Wes\Config\Config;

/*
 * This uses some moderate abuses of dynamic object fields (like $thing->{$some_string}),
 * but that comes about because the TwitterOauth library doesn't pass 'true' into
 * json_decode, so it gives us a StdObject instead of a more rational associative
 * array. I'd rather not change it in the library, to make deployment simpler
 * (and to avoid forking it), so that's what we get.
 */
abstract class ParserBase {
    /* Simple string: just pull the field with that name out of the root of the
     * parsed JSON object
     *
     * Sub array:
     *  'type': 'string', 'int', 'datetime'
     *  'field':
     *      Simple string: pull the field with that name into the object field named after the key
     *      Array: descend according to the array; e.g., array('user', 'id_str')
     *
     * This should likely be pulled into a separate class from the DB model. But, this works for now.
     *
     * And yes, this is a fairly egregious abuse of PHP arrays.
     */
    protected static $parseFields;

    protected static function ParseDateTime($value) {
        try {
            $dt = new \DateTime($value);
            $dt->setTimezone(Config::GetDefaultTimezone());
            return $dt;
        } catch(\Exception $ex) {
            Logger::fatal($ex->getMessage());
            return null;
        }
    }

    protected static function ParseValue($type, $value) {
        switch($type) {
            case 'datetime':
                return self::ParseDateTime($value);
            case 'int':
                return (int)$value;
            default:
                Logger::fatal('invalid type in ParseValue: ' . $type);
                return $value;
        }
    }

    protected static function ResolveDescent($obj, $descent) {
        $current = $obj;
        for($i = 0; $i < count($descent); $i++) {
            if(!isset($current->{$descent[$i]})) {
                return null;
            }
            $current = $current->{$descent[$i]};
        }
        return $current;
    }

    protected static function GetValue($obj, $key) {
        return isset($obj->$key) ? $obj->$key : null;
    }

    protected static function ResolveComplex($obj, $spec, $fieldName, $seed) {
        $resolvedVal = null;
        if(isset($spec['field'])) {
           if(is_array($spec['field'])) {
               $resolvedVal = static::ResolveDescent($obj, $spec['field']);
           } else {
               //echo "spec: " . print_r($spec, true) . "\n";
               $resolvedVal = self::GetValue($obj, $spec['field']);
           }
        }
        if(isset($spec['type'])) {
            if($spec['type'] !== 'virtual') {
                $resolvedVal = static::ParseValue($spec['type'], $resolvedVal);
            } else {
                $resolvedVal = $seed->$fieldName;
            }
        }

        return $resolvedVal;
    }

    public static function ParseFromJsonObj($json, $seed) {
        foreach(static::$parseFields as $key => $value) {
            // Integer key means simple array element
            if(is_int($key)) {
                $seed->$value = self::GetValue($json, $value);
                continue;
            }

            $fieldName = $key;

            if(is_string($value)) {
                $seed->$fieldName = self::GetValue($json, $value);
                continue;
            }

            if(is_array($value)) {
                $seed->$fieldName = static::ResolveComplex($json, $value, $fieldName, $seed);
            }
        }
        return $seed;
    }

}
