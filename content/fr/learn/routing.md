# Itinéraire

> **Remarque :** Vous voulez en savoir plus sur l'itinéraire ? Consultez la page [pourquoi les frameworks](/learn/why-frameworks) pour une explication plus détaillée.

Le routage de base dans Flight est réalisé en associant un motif d'URL à une fonction de rappel ou à un tableau d'une classe et d'une méthode.

```php
Flight::route('/', function(){
    echo 'Bonjour le monde!';
});
```

Le rappel peut être n'importe quel objet qui est appelable. Ainsi, vous pouvez utiliser une fonction régulière :

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

Ou une méthode objet :

```php

// Greeting.php
class Salutation
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function bonjour() {
        echo "Bonjour, {$this->name}!";
    }
}

// index.php
$salutation = new Salutation();

Flight::route('/', array($salutation, 'bonjour'));
```

Les itinéraires sont associés dans l'ordre où ils sont définis. Le premier itinéraire qui correspond à une demande sera invoqué.

## Routage Méthode

Par défaut, les motifs d'itinéraire sont associés à toutes les méthodes de demande. Vous pouvez répondre à des méthodes spécifiques en plaçant un identifiant avant l'URL.

```php
Flight::route('GET /', function () {
  echo 'J'ai reçu une demande GET.';
});

Flight::route('POST /', function () {
  echo 'J'ai reçu une demande POST.';
});
```

Vous pouvez également mapper plusieurs méthodes vers un seul rappel en utilisant un délimiteur `|` :

```php
Flight::route('GET|POST /', function () {
  echo 'J'ai reçu une demande GET ou POST.';
});
```

En outre, vous pouvez obtenir l'objet Routage qui dispose de certaines méthodes d'aide que vous pouvez utiliser :

```php

$router = Flight::router();

// mappe toutes les méthodes
$router->map('/', function() {
	echo 'Bonjour le monde!';
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

Vous pouvez utiliser des expressions régulières dans vos itinéraires :

```php
Flight::route('/utilisateur/[0-9]+', function () {
  // Cela correspondra à /utilisateur/1234
});
```

Bien que cette méthode soit disponible, il est recommandé d'utiliser des paramètres nommés, ou
des paramètres nommés avec des expressions régulières, car ils sont plus lisibles et plus faciles à entretenir.

## Paramètres Nommés

Vous pouvez spécifier des paramètres nommés dans vos itinéraires qui seront transmis à
votre fonction de rappel.

```php
Flight::route('/@nom/@id', function (string $nom, string $id) {
  echo "bonjour, $nom ($id)!";
});
```

Vous pouvez également inclure des expressions régulières avec vos paramètres nommés en utilisant
le délimiteur `:` :

```php
Flight::route('/@nom/@id:[0-9]{3}', function (string $nom, string $id) {
  // Cela correspondra à /bob/123
  // Mais ne correspondra pas à /bob/12345
});
```

> **Remarque :** L'association des groupes de regex `()` avec des paramètres nommés n'est pas prise en charge. :’\(

## Paramètres Optionnels

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

Tous les paramètres optionnels qui ne sont pas associés seront transmis en tant que `NULL`.

## Jokers

La correspondance n'est effectuée que sur des segments d'URL individuels. Si vous voulez faire correspondre plusieurs
segments, vous pouvez utiliser le joker `*`.

```php
Flight::route('/blog/*', function () {
  // Cela correspondra à /blog/2000/02/01
});
```

Pour associer toutes les demandes à un seul rappel, vous pouvez faire :

```php
Flight::route('*', function () {
  // Faire quelque chose
});
```

## Passage

Vous pouvez transmettre l'exécution au prochain itinéraire correspondant en retournant `true` de
votre fonction de rappel.

```php
Flight::route('/utilisateur/@nom', function (string $nom) {
  // Vérifier certaines conditions
  if ($nom !== "Bob") {
    // Continuer vers l'itinéraire suivant
    return true;
  }
});

Flight::route('/utilisateur/*', function () {
  // Cela sera appelé
});
```

## Aliasing d'Itinéraire

Vous pouvez attribuer un alias à un itinéraire, afin que l'URL puisse être générée dynamiquement plus tard dans votre code (comme un template par exemple).

```php
Flight::route('/utilisateurs/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');

// plus tard dans le code quelque part
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // renverra '/utilisateurs/5'
```

Cela est particulièrement utile si votre URL venait à changer. Dans l'exemple ci-dessus, supposons que les utilisateurs ont été déplacés vers `/admin/users/@id` à la place.
Avec l'aliasing en place, vous n'avez pas à changer partout où vous référencez l'alias car l'alias retournera maintenant `/admin/users/5` comme dans l'exemple ci-dessus.

L'aliasing d'itinéraire fonctionne également dans les groupes :

```php
Flight::group('/utilisateurs', function() {
    Flight::route('/@id', function($id) { echo 'utilisateur:'.$id; }, false, 'vue_utilisateur');
});


// plus tard dans le code quelque part
Flight::getUrl('vue_utilisateur', [ 'id' => 5 ]); // renverra '/utilisateurs/5'
```

## Info Itinéraire

Si vous souhaitez inspecter les informations d'itinéraire correspondantes, vous pouvez demander que l'objet d'itinéraire soit passé à votre rappel en passant `true` en tant que troisième paramètre dans
la méthode d'itinéraire. L'objet d'itinéraire sera toujours le dernier paramètre passé à votre
fonction de rappel.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Tableau des méthodes HTTP mises en correspondance
  $route->methods;

  // Tableau des paramètres nommés
  $route->params;

  // Expression régulière correspondante
  $route->regex;

  // Contient le contenu de tout '*' utilisé dans le motif d'URL
  $route->splat;

  // Affiche le chemin de l'URL.... si vous en avez vraiment besoin
  $route->motif;

  // Montre quel middleware est attribué à ceci
  $route->middleware;

  // Montre l'alias attribué à cet itinéraire
  $route->alias;
}, true);
```

## Regroupement d'Itinéraires

Il y a des moments où vous voulez regrouper des itinéraires apparentés ensemble (comme `/api/v1`).
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
	// Flight::get() obtient des variables, il ne définit pas un itinéraire ! Voir le contexte de l'objet ci-dessous
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

	// Flight::get() obtient des variables, il ne définit pas un itinéraire ! Voir le contexte de l'objet ci-dessous
	Flight::route('GET /utilisateurs', function () {
	  // Correspond à GET /api/v2/utilisateurs
	});
  });
});
```

### Regroupement avec Contexte d'Objet

Vous pouvez toujours utiliser le regroupement d'itinéraire avec l'objet `Engine` de la manière suivante :

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // utiliser la variable $router
  $router->get('/utilisateurs', function () {
	// Correspond à GET /api/v1/utilisateurs
  });

  $router->post('/articles', function () {
	// Correspond à POST /api/v1/articles
  });
});
```