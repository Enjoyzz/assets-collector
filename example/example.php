<?php

use Enjoys\AssetsCollector\Assets;
use Enjoys\AssetsCollector\Environment;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Logger;

include "../vendor/autoload.php";

$logger = new Logger('Logger');

$console = new BrowserConsoleHandler();
$output = "[[%channel%]]{font-weight: bold} [[%level_name%]]{macro: autolabel}  %message% ";
$formatter = new LineFormatter($output);
$console->setFormatter($formatter);
$logger->pushHandler($console);


$environment = new Environment('assets', __DIR__);
$environment->setBaseUrl("/assets-collector/example/assets");
$environment->setStrategy(Assets::STRATEGY_ONE_FILE);
$environment->setCacheTime(5);
$environment->setLogger($logger);
//$environment->setPageId('d');


$assets = new Assets($environment);

//$assets->add('css', '../build/phpmetrics/../phpmetrics/css/style.css');
//$assets->add('css', '../build/phpmetrics/../phpmetrics/css/style.css');
$assets->add('css', 'https://www.php.net/cached.php?t=1539765004&f=/fonts/Font-Awesome/css/fontello.css', '1');
$assets->add('css', '../build/phpmetrics/../phpmetrics/css/style.css');
var_dump($assets->get('css'));
var_dump($assets->get('css', '1'));
