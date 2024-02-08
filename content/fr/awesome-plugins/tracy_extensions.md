Tracy Flight Panel Extensions
=====

Ceci est un ensemble d'extensions pour rendre le travail avec Flight un peu plus riche.

- Flight - Analyse toutes les variables de Flight.
- Database - Analyse toutes les requêtes qui ont été exécutées sur la page (si vous initialisez correctement la connexion à la base de données)
- Request - Analyse toutes les variables `$_SERVER` et examine toutes les charges globales (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analyse toutes les variables `$_SESSION` si les sessions sont actives.

Voici le panneau

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Et chaque panneau affiche des informations très utiles sur votre application!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Installation
-------
Exécutez `composer require flightphp/tracy-extensions --dev` et vous êtes prêt à partir!

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
// enveloppeur PDO requis à utiliser UNIQUEMENT EN DÉVELOPPEMENT (pas en production s'il vous plaît!)
// Il a les mêmes paramètres qu'une connexion PDO régulière
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// ou si vous attachez ceci au framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// maintenant chaque fois que vous exécutez une requête, elle capturera le temps, la requête et les paramètres

// Ceci connecte les points
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// plus de code

Flight::start();
```