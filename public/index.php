<?php
require dirname(__DIR__).'/vendor/autoload.php';

define('WWW_ROOT',     dirname(__DIR__));
define('VIEW_ROOT',    WWW_ROOT.'/views');
define('STORAGE_ROOT', WWW_ROOT.'/storage');
define('CACHE_ROOT',   STORAGE_ROOT.'/cache');

use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

$app = new App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

// Container
$container = $app->getContainer();

$container['view'] = function ($c) {
    $view = new Twig(VIEW_ROOT, [
        'charset'          => 'utf-8',
        'cache'            => CACHE_ROOT.'/views',
        'auto_reload'      => true,
        'strict_variables' => false,
        'autoescape'       => true
    ]);

    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new TwigExtension($c['router'], $basePath));

    return $view;
};

// Routes
$app->get('/', function($request, $response, $args) {
    return $this->view->render($response, 'index.html');
});

// Bootstrap
$app->run();
