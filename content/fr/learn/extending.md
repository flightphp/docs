# Étendre

Flight est conçu pour être un framework extensible. Le framework est livré avec un ensemble
de méthodes et de composants par défaut, mais il vous permet de mapper vos propres méthodes,
enregistrer vos propres classes ou même remplacer des classes et des méthodes existantes.

Si vous recherchez un conteneur d'injection de dépendances (Dependency Injection Container), rendez-vous sur la
page du [Conteneur d'injection de dépendances](dependency-injection-container).

## Mapper des méthodes

Pour mapper votre propre méthode personnalisée, vous utilisez la fonction `map` :

```php
// Mapper votre méthode
Flight::map('bonjour', function (string $nom) {
  echo "bonjour $nom !";
});

// Appeler votre méthode personnalisée
Flight::bonjour('Bob');
```

Cela est plus utilisé lorsque vous devez passer des variables dans votre méthode pour obtenir une valeur attendue. Utiliser la méthode `register()` comme ci-dessous est davantage pour transmettre une configuration
et ensuite appeler votre classe préconfigurée.

## Enregistrer des classes

Pour enregistrer votre propre classe et la configurer, vous utilisez la fonction `register` :

```php
// Enregistrer votre classe
Flight::register('utilisateur', Utilisateur::class);

// Obtenir une instance de votre classe
$user = Flight::utilisateur();
```

La méthode d'enregistrement vous permet également de passer des paramètres au constructeur de votre classe. Ainsi, lorsque vous chargez votre classe personnalisée, elle sera préinitialisée.
Vous pouvez définir les paramètres du constructeur en passant un tableau supplémentaire.
Voici un exemple de chargement d'une connexion de base de données :

```php
// Enregistrer la classe avec des paramètres de constructeur
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtenir une instance de votre classe
// Cela va créer un objet avec les paramètres définis
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// et si vous en avez besoin plus tard dans votre code, il vous suffit d'appeler à nouveau la même méthode
class UnController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si vous passez un paramètre de rappel supplémentaire, il sera exécuté immédiatement
après la construction de la classe. Cela vous permet d'effectuer toute procédure de configuration pour votre
nouvel objet. La fonction de rappel prend un paramètre, une instance du nouvel objet.

```php
// Le rappel recevra l'objet qui a été construit
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
Pour obtenir une nouvelle instance d'une classe, il vous suffit de passer `false` comme paramètre :

```php
// Instance partagée de la classe
$partagé = Flight::db();

// Nouvelle instance de la classe
$nouveau = Flight::db(false);
```

Gardez à l'esprit que les méthodes mappées ont la priorité sur les classes enregistrées. Si vous
déclarez les deux en utilisant le même nom, seule la méthode mappée sera invoquée.

## Remplacer les méthodes du framework

Flight vous permet de remplacer sa fonctionnalité par défaut pour répondre à vos besoins,
sans avoir à modifier de code.

Par exemple, lorsque Flight ne peut pas faire correspondre une URL à une route, il invoque la méthode `notFound`
qui envoie une réponse `HTTP 404` générique. Vous pouvez remplacer ce comportement
en utilisant la méthode `map` :

```php
Flight::map('notFound', function() {
  // Afficher la page d'erreur 404 personnalisée
  include 'erreurs/404.html';
});
```

Flight vous permet également de remplacer des composants principaux du framework.
Par exemple, vous pouvez remplacer la classe Router par défaut par votre propre classe personnalisée :

```php
// Enregistrer votre classe personnalisée
Flight::register('router', MaClasseRouter::class);

// Lorsque Flight charge l'instance du routeur, il chargera votre classe
$monrouteur = Flight::router();
```

Cependant, les méthodes du framework comme `map` et `register` ne peuvent pas être remplacées. Vous obtiendrez
une erreur si vous essayez de le faire.