# Middleware de Route

Flight prend en charge le middleware de route et de groupe de route. Le middleware est une fonction qui est exécutée avant (ou après) le rappel de la route. C'est une excellente façon d'ajouter des vérifications d'authentification API dans votre code, ou de valider que l'utilisateur a la permission d'accéder à la route.

## Middleware de Base

Voici un exemple de base :

```php
// Si vous ne fournissez qu'une fonction anonyme, elle sera exécutée avant le rappel de la route. 
// Il n'y a pas de fonctions de middleware "après", sauf pour les classes (voir ci-dessous)
Flight::route('/chemin', function() { echo 'Me voici !'; })->addMiddleware(function() {
	echo 'Middleware en premier !';
});

Flight::start();

// Cela affichera "Middleware en premier ! Me voici !"
```

Il y a quelques notes très importantes sur le middleware dont vous devez être conscient avant de les utiliser :
- Les fonctions de middleware sont exécutées dans l'ordre où elles sont ajoutées à la route. L'exécution est similaire à la façon dont [Slim Framework gère ceci](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Les "avant" sont exécutés dans l'ordre ajouté, et les "après" sont exécutés dans l'ordre inverse.
- Si votre fonction de middleware renvoie false, toute l'exécution est arrêtée et une erreur 403 Forbidden est déclenchée. Vous voudrez probablement gérer ceci de manière plus gracieuse avec un `Flight::redirect()` ou quelque chose de similaire.
- Si vous avez besoin de paramètres de votre route, ils seront transmis dans un seul tableau à votre fonction de middleware. (`function($params) { ... }` ou `public function before($params) {}`). La raison en est que vous pouvez structurer vos paramètres en groupes et dans certains de ces groupes, vos paramètres peuvent réellement apparaître dans un ordre différent, ce qui romprait la fonction de middleware en faisant référence au mauvais paramètre. De cette façon, vous pouvez y accéder par nom au lieu de position.

## Classes de Middleware

Le middleware peut également être enregistré en tant que classe. Si vous avez besoin de la fonctionnalité "après", vous **devez** utiliser une classe.

```php
class MonMiddleware {
	public function before($params) {
		echo 'Middleware en premier !';
	}

	public function after($params) {
		echo 'Middleware en dernier !';
	}
}

$MonMiddleware = new MonMiddleware();
Flight::route('/chemin', function() { echo 'Me voici!'; })->addMiddleware($MonMiddleware); // aussi ->addMiddleware([ $MonMiddleware, $MonMiddleware2 ]);

Flight::start();

// Cela affichera "Middleware en premier ! Me voici! Middleware en dernier !"
```

## Regroupement de Middleware

Vous pouvez ajouter un groupe de route, et ensuite chaque route de ce groupe aura le même middleware également. Cela est utile si vous devez regrouper un ensemble de routes par exemple avec un middleware d'authentification pour vérifier la clé API dans l'en-tête.

```php

// ajouté à la fin de la méthode de groupe
Flight::group('/api', function() {

	// Cette route en apparence "vide" correspondra en réalité à /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/utilisateurs', function() { echo 'utilisateurs'; }, false, 'utilisateurs');
	Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur :'.$id; }, false, 'vue_utilisateur');
}, [ new MiddlewareAuthApi() ]);
```

Si vous souhaitez appliquer un middleware global à toutes vos routes, vous pouvez ajouter un groupe "vide" :

```php

// ajouté à la fin de la méthode de groupe
Flight::group('', function() {
	Flight::route('/utilisateurs', function() { echo 'utilisateurs'; }, false, 'utilisateurs');
	Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur :'.$id; }, false, 'vue_utilisateur');
}, [ new MiddlewareAuthApi() ]);
```