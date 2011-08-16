<?php

/**
 * My Application bootstrap file.
 */

use Nette\Diagnostics\Debugger,
	Nette\Application\Routers\Route;

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
$router[] = new Route('index.php', 'Front:Default:default', Route::ONE_WAY);
$router[] = new Route('<presenter>/<action>[/<id>]', 'Front:Default:default');


// Configure and run the application!
$application = $container->application;
//$application->catchExceptions = TRUE;
$application->errorPresenter = 'Error';
$application->run();
