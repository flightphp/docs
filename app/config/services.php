<?php

use app\utils\CustomEngine;
use app\utils\Translator;
use Latte\Engine as LatteEngine;
use Wruczek\PhpFileCache\PhpFileCache;

/** 
 * @var array $config 
 * @var CustomEngine $app
 */
$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));


	$languageAbbreviation = Translator::getLanguageFromRequest();
	$translator = new Translator($languageAbbreviation);
	$translatorExtension = new \Latte\Essential\TranslatorExtension(
		[ $translator, 'translate' ],
		$languageAbbreviation
	);
	$latte->addExtension($translatorExtension);
});

$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {
	$cache->setDevMode(ENVIRONMENT === 'development');
});

$app->register('parsedown', Parsedown::class);