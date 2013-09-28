<?php
require(__DIR__ . '/../vendor/autoload.php');

use Wes\Config\Config;
use Wes\Twitter\TwitterUtil;
use Wes\Db\Db;
use Wes\Twitter\Tweet;
use Wes\Twitter\TweetQuery;
use Wes\Twitter\TweetQueryTweet;

$config = Config::GetConfig(__DIR__ . '/config.php');
$dbConf = $config['db'];
$db = Db::GetInstance($dbConf['host'], $dbConf['user'], $dbConf['pass'], $dbConf['name']);

$tConf = $config['twitter'];

$t = new TwitterUtil($tConf['consumer_key'], $tConf['consumer_secret'], $tConf['oauth_token'], $tConf['oauth_token_secret']);

$query = '#BreakingBadMarathon';
$response = $t->Search($query);

$tweets = array();

echo "tweets received, parsing\n";

$insertObj = new Tweet();
$insertObj->BatchUpsert($db, $response->statuses);

$queryObj = new TweetQuery($query);
$queryObj->Upsert($db, $queryObj);

$relObj = new TweetQueryTweet($query);
$relObj->BatchUpsert($db, $response->statuses);

