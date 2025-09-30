Extensions du panneau Tracy Flight
=====

Ceci est un ensemble d'extensions pour rendre le travail avec Flight un peu plus riche.

- Flight - Analyser toutes les variables Flight.
- Base de données - Analyser toutes les requêtes qui ont été exécutées sur la page (si vous initialisez correctement la connexion à la base de données)
- Requête - Analyser toutes les variables `$_SERVER` et examiner toutes les charges globales (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analyser toutes les variables `$_SESSION` si les sessions sont actives.

Voici le panneau

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Et chaque panneau affiche des informations très utiles sur votre application !

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Cliquez [ici](https://github.com/flightphp/tracy-extensions) pour voir le code.

Installation
-------
Exécutez `composer require flightphp/tracy-extensions --dev` et vous êtes prêt !

Configuration
-------
Il y a très peu de configuration à faire pour démarrer cela. Vous devrez initialiser le débogueur Tracy avant d'utiliser cela [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide) :

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// code de démarrage
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Vous devrez peut-être spécifier votre environnement avec Debugger::enable(Debugger::DEVELOPMENT)

// si vous utilisez des connexions à la base de données dans votre application, il y a un 
// wrapper PDO requis à utiliser UNIQUEMENT EN DÉVELOPPEMENT (pas en production s'il vous plaît !)
// Il a les mêmes paramètres qu'une connexion PDO régulière
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// ou si vous l'attachez au framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// maintenant, chaque fois que vous exécutez une requête, elle capturera le temps, la requête et les paramètres

// Cela connecte les points
if(Debugger::$showBar === true) {
	// Cela doit être false sinon Tracy ne peut pas réellement rendre :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// plus de code

Flight::start();
```

## Configuration supplémentaire

### Données de session
Si vous avez un gestionnaire de session personnalisé (comme ghostff/session), vous pouvez passer n'importe quel tableau de données de session à Tracy et il l'affichera automatiquement pour vous. Vous le passez avec la clé `session_data` dans le deuxième paramètre du constructeur `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// ou utilisez flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Cela doit être false sinon Tracy ne peut pas réellement rendre :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// routes et autres choses...

Flight::start();
```

### Latte

_PHP 8.1+ est requis pour cette section._

Si vous avez Latte installé dans votre projet, Tracy a une intégration native avec Latte pour analyser vos templates. Vous enregistrez simplement l'extension avec votre instance Latte.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// autres configurations...

	// n'ajouter l'extension que si la barre de débogage Tracy est activée
	if(Debugger::$showBar === true) {
		// c'est ici que vous ajoutez le panneau Latte à Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```