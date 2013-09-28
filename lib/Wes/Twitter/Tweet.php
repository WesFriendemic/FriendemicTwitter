<?php
namespace Wes\Twitter;

use Wes\Model\ModelBase;

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
}
