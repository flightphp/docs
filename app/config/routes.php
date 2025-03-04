<?php

use app\controllers\IndexController;
use app\middleware\HeaderSecurityMiddleware;
use app\utils\Translator;
use flight\Engine;
use flight\net\Router;

$headerSecurityMiddleware = new HeaderSecurityMiddleware();

/** @var Engine $app */
/** @var Router $router */

$IndexController = new IndexController($app);

// This processes github webhooks
$router->post('/update-stuff', [$IndexController, 'updateStuffPost'], false, 'update_stuff')
	->addMiddleware($headerSecurityMiddleware);

// if theres no language or version in the url, redirect and default to en and v3
$app->route('/', function () use ($app) {
	// pull out the default language by the accept header
	$language = Translator::getLanguageFromRequest();
	$app->redirect('/'.$language.'/v3/');
})->addMiddleware($headerSecurityMiddleware);

$app->route('/@language:[a-z0-9]{2}', function (string $language) use ($app): void {
	// if there's a number in it, it's actually probably the version so we'll need to pull the language out and consider this a version
	if (preg_match('/\d/', $language) === 1) {
		$version = $language;
		$language = Translator::getLanguageFromRequest();
		$app->redirect("/en/$language/");
	} else {
		$version = 'v3';
	}
	$app->redirect("/$language/$version/");
})->addMiddleware($headerSecurityMiddleware);

// Pick up old routes that didn't use to have a language and version header
$app->route('/@section:[\w\-]{3,}(/@sub_section:[\w\-]{3,})', function (string $section, ?string $sub_section = '') use ($app): void {
	$language = Translator::getLanguageFromRequest();
	$app->redirect("/{$language}/v3/$section/$sub_section");
})->addMiddleware($headerSecurityMiddleware);

$app->group('/@language:[a-z]{2}/@version:[a-z0-9]{2}', function (Router $router) use ($app, $IndexController): void {
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
}, [ $headerSecurityMiddleware ]);

$app->map('notFound', function () use ($app): void {
    $app->response()->clearBody()->status(404);

	// pull the version out of the URL
	$url = $app->request()->url;
	$version = preg_match('~/(v\d)/~', $url, $matches) === 1 ? $matches[1] : 'v3';

    (new IndexController($app))->renderPage('not_found.latte', [
		'title' => '404 Not Found',
		'version' => $version
	]);
    $app->response()->send();

    exit;
});
