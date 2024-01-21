<?php

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
$router->get('/license', [ $IndexController, 'licenseGet' ], false, 'license');
$router->get('/learn', [ $IndexController, 'learnGet' ], false, 'learn');
$router->get('/examples', [ $IndexController, 'examplesGet' ], false, 'examples');
$router->group('/plugins', function(Router $router) use ($IndexController) {
	$router->get('/', [ $IndexController, 'pluginsGet' ], false, 'plugins');
	$router->get('/@plugin_name', [ $IndexController, 'pluginGet' ], false, 'plugin');
});
