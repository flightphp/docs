<?php

use app\utils\CustomEngine;
use app\utils\Translator;
use flight\Apm;
use flight\apm\logger\LoggerFactory;
use Latte\Engine as LatteEngine;
use Latte\Essential\TranslatorExtension;
use Latte\Loaders\FileLoader;
use flight\Cache;

/**
 * @var array $config
 * @var CustomEngine $app
 */

 // This translates some common parts of the page, not the content
$app->register('translator', Translator::class);

// Templating Engine used to render the views
$app->register('latte', LatteEngine::class, [], function (LatteEngine $latte) use ($app): void {
    $latte->setTempDirectory(__DIR__ . '/../cache/');
    $latte->setLoader(new FileLoader(__DIR__ . '/../views/'));
    $translator = $app->translator();

    $translatorExtension = new TranslatorExtension(
        $translator->translate(...),
    );

    $latte->addExtension($translatorExtension);
});

// Cache for storing parsedown and other things
$app->register('cache', Cache::class, [__DIR__ . '/../cache/'], function (Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});

// Parsedown is a markdown parser
$app->register('parsedown', Parsedown::class);

// Register the APM
$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
