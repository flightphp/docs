```fr
Tracy Flight Panel Extensions
=====

Ce sont des extensions pour enrichir un peu le travail avec Flight.

- Vol - Analyser toutes les variables de vol.
- Base de données - Analyser toutes les requêtes qui ont été exécutées sur la page (si vous initialisez correctement la connexion à la base de données)
- Requête - Analyser toutes les variables `$_SERVER` et examiner tous les envois globaux (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analyser toutes les variables `$_SESSION` si les sessions sont actives.

C'est le panneau
![Barre de vol](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

Et chaque panneau affiche des informations très utiles sur votre application!
![Données de vol](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Base de données de vol](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Requête de vol](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Installation
-------
Exécutez `composer require flightphp/tracy-extensions --dev` et vous êtes prêt!

Configuration
-------
Il y a très peu de configuration à faire pour commencer. Vous devrez initialiser le débogueur Tracy avant d'utiliser ceci [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// code d'amorçage
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Vous devrez peut-être spécifier votre environnement avec Debugger::enable(Debugger::DEVELOPMENT)

// Si vous utilisez des connexions à la base de données dans votre application, il y a un
// wrapper PDO requis à utiliser UNIQUEMENT EN DÉVELOPPEMENT (pas en production s'il vous plaît!)
// Il a les mêmes paramètres qu'une connexion PDO régulière
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// ou si vous attachez ceci au cadre Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// maintenant chaque fois que vous effectuez une requête, il capturera le temps, la requête et les paramètres

// Cela connecte les points
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// plus de code

Flight::start();
```