<?php

use app\controllers\DocsController;
use app\middleware\HeaderSecurityMiddleware;
use app\utils\DocsLogic;
use app\utils\Translator;
use flight\Container;

// This acts like a global middleware
Flight::group('', function () {
    /*
     * Specific routes
     */
    // This processes github webhooks
    Flight::route('POST /update-stuff', [DocsController::class, 'updateStuffPost'], false, 'update_stuff');

    /*
     * Redirects
     */
    // if theres no language or version in the url, redirect and default to en and v3
    Flight::route('/', function () {
        // pull out the default language by the accept header
        $language = Translator::getLanguageFromRequest();
        Flight::redirect("/{$language}/v3/");
    });

    // If the route only defines a language (ex: /en) redirect with a version
    Flight::route('/@language:[a-z0-9]{2}', function (string $language): void {
        // if there's a number in it, it's actually probably the version so we'll need to pull the language out and consider this a version
        if (preg_match('/\d/', $language) === 1) {
            $version = $language;
            $language = Translator::getLanguageFromRequest();
            Flight::redirect("/en/$language/");
        } else {
            $version = 'v3';
        }

        Flight::redirect("/{$language}/{$version}/");
    });

    // Pick up old routes that didn't use to have a language and version header
    Flight::route(
        '/@section:[\w\-]{3,}(/@sub_section:[\w\-]{2,})',
        function (string $section, ?string $sub_section = ''): void {
            $language = Translator::getLanguageFromRequest();
            Flight::redirect("/{$language}/v3/{$section}/{$sub_section}");
        }
    );

    /*
     * Core routes
     */
    Flight::group('/@language:[a-z]{2}/@version:[a-z0-9]{2}', function (): void {
        Flight::route('/*', function (string $language): true {
            if (!defined('LANGUAGE')) {
                define('LANGUAGE', $language);
            }

            return true;
        });

        Flight::route('GET /', [DocsController::class, 'aboutGet'], false, 'about');
        Flight::route('GET /single-page', [DocsController::class, 'singlePageGet'], false, 'single_page');
        Flight::route('GET /install', [DocsController::class, 'installGet'], false, 'install');
        Flight::route('GET /license', [DocsController::class, 'licenseGet'], false, 'license');
        Flight::route('GET /examples', [DocsController::class, 'examplesGet'], false, 'examples');
        Flight::route('GET /media', [DocsController::class, 'mediaGet'], false, 'media');
        Flight::route('GET /search', [DocsController::class, 'searchGet'], false, 'search');

        Flight::group('/learn', function (): void {
            Flight::route('GET /', [DocsController::class, 'learnGet'], false, 'learn');
            Flight::route('GET /@section_name', [DocsController::class, 'learnSectionsGet']);
        });

        Flight::group('/guides', function (): void {
            Flight::route('GET /', [DocsController::class, 'guidesGet'], false, 'guides');
            Flight::route('GET /@section_name', [DocsController::class, 'guidesSectionsGet']);
        });

        Flight::group('/awesome-plugins', function (): void {
            Flight::route('GET /', [DocsController::class, 'awesomePluginsGet'], false, 'awesome_plugins');
            Flight::route('GET /@plugin_name', [DocsController::class, 'pluginGet'], false, 'plugin');
        });
    });
}, [HeaderSecurityMiddleware::class]);

/*
 * 404 Handler
 */
Flight::map('notFound', function (): void {
    // Clear out anything that may have been generated
    Flight::response()->clearBody()->status(404);

    // pull the version out of the URL
    $url = Flight::request()->url;
    $version = preg_match('~/(v\d)/~', $url, $matches) === 1 ? $matches[1] : 'v3';

    Container::getInstance()->get(DocsLogic::class)->renderPage('not_found.latte', [
        'title' => '404 Not Found',
        'version' => $version,
    ]);

    try {
        Flight::response()->send();
        exit;
    } catch (\Swoole\ExitException) {
        // Swoole will throw an ExitException when exiting, we can ignore it.
    }
});
