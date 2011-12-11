<?php

/**
 * My Application bootstrap file.
 */

use Nette\Diagnostics\Debugger,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\RouteList;

// Load Nette Framework
require $params['libsDir'] . '/Nette/loader.php';
require $params['appDir'] . '/services/Container.php';

// Enable Nette Debugger for error visualisation & logging
Debugger::$logDirectory = __DIR__ . '/../log';
Debugger::$strictMode = TRUE;
Debugger::enable();


// Load configuration from config.neon file
$configurator = new Nette\Configurator('\\Container');
$configurator->container->params += $params;
$container = $configurator->loadConfig(__DIR__ . '/config.neon');


// Setup router
$router = $container->router;
$router[] = $gameRouter = new RouteList('Game');
$gameRouter[] = new Route('game/<presenter>/<action>/[<id>]', 'Dashboard:default');
$router[] = $adminRouter = new RouteList('Admin');
$adminRouter[] = new Route('admin/<presenter>/<action>[/<id>]', 'Dashboard:default');
$router[] = $frontRouter = new RouteList('Front');
$frontRouter[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
$frontRouter[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');


// Configure and run the application!
$application = $container->application;
//$application->catchExceptions = TRUE;
$application->errorPresenter = 'Error';
$application->run();
// $container->model->resourceService->recalculateProduction($container->model->clanRepository->getPlayerClan());