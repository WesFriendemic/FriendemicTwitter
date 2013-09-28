<?php
require(__DIR__ . '/../vendor/autoload.php');

use Wes\Config\Config;
use Wes\Twitter\TwitterUtil;
use Wes\Db\Db;
use Wes\Twitter\Tweet;

$config = Config::GetConfig(__DIR__ . '/config.php');
$dbConf = $config['db'];
$db = Db::GetInstance($dbConf['host'], $dbConf['user'], $dbConf['pass'], $dbConf['name']);

$tConf = $config['twitter'];

$t = new TwitterUtil($tConf['consumer_key'], $tConf['consumer_secret'], $tConf['oauth_token'], $tConf['oauth_token_secret']);
$response = $t->Search('#BreakingBadMarathon');

$tweets = array();

echo "tweets received, parsing\n";

$insertObj = new Tweet();
die(print_r($insertObj->BatchUpsert($db, $response->statuses), true));

die(print_r($tweets, true));

