<?php
namespace Wes\Twitter;

use Wes\Model\ModelBase;
use Wes\Db\Db;

class Tweet extends ModelBase {
    public $text;
    public $id;
    public $created_at;
    public $retweeted;
    public $retweet_count;

    /* User Details. Should probably be normalized out, not worth it at this stage */

    public $user_id;
    public $user_name;
    public $user_profile_image_url;

    protected $parserClass = "Wes\Twitter\TweetParser";

    protected static $table = 'tweets';

    protected static $dbFields = array(
        'text',
        'id',
        'created_at',
        'retweeted',
        'retweet_count',
        'user_id',
        'user_name',
        'user_profile_image_url',
    );

    protected static $primaryKey = array('id');

    public static function ParseFromJsonObj($json, $seed) {
        $obj = parent::ParseFromJsonObj($json, $seed);
        $obj->query = $this->query;
        return $obj;
    }

    public static function GetByQuery($query) {
        $db = Db::GetInstance();
        $queryString =
            "select t.*, tq.query from tweets t inner join tweets_queries tq on " .
            "t.id = tq.tweet_id " .
            "where tq.query = :query";

        $stmt = $db->prepare($queryString);
        echo "executing query $queryString with query $query\n";
        if(!$stmt->execute(array('query' => $query))) {
            Logger::fatal("Error while executing select: " . print_r($stmt->errorInfo(), true));
            return array();
        }

        $objs = array();

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            echo "got a row\n";
            $objs[] = static::ParseFromRow($row);
        }

        return $objs;
    }
}
