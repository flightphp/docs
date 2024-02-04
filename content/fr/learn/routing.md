# Routage

Le routage dans Flight est réalisé en faisant correspondre un schéma d'URL à une fonction de rappel.

```php
Flight::route('/', function(){
    echo 'Bonjour le monde!';
});
```

La fonction de rappel peut être n'importe quel objet qui est appelable. Vous pouvez donc utiliser une fonction régulière :

```php
function bonjour(){
    echo 'Bonjour le monde!';
}

Flight::route('/', 'bonjour');
```

Ou une méthode de classe :

```php
class Salutation {
    public static function bonjour() {
        echo 'Bonjour le monde!';
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
        echo "Bonjour, {$this->name} !";
    }
}

$salutation = new Salutation();

Flight::route('/', array($salutation, 'bonjour'));
```

Les routes sont associées dans l'ordre où elles sont définies. La première route à satisfaire une requête sera invoquée.

## Routage par Méthode

Par défaut, les schémas de routage sont associés à toutes les méthodes de requête. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

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
  echo 'J'ai reçu une requête GET ou POST.';
});
```

## Expressions Régulières

Vous pouvez utiliser des expressions régulières dans vos routes :

```php
Flight::route('/utilisateur/[0-9]+', function () {
  // Cela correspondra à /utilisateur/1234
});
```

## Paramètres Nommmés

Vous pouvez spécifier des paramètres nommés dans vos routes qui seront transmis à votre fonction de rappel.

```php
Flight::route('/@nom/@id', function (string $nom, string $id) {
  echo "bonjour, $nom ($id) !";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant le délimiteur `:` :

```php
Flight::route('/@nom/@id:[0-9]{3}', function (string $nom, string $id) {
  // Cela correspondra à /bob/123
  // Mais ne correspondra pas à /bob/12345
});
```

La correspondance de groupes regex `()` avec des paramètres nommés n'est pas prise en charge.

## Paramètres Optionnels

Vous pouvez spécifier des paramètres nommés qui sont optionnels pour la correspondance en enveloppant des segments entre parenthèses.

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

Tous les paramètres optionnels qui ne sont pas associés seront transmis en tant que NULL.

## Jokers

La correspondance se fait uniquement sur des segments d'URL individuels. Si vous souhaitez faire correspondre plusieurs segments, vous pouvez utiliser le joker `*`.

```php
Flight::route('/blog/*', function () {
  // Cela correspondra à /blog/2000/02/01
});
```

Pour faire correspondre toutes les demandes à un seul rappel, vous pouvez faire :

```php
Flight::route('*', function () {
  // Faire quelque chose
});
```

## Passage

Vous pouvez passer l'exécution à la route correspondante suivante en renvoyant `true` depuis votre fonction de rappel.

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

## Infos sur la Route

Si vous voulez inspecter les informations de route correspondantes, vous pouvez demander que l'objet de route soit transmis à votre fonction de rappel en passant `true` en tant que troisième paramètre dans la méthode de route. L'objet de route sera toujours le dernier paramètre transmis à votre fonction de rappel.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP associées
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière correspondante
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le schéma d'URL
  $route->splat;
}, true);
```

## Regroupement de Routes

Il peut arriver que vous vouliez regrouper des routes associées ensemble (comme `/api/v1`). Vous pouvez le faire en utilisant la méthode `group` :

```php
Flight::group('/api/v1', function () {
  Flight::route('/utilisateurs', function () {
	// Correspond à /api/v1/utilisateurs
  });

  Flight::route('/publications', function () {
	// Correspond à /api/v1/publications
  });
});
```

Vous pouvez même imbriquer des groupes de groupes :

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtient des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v1/utilisateurs
	});

	Flight::post('/publications', function () {
	  // Correspond à POST /api/v1/publications
	});

	Flight::put('/publications/1', function () {
	  // Correspond à PUT /api/v1/publications
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtient des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v2/utilisateurs
	});
  });
});
```

### Regroupement avec le Contexte d'Objet

