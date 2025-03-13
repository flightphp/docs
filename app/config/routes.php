<?php

use app\controllers\DocsController;
use app\middleware\HeaderSecurityMiddleware;
use app\utils\DocsLogic;
use app\utils\Translator;
use flight\Container;

Flight::route('GET /api/status', static fn() => Flight::json(['status' => 'ok']));

// This acts like a global middleware
Flight::group('', static function (): void {
    /*
     * Specific routes
     */
    // This processes github webhooks
    Flight::route(
        'POST /update-stuff',
        [DocsController::class, 'updateStuffPost'],
        false,
        'update_stuff'
    );

    /*
     * Redirects
     */
    // if theres no language or version in the url, redirect and default to en and v3
    Flight::route('/', static function (): void {
        // pull out the default language by the accept header
        $language = Translator::getLanguageFromRequest();
        Flight::redirect('/' . $language . '/v3/');
    });

    // If the route only defines a language (ex: /en) redirect with a version
    Flight::route('/@language:[a-z0-9]{2}', static function (string $language): void {
        // if there's a number in it, it's actually probably the version so we'll need to pull the language out and consider this a version
        if (preg_match('/\d/', $language) === 1) {
            $version = $language;
            $language = Translator::getLanguageFromRequest();
            Flight::redirect("/en/$language/");
        } else {
            $version = 'v3';
        }
        Flight::redirect("/$language/$version/");
    });

    // Pick up old routes that didn't use to have a language and version header
    Flight::route('/@section:[\w\-]{3,}(/@sub_section:[\w\-]{3,})', function (string $section, ?string $sub_section = ''): void {
        $language = Translator::getLanguageFromRequest();
        Flight::redirect("/{$language}/v3/$section/$sub_section/");
    });

    /*
     * Core routes
     */
    Flight::group('/@language:[a-z]{2}/@version:[a-z0-9]{2}', static function (): void {
        Flight::route('GET /', [DocsController::class, 'aboutGet'], false, 'about');
        Flight::route('GET /single-page', [DocsController::class, 'singlePageGet'], false, 'single_page');
        Flight::route('GET /about', [DocsController::class, 'aboutGet']);
        Flight::route('GET /install', [DocsController::class, 'installGet'], false, 'install');

        // Unique URL workaround because this is the only 'single page' with a scrollspy for the time being.
        Flight::route('GET /install/install', static function (): void {
            Flight::redirect(Flight::getUrl('install'));
        });

        Flight::route('GET /license', [DocsController::class, 'licenseGet'], false, 'license');
        Flight::route('GET /examples', [DocsController::class, 'examplesGet'], false, 'examples');
        Flight::route('GET /media', [DocsController::class, 'mediaGet'], false, 'media');
        Flight::route('GET /search', [DocsController::class, 'searchGet'], false, 'search');

        Flight::group('/learn', static function (): void {
            Flight::route('GET ', [DocsController::class, 'learnGet'], false, 'learn');
            Flight::route('GET /@section_name', [DocsController::class, 'learnSectionsGet']);
        });

        Flight::group('/guides', static function (): void {
            Flight::route('GET ', [DocsController::class, 'guidesGet'], false, 'guides');
            Flight::route('GET /@section_name', [DocsController::class, 'guidesSectionsGet']);
        });

        Flight::group('/awesome-plugins', static function (): void {
            Flight::route('GET ', [DocsController::class, 'awesomePluginsGet'], false, 'awesome_plugins');
            Flight::route('GET /@plugin_name', [DocsController::class, 'pluginGet'], false, 'plugin');
        });
    });
}, [new HeaderSecurityMiddleware()]);

/*
 * 404 Handler
 */
Flight::map('notFound', static function (): void {
    // Clear out anything that may have been generated
    Flight::response()->clearBody()->status(404);

    // pull the version out of the URL
    $url = Flight::request()->url;
    $version = preg_match('~/(v\d)/~', $url, $matches) === 1 ? $matches[1] : 'v3';

    (new Container)->get(DocsLogic::class)->renderPage('not_found.latte', [
        'title' => '404 Not Found',
        'version' => $version
    ]);

    Flight::response()->send();

    exit;
});
