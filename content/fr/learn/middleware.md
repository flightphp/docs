# Middleware de Route

Flight prend en charge les middleware de route et de groupe de route. Un middleware est une fonction qui est exécutée avant (ou après) le rappel de la route. C'est un excellent moyen d'ajouter des vérifications d'authentification API dans votre code, ou de valider que l'utilisateur a la permission d'accéder à la route.

## Middleware de Base

Voici un exemple de base :

```php
// Si vous ne fournissez qu'une fonction anonyme, elle sera exécutée avant le rappel de la route. Il n'y a pas de fonctions middleware "après" à l'exception des classes (voir ci-dessous)
Flight::route('/chemin', function() { echo 'Me voici !'; })->addMiddleware(function() {
	echo 'Middleware en premier !';
});

Flight::start();

// Cela affichera "Middleware en premier ! Me voici !"
```

Il est important de noter quelques points essentiels concernant les middleware avant de les utiliser :
- Les fonctions middleware sont exécutées dans l'ordre où elles sont ajoutées à la route. L'exécution est similaire à [la manière dont Slim Framework gère cela](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Les "before" sont exécutés dans l'ordre ajouté, et les "after" sont exécutés dans l'ordre inverse.
- Si votre fonction middleware renvoie false, toute l'exécution est arrêtée et une erreur 403 Forbidden est déclenchée. Vous voudrez probablement gérer cela de manière plus gracieuse avec un `Flight::redirect()` ou quelque chose de similaire.
- Si vous avez besoin de paramètres de votre route, ils seront transmis dans un tableau unique à votre fonction middleware (`function($params) { ... }` ou `public function before($params) {}`). La raison en est que vous pouvez structurer vos paramètres en groupes et dans certains de ces groupes, vos paramètres peuvent en fait apparaître dans un ordre différent qui casserait la fonction middleware en faisant référence au mauvais paramètre. De cette manière, vous pouvez y accéder par nom au lieu de la position.
- Si vous passez seulement le nom du middleware, il sera automatiquement exécuté par le [container d'injection de dépendance](dependency-injection-container) et le middleware sera exécuté avec les paramètres nécessaires. Si vous n'avez pas de container d'injection de dépendance enregistré, il passera l'instance `flight\Engine` dans le `__construct()`.

## Classes Middleware

Les middleware peuvent également être enregistrés sous forme de classe. Si vous avez besoin de la fonctionnalité "after", vous **devez** utiliser une classe.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware en premier !';
	}

	public function after($params) {
		echo 'Middleware en dernier !';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/chemin', function() { echo 'Me voici ! '; })->addMiddleware($MyMiddleware); // également ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Cela affichera "Middleware en premier ! Me voici ! Middleware en dernier !"
```

## Gestion des Erreurs de Middleware

Disons que vous avez un middleware d'authentification et que vous souhaitez rediriger l'utilisateur vers une page de connexion s'il n'est pas authentifié. Vous avez quelques options à votre disposition :

1. Vous pouvez renvoyer false depuis la fonction middleware et Flight renverra automatiquement une erreur 403 Forbidden, mais sans personnalisation.
1. Vous pouvez rediriger l'utilisateur vers une page de connexion en utilisant `Flight::redirect()`.
1. Vous pouvez créer une erreur personnalisée à l'intérieur du middleware et arrêter l'exécution de la route.

### Exemple de Base

Voici un exemple simple avec return false; :
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// comme c'est vrai, tout continue simplement
	}
}
```

### Exemple de Redirection

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

### Exemple d'Erreur Personnalisée

Disons que vous devez déclencher une erreur JSON car vous construisez une API. Vous pouvez le faire de cette manière :
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'Vous devez être connecté pour accéder à cette page.'], 403);
			// ou
			Flight::json(['error' => 'Vous devez être connecté pour accéder à cette page.'], 403);
			exit;
			// ou
			Flight::halt(403, json_encode(['error' => 'Vous devez être connecté pour accéder à cette page.']);
		}
	}
}
```

## Regroupement de Middleware

Vous pouvez ajouter un groupe de route, puis chaque route de ce groupe aura également le même middleware. C'est utile si vous devez regrouper un ensemble de routes par exemple avec un middleware d'Auth pour vérifier la clé API dans l'en-tête.

```php

// ajouté à la fin de la méthode group
Flight::group('/api', function() {

	// Cette route en apparence "vide" correspondra en fait à /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Cela correspondra à /api/utilisateurs
    Flight::route('/utilisateurs', function() { echo 'utilisateurs'; }, false, 'utilisateurs');
	// Cela correspondra à /api/utilisateurs/1234
	Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur :'.$id; }, false, 'vue_utilisateur');
}, [ new ApiAuthMiddleware() ]);
```

Si vous souhaitez appliquer un middleware global à toutes vos routes, vous pouvez ajouter un groupe "vide" :

```php

// ajouté à la fin de la méthode group
Flight::group('', function() {

	// C'est toujours /utilisateurs
	Flight::route('/utilisateurs', function() { echo 'utilisateurs'; }, false, 'utilisateurs');
	// Et c'est toujours /utilisateurs/1234
	Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur :'.$id; }, false, 'vue_utilisateur');
}, [ new ApiAuthMiddleware() ]);
```