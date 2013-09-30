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
        $db = Db::GetInstance();

        $insertObj = new Tweet();
        $insertObj->BatchUpsert($response->statuses);

        $queryObj = new TweetQuery($query, $now);
        $queryObj->Upsert($db, $queryObj);

        $relObj = new TweetQueryTweet($query);
        $relObj->BatchUpsert($response->statuses);

        return true;
    }

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
