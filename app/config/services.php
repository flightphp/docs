<?php

use app\utils\Translator;
use Latte\Engine as LatteEngine;
use Latte\Essential\TranslatorExtension;
use Latte\Loaders\FileLoader;
use flight\Cache;

// This translates some common parts of the page, not the content
Flight::register('translator', Translator::class);

// Templating Engine used to render the views
Flight::register('latte', LatteEngine::class, [], static function (LatteEngine $latte): void {
    $latte->setTempDirectory(__DIR__ . '/../cache/');
    $latte->setLoader(new FileLoader(__DIR__ . '/../views/'));
    $translator = Flight::translator();

    $translatorExtension = new TranslatorExtension(
        [$translator, 'translate'],
    );

    $latte->addExtension($translatorExtension);
});

// Cache for storing parsedown and other things
Flight::register('cache', Cache::class, [__DIR__ . '/../cache/'], static function (Cache $cache): void {
    $cache->setDevMode(ENVIRONMENT === 'development');
});

// Parsedown is a markdown parser
Flight::register('parsedown', Parsedown::class);
