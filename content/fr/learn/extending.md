# Extension

Flight est conçu pour être un framework extensible. Le framework est livré avec un ensemble de méthodes et de composants par défaut, mais il vous permet de mapper vos propres méthodes, d'enregistrer vos propres classes, ou même de remplacer des classes et des méthodes existantes.

Si vous recherchez un DIC (Conteneur d'Injection de Dépendances), rendez-vous sur la page [Conteneur d'Injection de Dépendances](dependency-injection-container).

## Mapper des Méthodes

Pour mapper votre propre méthode personnalisée simple, vous utilisez la fonction `map` :

```php
// Mapper votre méthode
Flight::map('hello', function (string $name) {
  echo "bonjour $name!";
});

// Appeler votre méthode personnalisée
Flight::hello('Bob');
```

Bien qu'il soit possible de créer des méthodes personnalisées simples, il est recommandé de créer simplement des fonctions standard en PHP. Cela bénéficie de l'autocomplétion dans les IDE et est plus facile à lire. L'équivalent du code ci-dessus serait :

```php
function hello(string $name) {
  echo "bonjour $name!";
}

hello('Bob');
```

Ceci est plus utilisé lorsque vous devez passer des variables à votre méthode pour obtenir une valeur attendue. Utiliser la méthode `register()` comme ci-dessous est plus destiné à passer une configuration et à appeler votre classe préconfigurée.

## Enregistrement de Classes

Pour enregistrer votre propre classe et la configurer, vous utilisez la fonction `register` :

```php
// Enregistrer votre classe
Flight::register('user', User::class);

// Obtenir une instance de votre classe
$user = Flight::user();
```

La méthode d'enregistrement vous permet également de passer des paramètres à votre constructeur de classe. Ainsi, lorsque vous chargez votre classe personnalisée, elle sera pré-initialisée. Vous pouvez définir les paramètres du constructeur en passant un tableau supplémentaire. Voici un exemple de chargement d'une connexion à la base de données :

```php
// Enregistrer la classe avec des paramètres de constructeur
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtenir une instance de votre classe
// Cela créera un objet avec les paramètres définis
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// et si vous en aviez besoin plus tard dans votre code, il vous suffit d'appeler la même méthode à nouveau
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si vous passez un paramètre de rappel supplémentaire, il sera exécuté immédiatement après la construction de la classe. Cela vous permet d'effectuer toute procédure de configuration pour votre nouvel objet. La fonction de rappel prend un paramètre, une instance du nouvel objet.

```php
// Le rappel sera passé à l'objet qui a été construit
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Par défaut, chaque fois que vous chargez votre classe, vous obtiendrez une instance partagée. Pour obtenir une nouvelle instance d'une classe, il vous suffit de passer `false` comme paramètre :

```php
// Instance partagée de la classe
$shared = Flight::db();

// Nouvelle instance de la classe
$new = Flight::db(false);
```

Gardez à l'esprit que les méthodes mappées ont la priorité sur les classes enregistrées. Si vous déclarez les deux en utilisant le même nom, seule la méthode mappée sera invoquée.

## Journalisation

Flight n'a pas de système de journalisation intégré, cependant, il est très facile d'utiliser une bibliothèque de journalisation avec Flight. Voici un exemple utilisant la bibliothèque Monolog :

```php
// index.php ou bootstrap.php

// Enregistrer le logger avec Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Maintenant qu'il est enregistré, vous pouvez l'utiliser dans votre application :

```php
// Dans votre contrôleur ou route
Flight::log()->warning('Ceci est un message d\'avertissement');
```

Cela enregistrera un message dans le fichier journal que vous avez spécifié. Que faire si vous souhaitez enregistrer quelque chose lorsqu'une erreur se produit ? Vous pouvez utiliser la méthode `error` :

```php
// Dans votre contrôleur ou route

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Affichez votre page d'erreur personnalisée
	include 'errors/500.html';
});
```

Vous pourriez également créer un système APM (Surveillance de Performance d'Application) de base en utilisant les méthodes `before` et `after` :

```php
// Dans votre fichier bootstrap

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('La requête '.Flight::request()->url.' a pris ' . round($end - $start, 4) . ' secondes');

	// Vous pourriez également ajouter vos en-têtes de requête ou de réponse
	// pour les enregistrer également (soyez prudent car cela serait beaucoup de données
	// si vous avez beaucoup de requêtes)
	Flight::log()->info('En-têtes de requête : ' . json_encode(Flight::request()->headers));
	Flight::log()->info('En-têtes de réponse : ' . json_encode(Flight::response()->headers));
});
```

## Remplacer les Méthodes du Framework

Flight vous permet de remplacer sa fonctionnalité par défaut pour répondre à vos propres besoins, sans avoir à modifier de code. Vous pouvez consulter toutes les méthodes que vous pouvez remplacer [ici](/learn/api).

Par exemple, lorsque Flight ne parvient pas à faire correspondre une URL à une route, il invoque la méthode `notFound` qui envoie une réponse générique `HTTP 404`. Vous pouvez remplacer ce comportement en utilisant la méthode `map` :

```php
Flight::map('notFound', function() {
  // Afficher la page 404 personnalisée
  include 'errors/404.html';
});
```

Flight vous permet également de remplacer des composants principaux du framework. Par exemple, vous pouvez remplacer la classe Router par défaut par votre propre classe personnalisée :

```php
// Enregistrer votre classe personnalisée
Flight::register('router', MyRouter::class);

// Lorsque Flight charge l'instance Router, il chargera votre classe
$myrouter = Flight::router();
```

Cependant, les méthodes du framework comme `map` et `register` ne peuvent pas être remplacées. Vous obtiendrez une erreur si vous essayez de le faire.