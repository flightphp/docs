# Intergiciel de route

Flight prend en charge l'intergiciel de route et le regroupement d'intergiciel de route. L'intergiciel est une fonction qui s'exécute avant (ou après) le rappel de route. C'est une excellente manière d'ajouter des vérifications d'authentification d'API dans votre code, ou de valider que l'utilisateur a la permission d'accéder à la route.

## Intergiciel de base

Voici un exemple de base :

```php
// Si vous ne fournissez qu'une fonction anonyme, elle sera exécutée avant le rappel de route. 
// il n'y a pas de fonctions d'intergiciel "après" sauf pour les classes (voir ci-dessous)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Cela affichera "Middleware first! Here I am!"
```

Il y a quelques notes très importantes sur l'intergiciel que vous devriez connaître avant de les utiliser :
- Les fonctions d'intergiciel sont exécutées dans l'ordre dans lequel elles sont ajoutées à la route. L'exécution est similaire à la façon dont [Slim Framework gère cela](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Les "avant" sont exécutés dans l'ordre ajouté, et les "après" sont exécutés en ordre inverse.
- Si votre fonction d'intergiciel retourne false, toute exécution est arrêtée et une erreur 403 Forbidden est générée. Vous voudrez probablement gérer cela de manière plus élégante avec un `Flight::redirect()` ou quelque chose de similaire.
- Si vous avez besoin de paramètres de votre route, ils seront transmis sous forme d'un seul tableau à votre fonction d'intergiciel. (`function($params) { ... }` ou `public function before($params) {}`). La raison est que vous pouvez structurer vos paramètres en groupes et dans certains de ces groupes, vos paramètres peuvent apparaître dans un ordre différent, ce qui casserait la fonction d'intergiciel en se référant au mauvais paramètre. Ainsi, vous pouvez y accéder par nom au lieu de position.
- Si vous passez simplement le nom de l'intergiciel, il sera automatiquement exécuté par le [conteneur d'injection de dépendances](dependency-injection-container) et l'intergiciel sera exécuté avec les paramètres dont il a besoin. Si vous n'avez pas de conteneur d'injection de dépendances enregistré, il passera l'instance de `flight\Engine` dans le `__construct()`.

## Classes d'intergiciel

L'intergiciel peut être enregistré en tant que classe aussi. Si vous avez besoin de la fonctionnalité "après", vous **devez** utiliser une classe.

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // aussi ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Cela affichera "Middleware first! Here I am! Middleware last!"
```

## Gestion des erreurs d'intergiciel

Supposons que vous ayez un intergiciel d'authentification et que vous souhaitiez rediriger l'utilisateur vers une page de connexion s'il n'est pas authentifié. Vous avez quelques options à votre disposition :

1. Vous pouvez retourner false de la fonction d'intergiciel et Flight renverra automatiquement une erreur 403 Forbidden, mais sans personnalisation.
1. Vous pouvez rediriger l'utilisateur vers une page de connexion en utilisant `Flight::redirect()`.
1. Vous pouvez créer une erreur personnalisée dans l'intergiciel et arrêter l'exécution de la route.

### Exemple de base

Voici un exemple simple de retour false :
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// comme c'est vrai, tout continue normalement
	}
}
```

### Exemple de redirection

Voici un exemple de redirection de l'utilisateur vers une page de connexion :
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Exemple d'erreur personnalisée

Supposons que vous devez générer une erreur JSON car vous construisez une API. Vous pouvez le faire comme ceci :
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// ou
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// ou
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']));
		}
	}
}
```

## Regroupement d'intergiciel

Vous pouvez ajouter un groupe de routes, et ensuite chaque route dans ce groupe aura le même intergiciel. C'est utile si vous devez regrouper un tas de routes par exemple avec un intergiciel d'Auth pour vérifier la clé API dans l'en-tête.

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

Si vous souhaitez appliquer un intergiciel global à toutes vos routes, vous pouvez ajouter un groupe "vide" :

```php
// ajouté à la fin de la méthode group
Flight::group('', function() {

	// C'est toujours /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Et c'est toujours /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // ou [ new ApiAuthMiddleware() ], c'est la même chose
```