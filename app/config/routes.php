<?php

use app\controllers\ApiExampleController;
use app\controllers\IndexController;
use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */
$IndexController = new IndexController($app);
$router->get('/', [ $IndexController, 'aboutGet' ], false, 'about');
$router->get('/install', [ $IndexController, 'installGet' ], false, 'install');
$router->get('/learn', [ $IndexController, 'learnGet' ], false, 'learn');
