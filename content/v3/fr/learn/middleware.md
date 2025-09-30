# Middleware

## Aperçu

Flight prend en charge les middlewares de route et de groupe de routes. Le middleware est une partie de votre application où le code est exécuté avant 
(ou après) le rappel de la route. C'est une excellente façon d'ajouter des vérifications d'authentification API dans votre code, ou de valider que 
l'utilisateur a la permission d'accéder à la route.

## Comprendre

Les middlewares peuvent grandement simplifier votre application. Au lieu d'une inheritance de classes abstraites complexes ou de substitutions de méthodes, les middlewares 
vous permettent de contrôler vos routes en assignant votre logique d'application personnalisée. Vous pouvez penser aux middlewares comme à
un sandwich. Vous avez du pain à l'extérieur, et puis des couches de garnitures comme de la laitue, des tomates, de la viande et du fromage. Imaginez
que chaque requête est comme prendre une bouchée du sandwich où vous mangez les couches extérieures en premier et progressez vers le cœur.

Voici une visualisation de la façon dont les middlewares fonctionnent. Ensuite, nous vous montrerons un exemple pratique de son fonctionnement.

```text
Requête utilisateur à l'URL /api ----> 
	Middleware->before() exécuté ----->
		Fonction callable/méthode attachée à /api exécutée et réponse générée ------>
	Middleware->after() exécuté ----->
L'utilisateur reçoit la réponse du serveur
```

Et voici un exemple pratique :

```text
L'utilisateur navigue vers l'URL /dashboard
	LoggedInMiddleware->before() s'exécute
		before() vérifie une session connectée valide
			si oui, ne rien faire et continuer l'exécution
			si non, rediriger l'utilisateur vers /login
				Fonction callable/méthode attachée à /api exécutée et réponse générée
	LoggedInMiddleware->after() n'a rien de défini donc il laisse l'exécution continuer
L'utilisateur reçoit le HTML du tableau de bord du serveur
```

### Ordre d'exécution

Les fonctions de middleware sont exécutées dans l'ordre où elles sont ajoutées à la route. L'exécution est similaire à la façon dont [Slim Framework gère cela](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).

Les méthodes `before()` sont exécutées dans l'ordre d'ajout, et les méthodes `after()` sont exécutées en ordre inverse.

Ex : Middleware1->before(), Middleware2->before(), Middleware2->after(), Middleware1->after().

## Utilisation de base

Vous pouvez utiliser des middlewares comme n'importe quelle méthode callable, y compris une fonction anonyme ou une classe (recommandé)

### Fonction anonyme

Voici un exemple simple :

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Cela affichera "Middleware first! Here I am!"
```

> **Note :** Lorsque vous utilisez une fonction anonyme, la seule méthode interprétée est une méthode `before()`. Vous **ne pouvez pas** définir un comportement `after()` avec une classe anonyme.

### Utilisation de classes

Les middlewares peuvent (et devraient) être enregistrés comme une classe. Si vous avez besoin de la fonctionnalité "after", vous **devez** utiliser une classe.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); 
// aussi ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Cela affichera "Middleware first! Here I am! Middleware last!"
```

