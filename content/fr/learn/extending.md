# Extension

Flight est conçu pour être un framework extensible. Le framework est livré avec un ensemble de méthodes et de composants par défaut, mais vous permet de mapper vos propres méthodes, d'enregistrer vos propres classes, voire de remplacer des classes et des méthodes existantes.

Si vous recherchez un DIC (Conteneur d'injection de dépendances), passez à la [page du Conteneur d'injection de dépendances](dependency-injection-container) .

## Mappage des méthodes

Pour mapper votre propre méthode personnalisée, utilisez la fonction `map` :

```php
// Mapper votre méthode
Flight::map('hello', function (string $name) {
  echo "bonjour $name!";
});

// Appeler votre méthode personnalisée
Flight::hello('Bob');
');
```

Bien qu'il soit possible de créer des méthodes personnalisées simples, il est recommandé de simplement créer
des fonctions standard en PHP. Cela a la saisie semi-automatique dans les IDE et est plus facile à lire.
L'équivalent du code ci-dessus serait :

```php
function hello(string $name) {
  echo "bonjour $name!";
}

hello('Bob');
```

Cela est plus couramment utilisé lorsque vous devez transmettre des variables à votre méthode pour obtenir une valeur attendue. Utiliser la méthode `register()` comme ci-dessous est plus destiné à transmettre une configuration
et ensuite appeler votre classe préconfigurée.

## Enregistrement des classes

Pour enregistrer votre propre classe et la configurer, utilisez la fonction `register` :

```php
// Enregistrer votre classe
Flight::register('user', User::class);

// Obtenir une instance de votre classe
$user = Flight::user();
```

La méthode d'enregistrement vous permet également de transmettre des paramètres au constructeur de votre classe.
Ainsi, lorsque vous chargez votre classe personnalisée, elle sera pré-initialisée.
Vous pouvez définir les paramètres du constructeur en passant un tableau supplémentaire.
Voici un exemple de chargement d'une connexion de base de données :

```php
// Enregistrer la classe avec des paramètres du constructeur
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtenir une instance de votre classe
// Cela va créer un objet avec les paramètres définis
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// et si vous en avez besoin plus tard dans votre code, vous appelez simplement la même méthode à nouveau
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si vous passez en paramètre un callback supplémentaire, il sera exécuté immédiatement
après la construction de la classe. Cela vous permet d'effectuer toute procédure de configuration pour votre
nouvel objet. La fonction de rappel prend un paramètre, une instance du nouvel objet.

```php
// Le callback recevra l'objet qui a été construit
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Par défaut, chaque fois que vous chargez votre classe, vous obtiendrez une instance partagée.
Pour obtenir une nouvelle instance d'une classe, passez simplement `false` en tant que paramètre :

```php
// Instance partagée de la classe
$shared = Flight::db();

// Nouvelle instance de la classe
$new = Flight::db(false);
```

Gardez à l'esprit que les méthodes mappées ont la priorité sur les classes enregistrées. Si vous
déclarez les deux en utilisant le même nom, seule la méthode mappée sera invoquée.

## Remplacement des méthodes du framework

Flight vous permet de remplacer sa fonctionnalité par défaut pour répondre à vos propres besoins,
sans avoir à modifier le code. Vous pouvez afficher toutes les méthodes que vous pouvez remplacer [ici](/learn/api).

Par exemple, lorsque Flight ne peut pas faire correspondre une URL à une route, il invoque la méthode `notFound`
qui envoie une réponse générique `HTTP 404`. Vous pouvez remplacer ce comportement
en utilisant la méthode `map` :

```php
Flight::map('notFound', function() {
  // Afficher une page d'erreur personnalisée 404
  include 'errors/404.html';
});
```

Flight vous permet également de remplacer les composants principaux du framework.
Par exemple, vous pouvez remplacer la classe Routeur par défaut par votre propre classe personnalisée :

```php
// Enregistrer votre classe personnalisée
Flight::register('router', MyRouter::class);

// Lorsque Flight charge l'instance du Routeur, il chargera votre classe
$myrouter = Flight::router();
```

Cependant les méthodes du framework comme `map` et `register` ne peuvent pas être remplacées. Vous obtiendrez
une erreur si vous essayez de le faire.