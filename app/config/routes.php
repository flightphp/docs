<?php

use app\controllers\DocsController;
use app\middleware\HeaderSecurityMiddleware;
use app\utils\DocsLogic;
use app\utils\Translator;
use app\utils\CustomEngine;
use flight\net\Router;

/** @var CustomEngine $app */
/** @var Router $router */

// This acts like a global middleware
$router->group('', function (Router $router) use ($app) {

	/*
	 * Specific routes
	 */
	// This processes github webhooks
	$router->post('/update-stuff', [DocsController::class, 'updateStuffPost'], false, 'update_stuff');

	/*
	 * Redirects
	 */
	// if theres no language or version in the url, redirect and default to en and v3
	$app->route('/', function () use ($app) {
		// pull out the default language by the accept header
		$language = Translator::getLanguageFromRequest();
		$app->redirect('/'.$language.'/v3/');
	});

	// If the route only defines a language (ex: /en) redirect with a version
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
	});

	// Pick up old routes that didn't use to have a language and version header
	$app->route('/@section:[\w\-]{3,}(/@sub_section:[\w\-]{3,})', function (string $section, ?string $sub_section = '') use ($app): void {
		$language = Translator::getLanguageFromRequest();
		$app->redirect("/{$language}/v3/$section/$sub_section");
	});

	/*
	 * Core routes
	 */
	$app->group('/@language:[a-z]{2}/@version:[a-z0-9]{2}', function (Router $router) use ($app): void {
		$router->get('/', [DocsController::class, 'aboutGet'], false, 'about');
		$router->get('/single-page', [DocsController::class, 'singlePageGet'], false, 'single_page');
		$router->get('/about', [DocsController::class, 'aboutGet']);
		$router->get('/install', [DocsController::class, 'installGet'], false, 'install');

		// Unique URL workaround because this is the only 'single page' with a scrollspy for the time being.
		$router->get('/install/install', function () use ($app): void {
			$app->redirect($app->getUrl('install'));
		});

		$router->get('/license', [DocsController::class, 'licenseGet'], false, 'license');
		$router->get('/examples', [DocsController::class, 'examplesGet'], false, 'examples');
		$router->get('/media', [DocsController::class, 'mediaGet'], false, 'media');
		$router->get('/search', [DocsController::class, 'searchGet'], false, 'search');

		$router->group('/learn', function (Router $router): void {
			$router->get('', [DocsController::class, 'learnGet'], false, 'learn');
			$router->get('/@section_name', [DocsController::class, 'learnSectionsGet']);
		});

		$router->group('/awesome-plugins', function (Router $router): void {
			$router->get('', [DocsController::class, 'awesomePluginsGet'], false, 'awesome_plugins');
			$router->get('/@plugin_name', [DocsController::class, 'pluginGet'], false, 'plugin');
		});
	});
}, [ new HeaderSecurityMiddleware() ]);

/*
 * 404 Handler
 */
$app->map('notFound', function () use ($app): void {
	// Clear out anything that may have been generated
    $app->response()->clearBody()->status(404);

	// pull the version out of the URL
	$url = $app->request()->url;
	$version = preg_match('~/(v\d)/~', $url, $matches) === 1 ? $matches[1] : 'v3';

    (new DocsLogic($app))->renderPage('not_found.latte', [
		'title' => '404 Not Found',
		'version' => $version
	]);
    $app->response()->send();
    exit;
});
