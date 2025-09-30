# Extension

## Aperçu

Flight est conçu pour être un framework extensible. Le framework est livré avec un
ensemble de méthodes et de composants par défaut, mais il vous permet de mapper vos propres méthodes,
d'enregistrer vos propres classes, ou même de surcharger les classes et méthodes existantes.

## Compréhension

Il existe 2 façons d'étendre la fonctionnalité de Flight :

1. Mappage de méthodes - Cela est utilisé pour créer des méthodes personnalisées simples que vous pouvez appeler
   depuis n'importe où dans votre application. Elles sont généralement utilisées pour des fonctions utilitaires
   que vous souhaitez pouvoir appeler depuis n'importe où dans votre code. 
2. Enregistrement de classes - Cela est utilisé pour enregistrer vos propres classes avec Flight. Cela est
   généralement utilisé pour des classes qui ont des dépendances ou qui nécessitent une configuration.

Vous pouvez également surcharger les méthodes du framework existantes pour modifier leur comportement par défaut afin de mieux
répondre aux besoins de votre projet. 

> Si vous recherchez un DIC (Dependency Injection Container), passez à la
page [Dependency Injection Container](/learn/dependency-injection-container).

## Utilisation de base

### Surcharge des méthodes du framework

Flight vous permet de surcharger sa fonctionnalité par défaut pour répondre à vos propres besoins,
sans avoir à modifier le code. Vous pouvez voir toutes les méthodes que vous pouvez surcharger [ci-dessous](#mappable-framework-methods).

Par exemple, lorsque Flight ne peut pas faire correspondre une URL à une route, il invoque la méthode `notFound`
qui envoie une réponse générique `HTTP 404`. Vous pouvez surcharger ce comportement
en utilisant la méthode `map` :

```php
Flight::map('notFound', function() {
  // Afficher une page 404 personnalisée
  include 'errors/404.html';
});
```

Flight vous permet également de remplacer les composants principaux du framework.
Par exemple, vous pouvez remplacer la classe Router par défaut par votre propre classe personnalisée :

```php
// créer votre classe Router personnalisée
class MyRouter extends \flight\net\Router {
	// surcharger les méthodes ici
	// par exemple, un raccourci pour les requêtes GET pour supprimer
	// la fonctionnalité de passage de route
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// Enregistrer votre classe personnalisée
Flight::register('router', MyRouter::class);

// Lorsque Flight charge l'instance Router, il chargera votre classe
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

Cependant, les méthodes du framework comme `map` et `register` ne peuvent pas être surchargées. Vous obtiendrez
une erreur si vous essayez de le faire (voir encore [ci-dessous](#mappable-framework-methods) pour une liste des méthodes).

### Méthodes du framework mappables

Voici l'ensemble complet des méthodes pour le framework. Il se compose de méthodes principales, 
qui sont des méthodes statiques régulières, et de méthodes extensibles, qui sont des méthodes mappées qui peuvent 
être filtrées ou surchargées.

#### Méthodes principales

Ces méthodes sont essentielles au framework et ne peuvent pas être surchargées.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Crée une méthode personnalisée du framework.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Enregistre une classe à une méthode du framework.
Flight::unregister(string $name) // Désenregistre une classe d'une méthode du framework.
Flight::before(string $name, callable $callback) // Ajoute un filtre avant une méthode du framework.
Flight::after(string $name, callable $callback) // Ajoute un filtre après une méthode du framework.
Flight::path(string $path) // Ajoute un chemin pour l'autoloading des classes.
Flight::get(string $key) // Obtient une variable définie par Flight::set().
Flight::set(string $key, mixed $value) // Définit une variable dans le moteur Flight.
Flight::has(string $key) // Vérifie si une variable est définie.
Flight::clear(array|string $key = []) // Efface une variable.
Flight::init() // Initialise le framework à ses paramètres par défaut.
Flight::app() // Obtient l'instance de l'objet application
Flight::request() // Obtient l'instance de l'objet requête
Flight::response() // Obtient l'instance de l'objet réponse
Flight::router() // Obtient l'instance de l'objet routeur
Flight::view() // Obtient l'instance de l'objet vue
```

#### Méthodes extensibles

```php
Flight::start() // Démarre le framework.
Flight::stop() // Arrête le framework et envoie une réponse.
Flight::halt(int $code = 200, string $message = '') // Arrête le framework avec un code de statut et un message optionnels.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mappe un motif d'URL à un callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mappe un motif d'URL de requête POST à un callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mappe un motif d'URL de requête PUT à un callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mappe un motif d'URL de requête PATCH à un callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mappe un motif d'URL de requête DELETE à un callback.
Flight::group(string $pattern, callable $callback) // Crée un groupement pour les URLs, le motif doit être une chaîne.
Flight::getUrl(string $name, array $params = []) // Génère une URL basée sur un alias de route.
Flight::redirect(string $url, int $code) // Redirige vers une autre URL.
Flight::download(string $filePath) // Télécharge un fichier.
Flight::render(string $file, array $data, ?string $key = null) // Rend un fichier de template.
Flight::error(Throwable $error) // Envoie une réponse HTTP 500.
Flight::notFound() // Envoie une réponse HTTP 404.
Flight::etag(string $id, string $type = 'string') // Effectue le cache HTTP ETag.
Flight::lastModified(int $time) // Effectue le cache HTTP de dernière modification.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envoie une réponse JSON et arrête le framework.
Flight::onEvent(string $event, callable $callback) // Enregistre un écouteur d'événement.
Flight::triggerEvent(string $event, ...$args) // Déclenche un événement.
```

Toute méthode personnalisée ajoutée avec `map` et `register` peut également être filtrée. Pour des exemples sur la façon de filtrer ces méthodes, voir le guide [Filtering Methods](/learn/filtering).

#### Classes du framework extensibles

Il existe plusieurs classes dont vous pouvez surcharger la fonctionnalité en les étendant et
en enregistrant votre propre classe. Ces classes sont :

```php
Flight::app() // Classe Application - étendre la classe flight\Engine
Flight::request() // Classe Requête - étendre la classe flight\net\Request
Flight::response() // Classe Réponse - étendre la classe flight\net\Response
Flight::router() // Classe Routeur - étendre la classe flight\net\Router
Flight::view() // Classe Vue - étendre la classe flight\template\View
Flight::eventDispatcher() // Classe Dispatch d'événements - étendre la classe flight\core\Dispatcher
```

### Mappage de méthodes personnalisées

Pour mapper votre propre méthode personnalisée simple, vous utilisez la fonction `map` :

```php
// Mapper votre méthode
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Appeler votre méthode personnalisée
Flight::hello('Bob');
```

Bien qu'il soit possible de créer des méthodes personnalisées simples, il est recommandé de simplement créer
des fonctions standard en PHP. Cela offre l'autocomplétion dans les IDE et est plus facile à lire.
L'équivalent du code ci-dessus serait :

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Cela est utilisé plus souvent lorsque vous devez passer des variables dans votre méthode pour obtenir une
valeur attendue. Utiliser la méthode `register()` comme ci-dessous est plus pour passer une configuration
et ensuite appeler votre classe pré-configurée.

### Enregistrement de classes personnalisées

Pour enregistrer votre propre classe et la configurer, vous utilisez la fonction `register`. L'avantage que cela a sur map() est que vous pouvez réutiliser la même classe lorsque vous appelez cette fonction (ce qui serait utile avec `Flight::db()` pour partager la même instance).

```php
// Enregistrer votre classe
Flight::register('user', User::class);

// Obtenir une instance de votre classe
$user = Flight::user();
```

La méthode register vous permet également de passer des paramètres au
constructeur de votre classe. Ainsi, lorsque vous chargez votre classe personnalisée, elle sera pré-initialisée.
Vous pouvez définir les paramètres du constructeur en passant un tableau supplémentaire.
Voici un exemple de chargement d'une connexion à la base de données :

```php
// Enregistrer la classe avec des paramètres de constructeur
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtenir une instance de votre classe
// Cela créera un objet avec les paramètres définis
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// et si vous en avez besoin plus tard dans votre code, vous appelez simplement la même méthode
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si vous passez un paramètre de callback supplémentaire, il sera exécuté immédiatement
après la construction de la classe. Cela vous permet d'effectuer toute procédure de configuration pour votre
nouvel objet. La fonction de callback prend un paramètre, une instance du nouvel objet.

```php
// Le callback recevra l'objet qui a été construit
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Par défaut, chaque fois que vous chargez votre classe, vous obtiendrez une instance partagée.
Pour obtenir une nouvelle instance d'une classe, passez simplement `false` en tant que paramètre :

```php
// Instance partagée de la classe
$shared = Flight::db();

// Nouvelle instance de la classe
$new = Flight::db(false);
```

> **Note :** Gardez à l'esprit que les méthodes mappées ont la priorité sur les classes enregistrées. Si vous
déclarez les deux en utilisant le même nom, seule la méthode mappée sera invoquée.

### Exemples

Voici quelques exemples de la façon dont vous pouvez étendre Flight avec une fonctionnalité qui n'est pas intégrée au noyau.

#### Journalisation

Flight n'a pas de système de journalisation intégré, cependant, il est vraiment facile
d'utiliser une bibliothèque de journalisation avec Flight. Voici un exemple utilisant la
bibliothèque Monolog :

```php
// services.php

// Enregistrer le logger avec Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Maintenant qu'il est enregistré, vous pouvez l'utiliser dans votre application :

```php
// Dans votre contrôleur ou route
Flight::log()->warning('This is a warning message');
```

Cela journalisera un message dans le fichier de log que vous avez spécifié. Et si vous voulez journaliser quelque chose quand une
erreur se produit ? Vous pouvez utiliser la méthode `error` :

```php
// Dans votre contrôleur ou route
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Afficher votre page d'erreur personnalisée
	include 'errors/500.html';
});
```

Vous pourriez également créer un système APM (Application Performance Monitoring) de base
en utilisant les méthodes `before` et `after` :

```php
// Dans votre fichier services.php

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// Vous pourriez également ajouter vos en-têtes de requête ou de réponse
	// pour les journaliser également (soyez prudent car cela serait beaucoup de 
	// données si vous avez beaucoup de requêtes)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### Mise en cache