Vous pouvez aussi simplement définir le nom de la classe de middleware et elle instanciera la classe.

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **Note :** Si vous passez simplement le nom du middleware, il sera automatiquement exécuté par le [conteneur d'injection de dépendances](dependency-injection-container) et le middleware sera exécuté avec les paramètres dont il a besoin. Si vous n'avez pas de conteneur d'injection de dépendances enregistré, il passera par défaut l'instance `flight\Engine` dans le `__construct(Engine $app)`.

### Utilisation de routes avec paramètres

Si vous avez besoin de paramètres de votre route, ils seront passés dans un seul tableau à votre fonction de middleware. (`function($params) { ... }` ou `public function before($params) { ... }`). La raison en est que vous pouvez structurer vos paramètres en groupes et dans certains de ces groupes, vos paramètres peuvent apparaître dans un ordre différent, ce qui casserait la fonction de middleware en se référant au mauvais paramètre. De cette façon, vous pouvez y accéder par nom au lieu de position.

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId peut ou non être passé
		$jobId = $params['jobId'] ?? 0;

		// peut-être que s'il n'y a pas d'ID de job, vous n'avez pas besoin de rechercher quoi que ce soit.
		if($jobId === 0) {
			return;
		}

		// effectuer une recherche de quelque sorte dans votre base de données
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// Ce groupe ci-dessous obtient toujours le middleware parent
	// Mais les paramètres sont passés dans un seul tableau 
	// dans le middleware.
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// plus de routes...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### Groupement de routes avec middleware

Vous pouvez ajouter un groupe de routes, et puis chaque route dans ce groupe aura le même middleware. C'est 
utile si vous avez besoin de grouper un tas de routes par un middleware Auth pour vérifier la clé API dans l'en-tête.

```php

// ajouté à la fin de la méthode group
Flight::group('/api', function() {

	// Cette route "vide" correspondra en fait à /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Cela correspondra à /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Cela correspondra à /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Si vous voulez appliquer un middleware global à toutes vos routes, vous pouvez ajouter un groupe "vide" :

```php

// ajouté à la fin de la méthode group
Flight::group('', function() {

	// C'est toujours /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Et cela reste /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // ou [ new ApiAuthMiddleware() ], c'est la même chose
```

### Cas d'utilisation courants

#### Validation de clé API
Si vous vouliez protéger vos routes `/api` en vérifiant que la clé API est correcte, vous pouvez facilement gérer cela avec un middleware.

```php
use flight\Engine;

class ApiMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}
	
	public function before(array $params) {
		$authorizationHeader = $this->app->request()->getHeader('Authorization');
		$apiKey = str_replace('Bearer ', '', $authorizationHeader);

		// effectuer une recherche dans votre base de données pour la clé api
		$apiKeyHash = hash('sha256', $apiKey);
		$hasValidApiKey = !!$this->db()->fetchField("SELECT 1 FROM api_keys WHERE hash = ? AND valid_date >= NOW()", [ $apiKeyHash ]);

		if($hasValidApiKey !== true) {
			$this->app->jsonHalt(['error' => 'Invalid API Key']);
		}
	}
}

// routes.php
$router->group('/api', function(Router $router) {
	$router->get('/users', [ ApiController::class, 'getUsers' ]);
	$router->get('/companies', [ ApiController::class, 'getCompanies' ]);
	// plus de routes...
}, [ ApiMiddleware::class ]);
```

Maintenant toutes vos routes API sont protégées par ce middleware de validation de clé API que vous avez configuré ! Si vous ajoutez plus de routes dans le groupe de routeur, elles auront instantanément la même protection !

#### Validation de connexion

Voulez-vous protéger certaines routes pour qu'elles ne soient disponibles que pour les utilisateurs connectés ? Cela peut facilement être réalisé avec un middleware !

```php
use flight\Engine;

class LoggedInMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$session = $this->app->session();
		if($session->get('logged_in') !== true) {
			$this->app->redirect('/login');
			exit;
		}
	}
}

// routes.php
$router->group('/admin', function(Router $router) {
	$router->get('/dashboard', [ DashboardController::class, 'index' ]);
	$router->get('/clients', [ ClientController::class, 'index' ]);
	// plus de routes...
}, [ LoggedInMiddleware::class ]);
```

#### Validation de paramètres de route

Voulez-vous protéger vos utilisateurs en changeant les valeurs dans l'URL pour accéder à des données qu'ils ne devraient pas ? Cela peut être résolu avec un middleware !

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];
		$jobId = $params['jobId'];

		// effectuer une recherche de quelque sorte dans votre base de données
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {
	$router->get('', [ JobController::class, 'view' ]);
	$router->put('', [ JobController::class, 'update' ]);
	$router->delete('', [ JobController::class, 'delete' ]);
	// plus de routes...
}, [ RouteSecurityMiddleware::class ]);
```

## Gestion de l'exécution des middlewares

Disons que vous avez un middleware d'authentification et que vous voulez rediriger l'utilisateur vers une page de connexion s'il n'est pas 
authentifié. Vous avez plusieurs options à votre disposition :

1. Vous pouvez retourner false de la fonction middleware et Flight retournera automatiquement une erreur 403 Forbidden, mais sans personnalisation.
1. Vous pouvez rediriger l'utilisateur vers une page de connexion en utilisant `Flight::redirect()`.
1. Vous pouvez créer une erreur personnalisée dans le middleware et arrêter l'exécution de la route.

### Simple et direct

Voici un exemple simple de `return false;` :

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// puisque c'est vrai, tout continue simplement
	}
}
```

### Exemple de redirection

Voici un exemple de redirection de l'utilisateur vers une page de connexion :
```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Exemple d'erreur personnalisée

Disons que vous avez besoin de lancer une erreur JSON parce que vous construisez une API. Vous pouvez faire cela comme ceci :
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// ou
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// ou
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## Voir aussi
- [Routing](/learn/routing) - Comment mapper les routes vers les contrôleurs et rendre les vues.
- [Requests](/learn/requests) - Comprendre comment gérer les requêtes entrantes.
- [Responses](/learn/responses) - Comment personnaliser les réponses HTTP.
- [Dependency Injection](/learn/dependency-injection-container) - Simplifier la création et la gestion d'objets dans les routes.
- [Why a Framework?](/learn/why-frameworks) - Comprendre les avantages d'utiliser un framework comme Flight.
- [Middleware Execution Strategy Example](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## Dépannage
- Si vous avez une redirection dans votre middleware, mais que votre application ne semble pas rediriger, assurez-vous d'ajouter une instruction `exit;` dans votre middleware.

## Changelog
- v3.1: Ajout du support pour les middlewares.