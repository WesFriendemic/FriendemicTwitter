<?php

namespace Wes\Twitter;

use Wes\Twitter\TweetQueryTweet;
use Wes\Twitter\TweetQuery;
use Wes\Twitter\Tweet;
use Wes\Twitter\TwitterUtil;
use Wes\Config\Config;
use Wes\Db\Db;
use Wes\Logger;

class TweetSearch {
    /*
     * Run a query on Twitter. Inserts the retrived tweets into the database.
     *
     * @param string query Query to run on Twitter
     * @param bool debounce Whether we should limit identical queries, based on the 
     *  min_query_interval configuration parameter
     *
     * @return bool True on success, false on failure
     */
    public function RunSearch($query, $debounce=true) {
        $now = new \DateTime();

        if($debounce && !$this->ShouldFire($query)) {
            Logger::info('not running query, too soon');
            return false;
        }

        $config = Config::GetConfig();
        $tConf = $config['twitter'];

        $t = new TwitterUtil($tConf['consumer_key'], $tConf['consumer_secret'],
            $tConf['oauth_token'], $tConf['oauth_token_secret']);

        $response = $t->Search($query);
        if(!$response) {
            return false;
        }
        $db = Db::GetInstance();

        $insertObj = new Tweet();
        $insertObj->BatchUpsert($response->statuses);

        $queryObj = new TweetQuery($query, $now);
        $queryObj->Upsert($db, $queryObj);

        $relObj = new TweetQueryTweet($query);
        $relObj->BatchUpsert($response->statuses);

        return true;
    }

    /*
     * Basically, we only run a search if it's been greater than min_query_interval
     * seconds since the last time this query was searched, or if this is a new query.
     */
    protected function ShouldFire($query) {
        $config = Config::GetConfig();
        $minIntervalSeconds = $config['twitter']['min_query_interval'];

        $dbQuery = TweetQuery::Get(array('query' => $query));
        if(!$dbQuery) return true;

        $now = new \DateTime();
        $lastQueried = new \DateTime($dbQuery->date_queried);

        $interval = $now->getTimestamp() - $lastQueried->getTimestamp();

        return $interval > $minIntervalSeconds;
    }
}
