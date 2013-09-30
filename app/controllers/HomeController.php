<?php

use Wes\Twitter\TweetQueryTweet;
use Wes\Twitter\TweetSearch;
use Wes\Twitter\TweetQuery;
use Wes\Twitter\Tweet;
use Wes\Twitter\TwitterUtil;
use Wes\Config\Config;
use Wes\Db\Db;
use Wes\Logger;

class HomeController {
    protected $title;

    public function index() {
        global $twig;
        $this->title = "Home";

        echo $twig->render('home/home.twig', $this->GetViewObj());
    }

    protected function GetViewObj($additional=array()) {
        $query = isset($_REQUEST['query']) ? $_REQUEST['query'] : null;
        $queries = TweetQuery::GetBy(array());

        $default = array(
            'queries' => TweetQuery::GetBy(array()),
            'activeQuery' => isset($_REQUEST['query']) ? urldecode($_REQUEST['query']) : '',
            'title' => $this->title
        );

        return array_merge($default, $additional);
    }

    public function run_query() {
        Logger::SetWriter(new Wes\PreWriter());
        if(!isset($_REQUEST['query'])) {
            die('no query');
        }

        $query = $_REQUEST['query'];

        $ts = new TweetSearch();
        $ts->RunSearch($query, true);
    }
}
