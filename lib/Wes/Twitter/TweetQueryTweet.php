<?php
namespace Wes\Twitter;

use Wes\Model\ModelBase;

class TweetQueryTweet extends ModelBase {
    public $tweet_id;
    public $query;

    protected $parserClass = "Wes\Twitter\TweetQueryTweetParser";

    protected static $table = 'tweets_queries';

    protected static $dbFields = array(
        'tweet_id',
        'query',
    );

    protected static $primaryKey = array(
        'tweet_id',
        'query',
    );

    public function __construct($query) {
        $this->query = $query;
    }

    public function ParseFromJson($json) {
        $obj = parent::ParseFromJson($json);
        $obj->query = $this->query;
        return $obj;
    }
}
