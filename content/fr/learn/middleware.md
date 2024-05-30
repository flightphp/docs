# Middleware de Route

Flight prend en charge le middleware de route et de groupe de route. Le middleware est une fonction qui est exécutée avant (ou après) le rappel de la route. C'est un excellent moyen d'ajouter des vérifications d'authentification API dans votre code, ou de valider si l'utilisateur a la permission d'accéder à la route.

## Middleware Basique

Voici un exemple basique:

```php
// Si vous fournissez uniquement une fonction anonyme, elle sera exécutée avant le rappel de la route.
// Il n'y a pas de fonctions de middleware "après" sauf pour les classes (voir ci-dessous)
Flight::route('/chemin', function() { echo 'Je suis là!'; })->addMiddleware(function() {
	echo 'Middleware en premier!';
});

Flight::start();

// Cela affichera "Middleware en premier! Je suis là!"
```

Il y a quelques notes très importantes sur le middleware que vous devez connaître avant de les utiliser:
- Les fonctions de middleware sont exécutées dans l'ordre où elles sont ajoutées à la route. L'exécution est similaire à celle de la façon dont [Slim Framework gère cela](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Les fonctions avant sont exécutées dans l'ordre ajouté, et les fonctions après sont exécutées dans l'ordre inverse.
- Si votre fonction de middleware renvoie false, toute l'exécution s'arrête et une erreur 403 Forbidden est déclenchée. Vous voudrez probablement gérer cela de manière plus élégante avec un `Flight::redirect()` ou quelque chose de similaire.
- Si vous avez besoin de paramètres de votre route, ils seront transmis sous forme d'un seul tableau à votre fonction de middleware (`function($params) { ... }` ou `public function before($params) {}`). La raison en est que vous pouvez structurer vos paramètres en groupes et que dans certains de ces groupes, vos paramètres peuvent apparaître dans un ordre différent, ce qui romprait la fonction de middleware en faisant référence au mauvais paramètre. De cette manière, vous pouvez y accéder par nom plutôt que par position.
- Si vous transmettez uniquement le nom du middleware, il sera automatiquement exécuté par le [conteneur d'injection de dépendances](dependency-injection-container) et le middleware sera exécuté avec les paramètres dont il a besoin. Si vous n'avez pas de conteneur d'injection de dépendances enregistré, il transmettra l'instance `flight\Engine` dans le `__construct()`.

## Classes de Middleware

Le middleware peut également être enregistré en tant que classe. Si vous avez besoin de la fonctionnalité "après", vous **devez** utiliser une classe.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware en premier!';
	}

	public function after($params) {
		echo 'Middleware en dernier!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/chemin', function() { echo 'Je suis là!'; })->addMiddleware($MyMiddleware); // également ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Cela affichera "Middleware en premier! Je suis là! Middleware en dernier!"
```

## Gestion des Erreurs de Middleware

Disons que vous avez un middleware d'authentification et que vous voulez rediriger l'utilisateur vers une page de connexion s'il n'est pas authentifié. Vous avez quelques options à votre disposition:

1. Vous pouvez renvoyer false depuis la fonction de middleware et Flight renverra automatiquement une erreur 403 Forbidden, mais sans personnalisation.
1. Vous pouvez rediriger l'utilisateur vers une page de connexion en utilisant `Flight::redirect()`.
1. Vous pouvez créer une erreur personnalisée dans le middleware et arrêter l'exécution de la route.

### Exemple Basique

Voici un exemple simple avec un return false;:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// puisque c'est vrai, tout continue normalement
	}
}
```

### Exemple de Redirection

Voici un exemple de redirection de l'utilisateur vers une page de connexion:
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

### Exemple d'Erreur Personnalisée

Disons que vous devez renvoyer une erreur JSON car vous créez une API. Vous pouvez le faire comme ceci:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::json(['error' => 'Vous devez être connecté pour accéder à cette page.'], 403);
			exit;
			// ou
			Flight::halt(403, json_encode(['error' => 'Vous devez être connecté pour accéder à cette page.']);
		}
	}
}
```

## Groupement de Middleware

Vous pouvez ajouter un groupe de route, et alors chaque route de ce groupe aura également le même middleware. C'est utile si vous devez regrouper un ensemble de routes avec un middleware d'Auth pour vérifier la clé API dans l'en-tête.

```php

// ajouté à la fin de la méthode de groupe
Flight::group('/api', function() {

	// Cette route en apparence "vide" correspondra en réalité à /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/utilisateurs', function() { echo 'utilisateurs'; }, false, 'utilisateurs');
	Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');
}, [ new ApiAuthMiddleware() ]);
```

Si vous souhaitez appliquer un middleware global à toutes vos routes, vous pouvez ajouter un groupe "vide":

```php

// ajouté à la fin de la méthode de groupe
Flight::group('', function() {
	Flight::route('/utilisateurs', function() { echo 'utilisateurs'; }, false, 'utilisateurs');
	Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');
}, [ new ApiAuthMiddleware() ]);
```