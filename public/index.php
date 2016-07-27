<?php
date_default_timezone_set('Asia/Hong_Kong');
session_start();

require dirname(__DIR__).'/vendor/autoload.php';

define('WWW_ROOT',     dirname(__DIR__));
define('CONFIG_ROOT',  WWW_ROOT.'/config');
define('RESOURCE_ROOT', WWW_ROOT.'/resources');
define('STORAGE_ROOT',  WWW_ROOT.'/storage');
define('VIEW_ROOT',    RESOURCE_ROOT.'/views');
define('CACHE_ROOT',   STORAGE_ROOT.'/cache');

use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use NicklasW\PkmGoApi\Kernels\ApplicationKernel;
use NicklasW\PkmGoApi\Authenticators\Factory;
use App\Helpers\ViewHelper;

$app = new App([
    'settings' => require_once CONFIG_ROOT.'/app.php'
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
    $view->addExtension(new ViewHelper($c, $basePath));

    return $view;
};

// Routes
$app->get('/', 'App\\Controllers\HomeController:index');

$app->post('/api/pokemon/all', function($request, $response, $args) {
    $username   = $request->getParam('username');
    $password   = $request->getParam('password');
    $authMethod = $request->getParam('auth_method');

    $authType     = $authMethod === "ptc" ? Factory::AUTHENTICATION_TYPE_PTC : Factory::AUTHENTICATION_TYPE_GOOGLE;
    $application  = new ApplicationKernel($username, $password, $authType);

    $pokemonGoApi = $application->getPokemonGoApi();

    $inventory = $pokemonGoApi->getInventory();
    $items     = $inventory->getItems();
    $pokeBank  = $items->getPokeBank();
    $pokemons  = $pokeBank->getPokemons();

    $pokemonDatas = [];
    foreach($pokemons as $id => $pokemon) {
        $pokemonData = $pokemon->getPokemonData();

        if ($pokemonData->getIsEgg() === false) {
            array_push($pokemonDatas, [
                'id'            => $pokemonData->getId(),
                'pokemon_id'    => $pokemonData->getPokemonId(),
                'cp'            => $pokemonData->getCp(),
                'hp'            => $pokemonData->getStamina(),
                'hp_max'        => $pokemonData->getStaminaMax(),
                'move1'         => $pokemonData->getMove1(),
                'move2'         => $pokemonData->getMove2(),
                'height_m'      => $pokemonData->getHeightM(),
                'weight_kg'     => $pokemonData->getWeightKg(),
                'attack'        => $pokemonData->getIndividualAttack(),
                'defense'       => $pokemonData->getIndividualDefense(),
                'stamina'       => $pokemonData->getIndividualStamina(),
                'cp_multiplier' => $pokemonData->getCpMultiplier(),
                'nickname'      => $pokemonData->getNickname(),
            ]);
        }
    }

    return $response->withJson([
        'pokemons' => $pokemonDatas
    ]);
});

// Bootstrap
$app->run();
