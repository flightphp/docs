<?php

use app\utils\CustomEngine;
use app\utils\Translator;
use Latte\Engine as LatteEngine;
use Latte\Essential\TranslatorExtension;
use Latte\Loaders\FileLoader;
use flight\Cache;

/** 
 * @var array $config 
 * @var CustomEngine $app
 */
$app->register('latte', LatteEngine::class, [], function (LatteEngine $latte) {
    $latte->setTempDirectory(__DIR__ . '/../cache/');
    $latte->setLoader(new FileLoader(__DIR__ . '/../views/'));
    $languageAbbreviation = Translator::getLanguageFromRequest();
    $translator = new Translator($languageAbbreviation);

    $translatorExtension = new TranslatorExtension(
        [$translator, 'translate'],
        $languageAbbreviation
    );

    $latte->addExtension($translatorExtension);
});

$app->register('cache', Cache::class, [__DIR__ . '/../cache/'], function (Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});

$app->register('parsedown', Parsedown::class);
