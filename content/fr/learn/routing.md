# Routage

> **Remarque :** Vous voulez en savoir plus sur le routage ? Consultez la [page sur les frameworks](/learn/why-frameworks) pour une explication plus approfondie.

Le routage de base dans Flight est réalisé en faisant correspondre un modèle d'URL à une fonction de rappel ou à un tableau d'une classe et d'une méthode.

```php
Flight::route('/', function(){
    echo 'bonjour le monde!';
});
```

Le rappel peut être n'importe quel objet pouvant être appelé. Ainsi, vous pouvez utiliser une fonction régulière :

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
// Greeting.php
class Salutation
{
    public function __construct() {
        $this->name = 'Jean Dupont';
    }

    public function bonjour() {
        echo "Bonjour, {$this->name}!";
    }
}

// index.php
$salutation = new Salutation();

Flight::route('/', array($salutation, 'bonjour'));
```

Les routes sont appariées dans l'ordre où elles sont définies. La première route à correspondre à une requête sera invoquée.

## Routage par Méthode

Par défaut, les modèles de route sont appariés contre toutes les méthodes de requête. Vous pouvez répondre
à des méthodes spécifiques en plaçant un identifiant avant l'URL.

```php
Flight::route('GET /', function () {
  echo 'J'ai reçu une demande GET.';
});

Flight::route('POST /', function () {
  echo 'J'ai reçu une demande POST.';
});
```

Vous pouvez également mapper plusieurs méthodes à un seul rappel en utilisant un délimiteur `|` :

```php
Flight::route('GET|POST /', function () {
  echo 'J'ai reçu une demande GET ou POST.';
});
```

De plus, vous pouvez récupérer l'objet Router qui possède quelques méthodes d'aide pour vous :

```php

$router = Flight::router();

// mappe toutes les méthodes
$router->map('/', function() {
	echo 'bonjour le monde!';
});

// demande GET
$router->get('/utilisateurs', function() {
	echo 'utilisateurs';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Expressions Régulières

Vous pouvez utiliser des expressions régulières dans vos routes :

```php
Flight::route('/utilisateur/[0-9]+', function () {
  // Cela correspondra à /utilisateur/1234
});
```

Bien que cette méthode soit disponible, il est recommandé d'utiliser des paramètres nommés, ou
des paramètres nommés avec des expressions régulières, car ils sont plus lisibles et plus faciles à maintenir.

## Paramètres Nommés

Vous pouvez spécifier des paramètres nommés dans vos routes qui seront transmis à
your fonction de rappel.

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "bonjour, $name ($id)!";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant
le délimiteur `:` :

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Cela correspondra à /bob/123
  // Mais ne correspondra pas à /bob/12345
});
```

> **Remarque :** L'association de groupes d'expressions régulières `()` avec des paramètres nommés n'est pas supportée. :'\(

## Paramètres Optionnels

Vous pouvez spécifier des paramètres nommés qui sont optionnels pour correspondre en enveloppant
les segments entre parenthèses.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Cela correspondra aux URL suivantes :
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Tous les paramètres optionnels qui ne correspondent pas seront transmis en tant que `NULL`.

## Jokers

La correspondance est uniquement effectuée sur des segments d'URL individuels. Si vous voulez faire correspondre plusieurs
segments, vous pouvez utiliser le joker `*`.

```php
Flight::route('/blog/*', function () {
  // Cela correspondra à /blog/2000/02/01
});
```

Pour acheminer toutes les demandes vers un seul rappel, vous pouvez faire :

```php
Flight::route('*', function () {
  // Faire quelque chose
});
```

## Passage

Vous pouvez passer l'exécution à la route correspondante suivante en retournant `true` à partir de
votre fonction de rappel.

```php
Flight::route('/utilisateur/@name', function (string $name) {
  // Vérifier une condition
  if ($name !== "Bob") {
    // Continuer vers la prochaine route
    return true;
  }
});

Flight::route('/utilisateur/*', function () {
  // Cela sera appelé
});
```

## Alias de Route

Vous pouvez attribuer un alias à une route, de sorte que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un modèle par exemple).

```php
Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'affichage_utilisateur');

// plus tard dans le code quelque part
Flight::getUrl('affichage_utilisateur', [ 'id' => 5 ]); // retournera '/utilisateurs/5'
```

Ceci est particulièrement utile si votre URL doit changer. Dans l'exemple ci-dessus, disons que les utilisateurs ont été déplacés vers `/admin/utilisateurs/@id` à la place.
Avec l'alias en place, vous n'avez pas à changer l'endroit où vous référencez l'alias car l'alias renverra maintenant `/admin/utilisateurs/5` comme dans l'exemple ci-dessus.

L'alias de route fonctionne également dans les groupes :

```php
Flight::group('/utilisateurs', function() {
    Flight::route('/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'affichage_utilisateur');
});


// plus tard dans le code quelque part
Flight::getUrl('affichage_utilisateur', [ 'id' => 5 ]); // retournera '/utilisateurs/5'
```

## Informations sur la Route

Si vous souhaitez inspecter les informations de correspondance de la route, vous pouvez demander à ce que l'objet de la route
soit transmis à votre rappel en passant `true` en tant que troisième paramètre dans
la méthode de la route. L'objet de la route sera toujours le dernier paramètre transmis à votre
fonction de rappel.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP correspondantes
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière de correspondance
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le modèle d'URL
  $route->splat;

  // Affiche le chemin URL...si vraiment vous en avez besoin
  $route->pattern;

  // Affiche quel middleware est assigné à cela
  $route->middleware;

  // Affiche l'alias assigné à cette route
  $route->alias;
}, true);
```

## Regroupement de Routes

Il peut arriver que vous souhaitiez regrouper des routes liées ensemble (comme `/api/v1`). Vous pouvez le faire en utilisant la méthode `group` :

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
	// Flight::get() récupère des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
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

	// Flight::get() récupère des variables, il ne définit pas une route ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v2/utilisateurs
	});
  });
});
```

### Regroupement avec Contexte d'Objet

Vous pouvez toujours utiliser le regroupement de routes avec l'objet `Engine` de la manière suivante :

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // utilisez la variable $router
  $router->get('/utilisateurs', function () {
	// Correspond à GET /api/v1/utilisateurs
  });

  $router->post('/articles', function () {
	// Correspond à POST /api/v1/articles
  });
});
```  