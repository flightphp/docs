# Extension / Conteneurs

Flight est conçu pour être un framework extensible. Le framework est livré avec un ensemble de méthodes et de composants par défaut, mais il vous permet de mapper vos propres méthodes, d'enregistrer vos propres classes, ou même de remplacer les classes et méthodes existantes.

## Mappage de méthodes

Pour mapper votre propre méthode personnalisée simple, vous utilisez la fonction `map` :

```php
// Mapper votre méthode
Flight::map('hello', function (string $name) {
  echo "bonjour $name!";
});

// Appeler votre méthode personnalisée
Flight::hello('Bob');
```

Cela est plus utilisé lorsque vous avez besoin de passer des variables dans votre méthode pour obtenir une valeur attendue. Utiliser la méthode `register()` comme indiqué ci-dessous est plus pour passer une configuration et ensuite appeler votre classe préconfigurée.

## Enregistrement de Classes / Conteneurisation

Pour enregistrer votre propre classe et la configurer, vous utilisez la fonction `register` :

```php
// Enregistrer votre classe
Flight::register('utilisateur', Utilisateur::class);

// Obtenir une instance de votre classe
$user = Flight::utilisateur();
```

La méthode d'enregistrement vous permet également de transmettre des paramètres au constructeur de votre classe. Ainsi, lorsque vous chargez votre classe personnalisée, elle sera pré-initialisée. Vous pouvez définir les paramètres du constructeur en passant un tableau supplémentaire. Voici un exemple de chargement d'une connexion de base de données :

```php
// Enregistrer une classe avec des paramètres de constructeur
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'utilisateur', 'mot de passe']);

// Obtenez une instance de votre classe
// Cela créera un objet avec les paramètres définis
//
// new PDO('mysql:host=localhost;dbname=test','utilisateur','mot de passe');
//
$db = Flight::db();

// et si vous en avez besoin plus tard dans votre code, vous appelez simplement de nouveau la même méthode
class CertainsControlleurs {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si vous passez un paramètre de rappel supplémentaire, il sera exécuté immédiatement après la construction de la classe. Cela vous permet d'effectuer toutes les procédures de configuration pour votre nouvel objet. La fonction de rappel prend un paramètre, une instance du nouvel objet.

```php
// Le rappel recevra l'objet qui a été construit
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'utilisateur', 'mot de passe'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Par défaut, chaque fois que vous chargez votre classe, vous obtiendrez une instance partagée. Pour obtenir une nouvelle instance d'une classe, il suffit de passer `false` comme paramètre :

```php
// Instance partagée de la classe
$partagé = Flight::db();

// Nouvelle instance de la classe
$nouveau = Flight::db(false);
```

Gardez à l'esprit que les méthodes mappées prévalent sur les classes enregistrées. Si vous déclarez les deux avec le même nom, seule la méthode mappée sera invoquée.

## Remplacement

Flight vous permet de remplacer sa fonctionnalité par défaut pour répondre à vos besoins, sans avoir à modifier le code.

Par exemple, lorsque Flight ne peut pas faire correspondre une URL à une route, il invoque la méthode `notFound` qui envoie une réponse `HTTP 404` générique. Vous pouvez remplacer ce comportement en utilisant la méthode `map` :

```php
Flight::map('notFound', function() {
  // Afficher une page 404 personnalisée
  include 'erreurs/404.html';
});
```

Flight vous permet également de remplacer les composants principaux du framework. Par exemple, vous pouvez remplacer la classe Routeur par défaut par votre propre classe personnalisée :

```php
// Enregistrer votre classe personnalisée
Flight::register('routeur', MonRouteur::class);

// Lorsque Flight charge l'instance du Routeur, il chargera votre classe
$monrouteur = Flight::routeur();
```

Cependant, les méthodes du framework telles que `map` et `register` ne peuvent pas être remplacées. Vous obtiendrez une erreur si vous essayez de le faire.