<?php

use Wes\Twitter\Tweet;
use Wes\Twitter\TweetQuery;
use Wes\Twitter\TweetSearch;
use Wes\Logger;

class ApiController {
    public function GetTweets() {
        if(empty($_REQUEST['query'])) {
            $this->SendJson(array(
                'error' => 'Missing required query'
            ));
        }

        if(empty($_REQUEST['tz_offset'])) {
            $this->SendJson(array(
                'error' => "Missing required tz_offset"
            ));
        }
        $query = $_REQUEST['query'];
        $tzOffset = -1*$_REQUEST['tz_offset'];

        $dbQuery = TweetQuery::Get(array('query' => $query));

        $ts = new TweetSearch();
        $result = $ts->RunSearch($query);

        $tweets = Tweet::GetByQuery($_REQUEST['query']);
        $this->SendJson(array(
            'query' => $query,
            'tweets' => $tweets,
            'distribution' => Tweet::GetTweetDistribution($tweets, $tzOffset)
        ));
    }

    public function GetQueries() {
        $queries = TweetQuery::GetBy(array(), 50);

        $this->SendJson(array(
            'queries' => $queries
        ));
    }

    protected function SendJson($obj) {
        ob_clean();
        echo json_encode($obj);
        die();
    }
}