Flight n'a pas de système de mise en cache intégré, cependant, il est vraiment facile
d'utiliser une bibliothèque de mise en cache avec Flight. Voici un exemple utilisant la
bibliothèque [PHP File Cache](/awesome-plugins/php_file_cache) :

```php
// services.php

// Enregistrer le cache avec Flight
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

Maintenant qu'il est enregistré, vous pouvez l'utiliser dans votre application :

```php
// Dans votre contrôleur ou route
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// Effectuer un traitement pour obtenir les données
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // cache pour 1 heure
}
```

#### Instanciation facile d'objets DIC

Si vous utilisez un DIC (Dependency Injection Container) dans votre application,
vous pouvez utiliser Flight pour vous aider à instancier vos objets. Voici un exemple utilisant
la bibliothèque [Dice](https://github.com/level-2/Dice) :

```php
// services.php

// créer un nouveau conteneur
$container = new \Dice\Dice;
// n'oubliez pas de le réassigner à lui-même comme ci-dessous !
$container = $container->addRule('PDO', [
	// shared signifie que le même objet sera retourné à chaque fois
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// maintenant nous pouvons créer une méthode mappable pour créer n'importe quel objet. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Cela enregistre le gestionnaire de conteneur pour que Flight sache l'utiliser pour les contrôleurs/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// disons que nous avons la classe d'exemple suivante qui prend un objet PDO dans le constructeur
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// code qui envoie un email
	}
}

// Et enfin vous pouvez créer des objets en utilisant l'injection de dépendances
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

Génial, non ?

## Voir aussi
- [Dependency Injection Container](/learn/dependency-injection-container) - Comment utiliser un DIC avec Flight.
- [File Cache](/awesome-plugins/php_file_cache) - Exemple d'utilisation d'une bibliothèque de mise en cache avec Flight.

## Dépannage
- Rappelez-vous que les méthodes mappées ont la priorité sur les classes enregistrées. Si vous déclarez les deux en utilisant le même nom, seule la méthode mappée sera invoquée.

## Journal des modifications
- v2.0 - Version initiale.