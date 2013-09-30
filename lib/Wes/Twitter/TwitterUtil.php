<?php

namespace Wes\Twitter;

use Wes\Logger;

/*
 * An incredibly stripped down implementation of a Twitter client.
 *
 * As in, only supports the Search API, and only supports the q, count, and 
 * result_type parameters.
 */
class TwitterUtil {
    /*protected $consumerKey;
    protected $consumerSecret;
    protected $oauthToken;
    protected $oauthSecret;*/

    protected $twitterOauth;

    public function __construct($consumerKey, $consumerSecret, $oauthToken, $oauthSecret) {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->oauthToken = $oauthToken;
        $this->oauthSecret = $oauthSecret;
        $this->twitterOauth = new \TwitterOauth(
            $consumerKey,
            $consumerSecret,
            $oauthToken,
            $oauthSecret
        );
        $this->twitterOauth->useAPIVersion("1.1");

    }

    public function Search($q, $count=100, $result_type='mixed') {
        $params = array(
            'q' => $q,
            'count' => $count,
            'result_type' => $result_type
        );
        $url = "search/tweets";
        $response = $this->twitterOauth->get($url, $params);
        Logger::info("Response for $q: " . print_r($response, true));
        return $response;
    }
}
