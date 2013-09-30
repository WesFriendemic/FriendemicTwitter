<?php

namespace Wes\Model;

use Wes\Logger;
use Wes\Db\Db;
use Wes\Config\Config;

/*
 * Almost like an ultra simplified ORM base class. Generates inserts, updates, selects from
 * the static $dbFields, $primaryKey arrays.
 *
 * Overkill for the problem at hand? Yeah, probably. But I have some time to kill. And I may
 * want to plug more stuff into this at some point, good portfolio fodder.
 */
abstract class ModelBase {

    /* Cool to intuit this using reflection and compile the classes into
     * proxies (Doctrine-ish), but again, out of scope. Going for convenience
     * here.
     *
     * Currently presumes that the ID is provided, rather than potentially
     * being generated in the DB (auto-increment or similar)
     *
     * Woo hoo for late static binding!
     */
    protected static $dbFields;
    protected static $primaryKey;
    protected static $table;

    protected $parserClass;

    protected $selectQuery;
    protected $insertQuery;
    protected $updateQuery;

    public function __construct() { }

    /*
     * Not actually from, like, a JSON string, but from a StdObject parsed by
     * json_decode
     */
    public function ParseFromJson($json) {
        $parserClass = $this->parserClass;
        return $parserClass::ParseFromJsonObj($json, $this);
    }

    /*
     * Used to distinguish between an update and an insert
     */
    protected function PrepareSelectQuery(\PDO $db, $allFields=false, $whereFields=null, $limit=0, $offset=0) {
        if($whereFields === null) $whereFields = static::$primaryKey;
        $table = static::$table;

        $fieldClause = implode(",", array_map(function($field) {
            return "`$field`";
        }, $allFields ? static::$dbFields : static::$primaryKey));

        $whereClause = implode(" and ", array_map(function($field) {
            return "`$field` = :$field";
        }, $whereFields));

        if($limit !== 0) {
            $limit = (int)$limit;
            $offset = (int)$offset;

            $limitClause = " LIMIT $offset,$limit";
        } else $limitClause = '';

        $query = "select $fieldClause from $table";
        if(!empty($whereClause)) {
            $query .= " where $whereClause ";
        }
        $query .= $limitClause;

        Logger::debug("Prepared this select: $query\n");
        return $db->prepare($query);
    }

    protected function PrepareUpdateQuery(\PDO $db) {
        $table = static::$table;
        $keyFields = static::$primaryKey;

        $fields = static::$dbFields;

        $query = "update $table set ";
        $fieldClause = implode(",\n", array_map(function($field) {
            return "`$field` = :$field";
        }, $fields));

        $whereClause = implode(" and ", array_map(function($field) {
            return "`$field` = :$field";
        }, $keyFields));

        $whereClause = " where " . $whereClause;
        Logger::info("prepared update query with fields " . print_r($fields, true));

        return $db->prepare($query . $fieldClause . $whereClause);
    }

    protected static function PrepareInsertQuery(\PDO $db) {
        $table = static::$table;
        $fields = static::$dbFields;

        $query = "insert into $table (";
        $fieldClause = implode(",", array_map(function($field) {
            return "`$field`";
        }, $fields));
        $query .= $fieldClause;
        $query .= ") VALUES (";

        $fieldClause = implode(",", array_map(function($field) {
            return ":$field";
        }, $fields));

        return $db->prepare($query . $fieldClause . ")");
    }

    /*
     * Convenience method to turn an object into params for a DB call.
     * Parses proper DateTime objects, and converts to UTC time (time
     * handling is going to be important for the graphing)
     *
     * @param $arr array Simple array of field names
     * @param $obj Object to pull the field values from
     *
     * @return array Associative array of field names => values
     */
    protected function GetFields($arr, $obj) {
        $fields = array();
        foreach($arr as $field) {
            if(isset($obj->$field)) {
                $value = $obj->$field;
                if($value instanceof \DateTime) {
                    $value->setTimezone(Config::GetDefaultTimezone());
                    $value = $value->format('Y-m-d H:i:s');
                }
            } else $value = '';
            $fields[$field] = $value;
        }

        return $fields;
    }

    public function Upsert(\PDO $db, $obj, $selectQuery=null, $insertQuery=null, $updateQuery=null) {
        $selectQuery = ($selectQuery === null ? $this->PrepareSelectQuery($db) : $selectQuery);
        $insertQuery = ($insertQuery === null ? $this->PrepareInsertQuery($db) : $insertQuery);
        $updateQuery = ($updateQuery === null ? $this->PrepareUpdateQuery($db) : $updateQuery);

        $constructed = $this->parseFromJson($obj);
        $keyFields = $this->GetFields(static::$primaryKey, $constructed);
        $fullFields = $this->GetFields(static::$dbFields, $constructed);

        if(!$selectQuery->execute($keyFields)) {
            Logger::error("Select query failed: " . print_r($selectQuery->errorInfo(), true));
            return;
        }

        if($selectQuery->fetchColumn()) {
            if(!$updateQuery->execute($fullFields)) {
                $failed++;
                Logger::error("Update query failed: " . print_r($updateQuery->errorInfo(), true));
                Logger::error("Row: " . print_r($fullFields, true));
                return;
            }
        } else {
            if(!$insertQuery->execute($fullFields)) {
                $failed++;
                Logger::error("Insert query failed: " . print_r($insertQuery->errorInfo(), true));
                Logger::error("Row: " . print_r($fullFields, true));

                return;
            }
        }
    }

    /*
     * Generically insert or update a bunch of these models to the DB
     *
     * @param $db PDO Open connection to the database
     */
    public function BatchUpsert($objs) {
        $db = Db::GetInstance();
        $selectQuery = $this->PrepareSelectQuery($db);
        $insertQuery = $this->PrepareInsertQuery($db);
        $updateQuery = $this->PrepareUpdateQuery($db);

        foreach($objs as $obj) {
            $this->Upsert($db, $obj, $selectQuery, $insertQuery, $updateQuery);
        }
    }

    protected static function ParseFromRow($row) {
        $class = get_called_class();
        $obj = new $class();

        foreach(static::$dbFields as $field) {
            $obj->$field = $row[$field];
        }

        return $obj;
    }

    public static function Get($idValues) {
        $db = Db::GetInstance();
        $selectQuery = static::PrepareSelectQuery($db, true);
        $selectQuery->execute($idValues);

        $row = $selectQuery->fetch(\PDO::FETCH_ASSOC);
        if(empty($row)) return null;

        return static::ParseFromRow($row);
    }

    public static function GetBy($params, $limit=10, $offset=0) {
        $db = Db::GetInstance();
        $fields = array_keys($params);
        $selectQuery = static::PrepareSelectQuery($db, true, $fields, $limit, $offset);

        $selectQuery->execute($params);

        $objs = array();
        while($row = $selectQuery->fetch(\PDO::FETCH_ASSOC)) {
            $objs[] = static::ParseFromRow($row);
        }

        return $objs;
    }
}
