<?php
namespace Wes\Twitter;

use Wes\Model\ModelBase;

class TweetQuery extends ModelBase {
    public $id;
    public $query;

    protected static $table = 'queries';

    protected static $dbFields = array(
        'query'
    );

    protected static $autoIncrement = true;
    protected static $primaryKey = array('query');

    public function __construct($query) {
        echo "setting query to " . $query . "\n";
        $this->query = $query;
    }

    public function ParseFromJson($json) {
        $obj = new \stdClass();
        $obj->query = $this->query;
        return $obj;
    }
}
