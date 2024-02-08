# Extension / Conteneurs

Flight est conçu pour être un framework extensible. Le framework est livré avec un ensemble
de méthodes et composants par défaut, mais il vous permet de mapper vos propres méthodes,
enregistrer vos propres classes, ou même substituer des classes et méthodes existantes.

## Mapper des méthodes

Pour mapper votre propre méthode personnalisée simple, vous utilisez la fonction `map`:

```php
// Mapper votre méthode
Flight::map('hello', function (string $name) {
  echo "bonjour $name!";
});

// Appeler votre méthode personnalisée
Flight::hello('Bob');
```

Cela est davantage utilisé lorsque vous devez transmettre des variables à votre méthode pour obtenir une valeur attendue. Utiliser la méthode `register()` comme ci-dessous est davantage pour transmettre une configuration puis appeler votre classe préconfigurée.

## Enregistrement de Classes / Conteneurisation

Pour enregistrer votre propre classe et la configurer, vous utilisez la fonction `register`:

```php
// Enregistrer votre classe
Flight::register('utilisateur', Utilisateur::class);

// Obtenir une instance de votre classe
$user = Flight::user();
```

La méthode d'enregistrement permet également de transmettre des paramètres au constructeur de votre classe. Ainsi, lorsque vous chargez votre classe personnalisée, elle sera pré-initialisée. Vous pouvez définir les paramètres du constructeur en passant un tableau supplémentaire. Voici un exemple de chargement d'une connexion de base de données:

```php
// Enregistrer une classe avec des paramètres de constructeur
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'utilisateur', 'mot de passe']);

// Obtenir une instance de votre classe
// Cela créera un objet avec les paramètres définis
//
// new PDO('mysql:host=localhost;dbname=test','utilisateur','mot de passe');
//
$db = Flight::db();

// et si vous en aviez besoin plus tard dans votre code, vous appelez simplement à nouveau la même méthode
class CertainsContrôleurs {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si vous passez un paramètre de rappel supplémentaire, il sera exécuté immédiatement après la construction de la classe. Cela vous permet d'effectuer toute procédure de configuration pour votre nouvel objet. La fonction de rappel prend un paramètre, une instance du nouvel objet.

```php
// L'objet construit sera transmis au rappel
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'utilisateur', 'mot de passe'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Par défaut, chaque fois que vous chargez votre classe, vous obtiendrez une instance partagée.
Pour obtenir une nouvelle instance d'une classe, il suffit de passer `false` en paramètre:

```php
// Instance partagée de la classe
$partagé = Flight::db();

// Nouvelle instance de la classe
$nouveau = Flight::db(false);
```

Gardez à l'esprit que les méthodes mappées priment sur les classes enregistrées. Si vous
déclarez les deux en utilisant le même nom, seule la méthode mappée sera invoquée.

## Substitution

Flight vous permet de substituer sa fonctionnalité par défaut pour répondre à vos besoins,
sans avoir à modifier le code.

Par exemple, lorsque Flight ne peut pas faire correspondre une URL à une route, elle appelle la méthode `notFound`
qui envoie une réponse générique `HTTP 404`. Vous pouvez remplacer ce comportement
en utilisant la méthode `map`:

```php
Flight::map('notFound', function() {
  // Afficher une page d'erreur personnalisée 404
  include 'erreurs/404.html';
});
```

Flight vous permet également de remplacer des composants centraux du framework.
Par exemple, vous pouvez remplacer la classe Router par défaut par votre propre classe personnalisée:

```php
// Enregistrer votre classe personnalisée
Flight::register('routeur', MonRouteur::class);

// Lorsque Flight charge l'instance de Router, il chargera votre classe
$monrouteur = Flight::router();
```

Cependant, les méthodes framework comme `map` et `register` ne peuvent pas être substituées. Vous obtiendrez une erreur si vous essayez de le faire.