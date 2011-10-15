<?php

// uncomment this line if you must temporarily take down your site for maintenance
// require '.maintenance.php';

$params = array();

// absolute filesystem path to this web root
$params['wwwDir'] = __DIR__;

// absolute filesystem path to the application root
$params['appDir'] = realpath(__DIR__ . '/../app');

// absolute filesystem path to the libraries directory
$params['libsDir'] = realpath(__DIR__ . '/../lib');

// absolute filesystem path to the temporary files directory
$params['tempDir'] = realpath(__DIR__ . '/../tmp');

// load bootstrap file
require $params['appDir'] . '/bootstrap.php';
