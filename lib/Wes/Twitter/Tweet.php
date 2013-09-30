<?php
namespace Wes\Twitter;

use Wes\Model\ModelBase;
use Wes\Db\Db;
use Wes\Logger;
use Wes\Stats\HistogramBins;

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
    public $user_screen_name;

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
        'user_screen_name',
    );

    protected static $primaryKey = array('id');

    public static function GetByQuery($query, $limit=100, $offset=0) {
        $db = Db::GetInstance();
        $queryString =
            "select t.*, tq.query from tweets t inner join tweets_queries tq on " .
            "t.id = tq.tweet_id " .
            "where tq.query = :query " .
            "order by t.created_at desc ";

        if($limit !== 0) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $queryString .= " LIMIT $offset, $limit";
        }

        $stmt = $db->prepare($queryString);
        Logger::info("executing query $queryString with query $query");
        if(!$stmt->execute(array('query' => $query))) {
            Logger::fatal("Error while executing select: " . print_r($stmt->errorInfo(), true));
            return array();
        }

        $objs = array();

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $objs[] = static::ParseFromRow($row);
        }

        return $objs;
    }

    public static function GetTweetDistribution($tweets, $tzOffset) {
        $timesOfDay = array();

        foreach($tweets as $tweet) {
            $dt = new \DateTime($tweet->created_at);
            $interval = new \DateInterval('PT' . abs($tzOffset) . 'H');

            if($tzOffset >= 0) {
                $dt->add($interval);
            } else {
                $dt->sub($interval);
            }

            $h = $dt->format('G');
            $m = $dt->format('i');

            $totalMinutes = $h*60+$m;

            $timesOfDay[] = $totalMinutes;
        }

        $histo = new HistogramBins($timesOfDay, false, 0, 24*60);
        return $histo->GetBins(24, 6);
    }
}
