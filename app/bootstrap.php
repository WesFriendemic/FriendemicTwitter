<?php
require(__DIR__ . '/../vendor/autoload.php');

use Wes\Config\Config;
use Wes\Db\Db;

$config = Config::GetConfig(__DIR__ . '/config.php');
$dbConf = $config['db'];
$db = Db::GetInstance($dbConf['host'], $dbConf['user'], $dbConf['pass'], $dbConf['name']);

$tLoader = new Twig_Loader_Filesystem($config['twig']['template_directory']);
$twig = new Twig_Environment($tLoader);
$GLOBALS['twig'] = $twig;

