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
        pattern: 'POST /update-stuff',
        callback: [DocsController::class, 'updateStuffPost'],
        alias: 'update_stuff'
    );

    /*
     * Redirects
     */
    // if theres no language or version in the url, redirect and default to en
    // and v3
    Flight::route('/', static function (): void {
        // pull out the default language by the accept header
        $language = Translator::getLanguageFromRequest();

        Flight::redirect("/$language/v3/");
    });

    // If the route only defines a language (ex: /en) redirect with a version
    Flight::route(
        '/@language:[a-z0-9]{2}',
        static function (string $language): void {
            // if there's a number in it, it's actually probably the version so
            // we'll need to pull the language out and consider this a version
            $version = preg_match('/\d/', $language) ? $language : 'v3';

            if (preg_match('/\d/', $language)) {
                $language = Translator::getLanguageFromRequest();
                Flight::redirect("/en/$language/");
            }

            Flight::redirect("/$language/$version/");
        }
    );

    // Pick up old routes that didn't use to have a language and version header
    Flight::route(
        '/@section:[\w\-]{3,}(/@sub_section:[\w\-]{3,})',
        static function (string $section, ?string $sub_section = ''): void {
            $language = Translator::getLanguageFromRequest();

            Flight::redirect("/$language/v3/$section/$sub_section/");
        }
    );

    /*
     * Core routes
     */
    Flight::group(
        '/@language:[a-z]{2}/@version:[a-z0-9]{2}',
        static function (): void {
            Flight::route(
                pattern: 'GET /',
                callback: [DocsController::class, 'aboutGet'],
                alias: 'about'
            );

            Flight::route(
                pattern: 'GET /single-page',
                callback: [DocsController::class, 'singlePageGet'],
                alias: 'single_page'
            );

            Flight::route('GET /about', [DocsController::class, 'aboutGet']);

            Flight::route(
                pattern: 'GET /install',
                callback: [DocsController::class, 'installGet'],
                alias: 'install'
            );

            // Unique URL workaround because this is the only 'single page'
            // with a scrollspy for the time being.
            Flight::route('GET /install/install', static function (): void {
                Flight::redirect(Flight::getUrl('install'));
            });

            Flight::route(
                pattern: 'GET /license',
                callback: [DocsController::class, 'licenseGet'],
                alias: 'license'
            );

            Flight::route(
                pattern: 'GET /examples',
                callback: [DocsController::class, 'examplesGet'],
                alias: 'examples'
            );

            Flight::route(
                pattern: 'GET /media',
                callback: [DocsController::class, 'mediaGet'],
                alias: 'media'
            );

            Flight::route(
                pattern: 'GET /search',
                callback: [DocsController::class, 'searchGet'],
                alias: 'search'
            );

            Flight::group('/learn', static function (): void {
                Flight::route(
                    pattern: 'GET ',
                    callback: [DocsController::class, 'learnGet'],
                    alias: 'learn'
                );

                Flight::route(
                    'GET /@section_name',
                    [DocsController::class, 'learnSectionsGet']
                );
            });

            Flight::group('/guides', static function (): void {
                Flight::route(
                    pattern: 'GET /',
                    callback: [DocsController::class, 'guidesGet'],
                    alias: 'guides'
                );

                Flight::route(
                    'GET /@section_name',
                    [DocsController::class, 'guidesSectionsGet']
                );
            });

            Flight::group('/awesome-plugins', static function (): void {
                Flight::route(
                    pattern: 'GET /',
                    callback: [DocsController::class, 'awesomePluginsGet'],
                    alias: 'awesome_plugins'
                );

                Flight::route(
                    pattern: 'GET /@plugin_name',
                    callback: [DocsController::class, 'pluginGet'],
                    alias: 'plugin'
                );
            });
        }
    );
}, [HeaderSecurityMiddleware::class]);

/*
 * 404 Handler
 */
Flight::map('notFound', static function (): void {
    // Clear out anything that may have been generated
    Flight::response()->clearBody()->status(404);

    // pull the version out of the URL
    $url = Flight::request()->url;
    $version = preg_match('~/(v\d)/~', $url, $matches) ? $matches[1] : 'v3';

    (new Container)->get(DocsLogic::class)->renderPage('not_found.latte', [
        'title' => '404 Not Found',
        'version' => $version
    ]);

    Flight::response()->send();
});
