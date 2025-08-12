# Middleware de Route

Flight prend en charge les middleware de route et de groupe de routes. Un middleware est une fonction qui s'exécute avant (ou après) le rappel de route. C'est un excellent moyen d'ajouter des vérifications d'authentification d'API dans votre code, ou de valider que l'utilisateur a la permission d'accéder à la route.

## Middleware de Base

Voici un exemple de base :

```php
// Si vous fournissez uniquement une fonction anonyme, elle sera exécutée avant le rappel de route. 
// il n'y a pas de fonctions middleware "après" sauf pour les classes (voir ci-dessous)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Cela affichera "Middleware first! Here I am!"
```

Il y a quelques notes très importantes sur les middleware que vous devriez connaître avant de les utiliser :
- Les fonctions middleware sont exécutées dans l'ordre dans lequel elles sont ajoutées à la route. L'exécution est similaire à la façon dont [Slim Framework handles this](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Les "before" sont exécutés dans l'ordre ajouté, et les "after" sont exécutés dans l'ordre inverse.
- Si votre fonction middleware retourne false, toute exécution est arrêtée et une erreur 403 Forbidden est générée. Vous voudrez probablement gérer cela de manière plus élégante avec un `Flight::redirect()` ou quelque chose de similaire.
- Si vous avez besoin de paramètres de votre route, ils seront passés sous forme d'un seul tableau à votre fonction middleware. (`function($params) { ... }` ou `public function before($params) {}`). La raison est que vous pouvez structurer vos paramètres en groupes et dans certains de ces groupes, vos paramètres pourraient apparaître dans un ordre différent, ce qui casserait la fonction middleware en se référant au mauvais paramètre. Ainsi, vous pouvez y accéder par nom au lieu de position.
- Si vous passez simplement le nom du middleware, il sera automatiquement exécuté par le [dependency injection container](dependency-injection-container) et le middleware sera exécuté avec les paramètres dont il a besoin. Si vous n'avez pas de conteneur d'injection de dépendances enregistré, il passera l'instance de `flight\Engine` dans le `__construct()`.

## Classes de Middleware

Les middleware peuvent également être enregistrés en tant que classe. Si vous avez besoin de la fonctionnalité "after", vous **devez** utiliser une classe.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';  // Affiche d'abord le middleware
	}

	public function after($params) {
		echo 'Middleware last!';  // Affiche en dernier le middleware
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // aussi ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Cela affichera "Middleware first! Here I am! Middleware last!"
```

## Gestion des Erreurs de Middleware

Supposons que vous ayez un middleware d'authentification et que vous vouliez rediriger l'utilisateur vers une page de connexion s'il n'est pas authentifié. Vous avez quelques options à votre disposition :

1. Vous pouvez retourner false de la fonction middleware et Flight renverra automatiquement une erreur 403 Forbidden, mais sans personnalisation.
1. Vous pouvez rediriger l'utilisateur vers une page de connexion en utilisant `Flight::redirect()`.
1. Vous pouvez créer une erreur personnalisée dans le middleware et arrêter l'exécution de la route.

### Exemple de Base

Voici un exemple simple de retour false :
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {  // Vérifie si l'utilisateur est connecté
			return false;
		}

		// comme c'est vrai, tout continue normalement
	}
}
```

### Exemple de Redirection

Voici un exemple de redirection de l'utilisateur vers une page de connexion :
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {  // Vérifie si l'utilisateur est connecté
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Exemple d'Erreur Personnalisée

Supposons que vous devez générer une erreur JSON parce que vous construisez une API. Vous pouvez faire cela comme suit :
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];  // Récupère l'en-tête d'autorisation
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);  // Arrête avec une erreur JSON
			// ou
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// ou
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']));
		}
	}
}
```

## Regroupement de Middleware

Vous pouvez ajouter un groupe de routes, et ensuite chaque route dans ce groupe aura le même middleware. C'est utile si vous devez regrouper un tas de routes par exemple avec un middleware d'Auth pour vérifier la clé API dans l'en-tête.

```php
// ajouté à la fin de la méthode group
Flight::group('/api', function() {

	// Cette route "vide" correspondra à /api
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
	// Et c'est toujours /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // ou [ new ApiAuthMiddleware() ], c'est la même chose
```