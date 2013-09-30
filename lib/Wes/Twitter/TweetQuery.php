<?php
namespace Wes\Twitter;

use Wes\Model\ModelBase;

class TweetQuery extends ModelBase {
    public $id;
    public $query;

    protected static $table = 'queries';

    protected static $dbFields = array(
        'query',
        'date_queried'
    );

    protected static $autoIncrement = true;
    protected static $primaryKey = array('query');

    public function __construct($query=null, $date_queried=null) {
        $this->query = $query;
        $this->date_queried = $date_queried;
    }

    public function ParseFromJson($json) {
        $obj = new \stdClass();
        $obj->query = $this->query;
        $obj->date_queried = $this->date_queried;
        return $obj;
    }
}
