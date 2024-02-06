# Routage

Le routage dans Flight est effectué en associant un modèle d'URL à une fonction de rappel.

```php
Flight::route('/', function(){
    echo 'bonjour le monde!';
});
```

Le rappel peut être n'importe quel objet qui est appelable. Ainsi, vous pouvez utiliser une fonction régulière :

```php
function bonjour(){
    echo 'bonjour le monde!';
}

Flight::route('/', 'bonjour');
```

Ou une méthode de classe :

```php
class Salutation {
    public static function bonjour() {
        echo 'bonjour le monde!';
    }
}

Flight::route('/', array('Salutation','bonjour'));
```

Ou une méthode d'objet :

```php
class Salutation
{
    public function __construct() {
        $this->name = 'Jean Dupont';
    }

    public function bonjour() {
        echo "Bonjour, {$this->name}!";
    }
}

$salutation = new Salutation();

Flight::route('/', array($salutation, 'bonjour'));
```

Les routes sont associées dans l'ordre où elles sont définies. La première route à correspondre à une requête sera appelée.

## Routage par méthode

Par défaut, les modèles de route sont associés à toutes les méthodes de requête. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

```php
Flight::route('GET /', function () {
  echo 'J'ai reçu une requête GET.';
});

Flight::route('POST /', function () {
  echo 'J'ai reçu une requête POST.';
});
```

Vous pouvez également mapper plusieurs méthodes à un seul rappel en utilisant un délimiteur `|` :

```php
Flight::route('GET|POST /', function () {
  echo 'J'ai reçu soit une requête GET ou une requête POST.';
});
```

## Expressions régulières

Vous pouvez utiliser des expressions régulières dans vos routes :

```php
Flight::route('/utilisateur/[0-9]+', function () {
  // Cela correspondra à /utilisateur/1234
});
```

## Paramètres nommés

Vous pouvez spécifier des paramètres nommés dans vos routes qui seront transmis
à votre fonction de rappel.

```php
Flight::route('/@nom/@id', function (string $nom, string $id) {
  echo "bonjour, $nom ($id)!";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant le délimiteur `:` :

```php
Flight::route('/@nom/@id:[0-9]{3}', function (string $nom, string $id) {
  // Cela correspondra à /bob/123
  // Mais ne correspondra pas à /bob/12345
});
```

La correspondance des groupes regex `()` avec des paramètres nommés n'est pas prise en charge.

## Paramètres optionnels

Vous pouvez spécifier des paramètres nommés qui sont optionnels pour la correspondance en enveloppant
les segments entre parenthèses.

```php
Flight::route(
  '/blog(/@annee(/@mois(/@jour)))',
  function(?string $annee, ?string $mois, ?string $jour) {
    // Cela correspondra aux URL suivantes :
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Tous les paramètres optionnels non correspondants seront transmis en tant que NULL.

## Jokers

La correspondance est effectuée uniquement sur des segments d'URL individuels. Si vous souhaitez faire correspondre plusieurs
segments, vous pouvez utiliser le joker `*`.

```php
Flight::route('/blog/*', function () {
  // Cela correspondra à /blog/2000/02/01
});
```

Pour router toutes les requêtes vers un seul rappel, vous pouvez faire :

```php
Flight::route('*', function () {
  // Faire quelque chose
});
```

## Passage

Vous pouvez passer l'exécution à la route correspondante suivante en retournant `true` à partir
de votre fonction de rappel.

```php
Flight::route('/utilisateur/@nom', function (string $nom) {
  // Vérifier une condition
  if ($nom !== "Bob") {
    // Continuer vers la route suivante
    return true;
  }
});

Flight::route('/utilisateur/*', function () {
  // Cela sera appelé
});
```

## Informations sur la route

Si vous souhaitez inspecter les informations sur la route correspondante, vous pouvez demander à ce que la route
l'objet soit transmis à votre fonction de rappel en passant `true` en tant que troisième paramètre dans
la méthode de route. L'objet de route sera toujours le dernier paramètre transmis à votre
fonction de rappel.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP correspondantes
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière correspondante
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le modèle d'URL
  $route->splat;
}, true);
```

## Regroupement des routes

Il peut arriver que vous souhaitiez regrouper des routes associées ensemble (comme `/api/v1`).
Vous pouvez le faire en utilisant la méthode `group` :

```php
Flight::group('/api/v1', function () {
  Flight::route('/utilisateurs', function () {
	// Correspond à /api/v1/utilisateurs
  });

  Flight::route('/articles', function () {
	// Correspond à /api/v1/articles
  });
});
```

Vous pouvez même imbriquer des groupes de groupes :

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtient des variables, il ne définit pas de route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v1/utilisateurs
	});

	Flight::post('/articles', function () {
	  // Correspond à POST /api/v1/articles
	});

	Flight::put('/articles/1', function () {
	  // Correspond à PUT /api/v1/articles
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtient des variables, il ne définit pas de route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v2/utilisateurs
	});
  });
});
```

