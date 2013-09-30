<?php

use Wes\Twitter\TweetQuery;
use Wes\Logger;

class HomeController {
    protected $title;

    public function index() {
        global $twig;
        $this->title = "Tweet Search";

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

    public function about() {
        global $twig;
        $this->title = "About";
        echo $twig->render('home/about.twig', $this->GetViewObj());
    }
}
