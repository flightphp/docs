Tracy Flight Panel Extensions
=====

Il s'agit d'un ensemble d'extensions pour rendre le travail avec Flight un peu plus riche.

- Flight - Analyser toutes les variables de Flight.
- Database - Analyser toutes les requêtes qui ont été exécutées sur la page (si vous initialisez correctement la connexion à la base de données).
- Request - Analyser toutes les variables `$_SERVER` et examiner toutes les charges globales (`$_GET`, `$_POST`, `$_FILES`).
- Session - Analyser toutes les variables `$_SESSION` si les sessions sont actives.

C'est le panneau

![Barre Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Et chaque panneau affiche des informations très utiles sur votre application !

![Données Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Base de données Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Requête Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Cliquez [ici](https://github.com/flightphp/tracy-extensions) pour voir le code.

Installation
-------
Exécutez `composer require flightphp/tracy-extensions --dev` et c'est parti !

Configuration
-------
Il y a très peu de configuration à faire pour démarrer. Vous devrez initialiser le débogueur Tracy avant d'utiliser ceci [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// code d'amorçage
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Vous devrez peut-être spécifier votre environnement avec Debugger::enable(Debugger::DEVELOPMENT)

// si vous utilisez des connexions à la base de données dans votre application, il y a un
// wrapper PDO requis à utiliser UNIQUEMENT EN DÉVELOPPEMENT (pas en production s'il vous plaît !)
// Il a les mêmes paramètres qu'une connexion PDO normale
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// ou si vous attachez ceci au framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// maintenant à chaque fois que vous exécutez une requête, il capturera le temps, la requête et les paramètres

// Cela connecte les points
if(Debugger::$showBar === true) {
	// Cela doit être faux sinon Tracy ne peut pas rendre correctement :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// plus de code

Flight::start();
```

## Configuration Supplémentaire

### Données de Session
Si vous avez un gestionnaire de session personnalisé (comme ghostff/session), vous pouvez passer n'importe quel tableau de données de session à Tracy et il l'affichera automatiquement pour vous. Vous le transmettez avec la clé `session_data` dans le deuxième paramètre du constructeur `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Cela doit être faux sinon Tracy ne peut pas rendre correctement :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// routes et autres choses...

Flight::start();
```

### Latte

Si vous avez Latte installé dans votre projet, vous pouvez utiliser le panneau Latte pour analyser vos modèles. Vous pouvez transmettre l'instance de Latte au constructeur `TracyExtensionLoader` avec la clé `latte` dans le deuxième paramètre.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// c'est ici que vous ajoutez le panneau Latte à Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Cela doit être faux sinon Tracy ne peut pas rendre correctement :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
