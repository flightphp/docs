<?php

use app\controllers\IndexController;
use app\middleware\HeaderSecurityMiddleware;
use flight\Engine;
use flight\net\Router;

/** @var Engine $app */
$app->group('', function (Router $router) use ($app): void {
    $IndexController = new IndexController($app);

    $router->get('/', [$IndexController, 'aboutGet'], false, 'about');
    $router->get('/single-page', [$IndexController, 'singlePageGet'], false, 'single_page');
    $router->get('/about', [$IndexController, 'aboutGet']);
    $router->get('/install', [$IndexController, 'installGet'], false, 'install');

    // Unique URL workaround because this is the only 'single page' with a scrollspy for the time being.
    $router->get('/install/install', function () use ($app): void {
        $app->redirect($app->getUrl('install'));
    });

    $router->get('/license', [$IndexController, 'licenseGet'], false, 'license');
    $router->get('/examples', [$IndexController, 'examplesGet'], false, 'examples');
    $router->get('/media', [$IndexController, 'mediaGet'], false, 'media');
    $router->get('/search', [$IndexController, 'searchGet'], false, 'search');

    $router->group('/learn', function (Router $router) use ($IndexController): void {
        $router->get('', [$IndexController, 'learnGet'], false, 'learn');
        $router->get('/@section_name', [$IndexController, 'learnSectionsGet']);
    });

    $router->group('/awesome-plugins', function (Router $router) use ($IndexController): void {
        $router->get('', [$IndexController, 'awesomePluginsGet'], false, 'awesome_plugins');
        $router->get('/@plugin_name', [$IndexController, 'pluginGet'], false, 'plugin');
    });

    // Clever name for the github webhook
    $router->post('/update-stuff', [$IndexController, 'updateStuffPost'], false, 'update_stuff');
}, [new HeaderSecurityMiddleware()]);

$app->map('notFound', function () use ($app): void {
    $app->response()->clearBody()->status(404);

    (new IndexController($app))->renderPage('not_found.latte');
    $app->response()->send();

    exit;
});