### Regroupement avec le contexte de l'objet

Vous pouvez toujours utiliser le regroupement des routes avec l'objet `Engine` de la manière suivante :

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/utilisateurs', function () {
	// Correspond à GET /api/v1/utilisateurs
  });

  $router->post('/articles', function () {
	// Correspond à POST /api/v1/articles
  });
});
```

## Aliasing des routes

Vous pouvez assigner un alias à une route, de sorte que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un modèle par exemple).

```php
Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');

// plus tard dans le code quelque part
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // retournera '/utilisateurs/5'
```

Ceci est particulièrement utile si votre URL venait à changer. Dans l'exemple ci-dessus, disons que les utilisateurs ont été déplacés vers `/admin/utilisateurs/@id` à la place.
Avec l'aliasing en place, vous n'avez pas à modifier partout où vous référencez l'alias car l'alias retournera maintenant `/admin/utilisateurs/5` comme dans l'exemple ci-dessus.

L'aliasing des routes fonctionne également dans les groupes :

```php
Flight::group('/utilisateurs', function() {
    Flight::route('/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');
});


// plus tard dans le code quelque part
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // retournera '/utilisateurs/5'
```

## Middleware des routes

Flight prend en charge le middleware des routes et du groupement des routes. Le middleware est une fonction qui est exécutée avant (ou après) le rappel de la route. C'est une excellente façon d'ajouter des vérifications d'authentification API dans votre code, ou de valider que l'utilisateur a l'autorisation d'accéder à la route.

Voici un exemple de base :

```php
// Si vous ne fournissez qu'une fonction anonyme, elle sera exécutée avant le rappel de la route. 
// il n'y a pas de fonctions de middleware "after" sauf pour les classes (voir ci-dessous)
Flight::route('/chemin', function() { echo 'Me voici!'; })->addMiddleware(function() {
	echo 'Middleware en premier!';
});

Flight::start();

// Cela affichera "Middleware en premier! Me voici!"
```

Il y a quelques notes très importantes sur le middleware que vous devez connaître avant de les utiliser :
- Les fonctions de middleware sont exécutées dans l'ordre où elles sont ajoutées à la route. L'exécution est similaire à celle de [Slim Framework](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Les "avant" sont exécutés dans l'ordre ajouté, et les "après" sont exécutés dans l'ordre inverse.
- Si votre fonction de middleware renvoie false, toute l'exécution est arrêtée et une erreur 403 Forbidden est déclenchée. Vous voudrez probablement gérer cela de manière plus gracieuse avec une `Flight::redirect()` ou quelque chose de similaire.
- Si vous avez besoin de paramètres de votre route, ils seront transmis dans un seul tableau à votre fonction de middleware. (`function($params) { ... }` ou `public function before($params) {}`). La raison en est que vous pouvez structurer vos paramètres en groupes et que dans certains de ces groupes, vos paramètres peuvent apparaître dans un ordre différent, ce qui pourrait casser la fonction de middleware en se référant au mauvais paramètre. De cette façon, vous pouvez y accéder par nom au lieu de par position.

### Classes de middleware

Le middleware peut également être enregistré en tant que classe. Si vous avez besoin de la fonctionnalité "après", vous devez utiliser une classe.

```php
class MonMiddleware {
	public function before($params) {
		echo 'Middleware en premier!';
	}

	public function after($params) {
		echo 'Middleware en dernier!';
	}
}

$MonMiddleware = new MonMiddleware();
Flight::route('/chemin', function() { echo ' Me voici! '; })->addMiddleware($MonMiddleware); // aussi ->addMiddleware([ $MonMiddleware, $MonMiddleware2 ]);

Flight::start();

// Cela affichera "Middleware en premier! Me voici! Middleware en dernier!"
```

### Groupes de middleware

Vous pouvez ajouter un groupe de route, et ensuite chaque route dans ce groupe aura le même middleware également. C'est utile si vous avez besoin de regrouper un ensemble de routes par exemple avec un middleware Auth pour vérifier la clé API dans l'en-tête.

```php

// ajouté à la fin de la méthode de groupe
Flight::group('/api', function() {
    Flight::route('/utilisateurs', function() { echo 'utilisateurs'; }, false, 'utilisateurs');
	Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');
}, [ new ApiAuthMiddleware() ]);
```