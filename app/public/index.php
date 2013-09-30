<?php

require(__DIR__ . '/../bootstrap.php');

$controllerName = isset($_REQUEST['controller']) ? $_REQUEST['controller'] : 'Home';
$controllerName .= 'Controller';
$controller = new $controllerName();

$actionName = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';

$controller->$actionName();
