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
        $query = $_REQUEST['query'];
        $dbQuery = TweetQuery::Get(array('query' => $query));

        $ts = new TweetSearch();
        $ts->RunSearch($query);

        $tweets = Tweet::GetByQuery($_REQUEST['query']);
        $this->SendJson(array(
            'query' => $query,
            'tweets' => $tweets,
            'distribution' => Tweet::GetTweetDistribution($tweets)
        ));
    }

    public function GetQueries() {
        $queries = TweetQuery::GetBy(array());

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