Vous pouvez toujours utiliser le regroupement de routes avec l'objet `Engine` de la manière suivante :

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/utilisateurs', function () {
	// Correspond à GET /api/v1/utilisateurs
  });

  $router->post('/publications', function () {
	// Correspond à POST /api/v1/publications
  });
});
```

## Alias de Route

Vous pouvez attribuer un alias à une route, afin que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un modèle par exemple).

```php
Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');

// plus tard dans le code quelque part
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // retournera '/utilisateurs/5'
```

Cela est particulièrement utile si votre URL devait changer. Dans l'exemple ci-dessus, disons que les utilisateurs ont été déplacés vers `/admin/utilisateurs/@id` à la place.
Avec l'alias en place, vous n'avez pas à changer les références à l'alias car celui-ci retournera maintenant `/admin/utilisateurs/5` comme dans l'exemple ci-dessus.

L'alias de route fonctionne également dans les groupes :

```php
Flight::group('/utilisateurs', function() {
    Flight::route('/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');
});


// plus tard dans le code quelque part
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // retournera '/utilisateurs/5'
```

## Middleware de Routage
Flight prend en charge les middleware de route et de groupe de route. Un middleware est une fonction qui est exécutée avant (ou après) le rappel de la route. C'est une excellente façon d'ajouter des vérifications d'authentification d'API dans votre code, ou de valider que l'utilisateur a la permission d'accéder à la route.

Voici un exemple de base :

```php
// Si vous ne fournissez qu'une fonction anonyme, elle sera exécutée avant le rappel de la route. 
// il n'y a pas de fonctions middleware "après" sauf pour les classes (voir ci-dessous)
Flight::route('/chemin', function() { echo 'Me voilà !'; })->addMiddleware(function() {
	echo 'Middleware d'abord !';
});

Flight::start();

// Cela affichera "Middleware d'abord ! Me voilà !"
```

Il y a quelques notes très importantes sur les middleware que vous devriez connaître avant de les utiliser :
- Les fonctions middleware sont exécutées dans l'ordre où elles sont ajoutées à la route. L'exécution est similaire à celle de la façon dont [Slim Framework gère ceci](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Les Avant sont exécutés dans l'ordre ajouté, et les Après sont exécutés dans l'ordre inverse.
- Si votre fonction middleware retourne false, toute l'exécution s'arrête et une erreur 403 Forbidden est déclenchée. Vous voudrez probablement gérer cela de manière plus gracieuse avec un `Flight::rediriger()` ou quelque chose de similaire.
- Si vous avez besoin de paramètres de votre route, ils seront transmis dans un seul tableau à votre fonction middleware. (`function($params) { ... }` ou `public function before($params) {}`). La raison en est que vous pouvez structurer vos paramètres en groupes et dans certains de ces groupes, vos paramètres pourraient apparaître dans un ordre différent ce qui casserait la fonction middleware en faisant référence au mauvais paramètre. De cette façon, vous pouvez y accéder par nom au lieu de par position.

### Classes Middleware

Les middleware peuvent également être enregistrés en tant que classe. Si vous avez besoin de la fonctionnalité "après", vous devez utiliser une classe.

```php
class MonMiddleware {
	public function before($params) {
		echo 'Middleware d'abord !';
	}

	public function after($params) {
		echo 'Middleware en dernier !';
	}
}

$MonMiddleware = new MonMiddleware();
Flight::route('/chemin', function() { echo 'Me voilà! '; })->addMiddleware($MonMiddleware); // aussi ->addMiddleware([ $MonMiddleware, $MonMiddleware2 ]);

Flight::start();

// Cela affichera "Middleware d'abord ! Me voilà ! Middleware en dernier !"
```

### Groupes Middleware

Vous pouvez ajouter un groupe de route, et ensuite chaque route de ce groupe aura également le même middleware. C'est utile si vous devez regrouper un tas de routes par exemple en un middleware Auth pour vérifier la clé API dans l'en-tête.

```php

// ajouté à la fin de la méthode group
Flight::group('/api', function() {
    Flight::route('/utilisateurs', function() { echo 'utilisateurs'; }, false, 'utilisateurs');
	Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');
}, [ new MiddlewareApiAuth() ]);