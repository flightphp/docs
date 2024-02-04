# Extension / Conteneurs

Le Flight est conçu pour être un framework extensible. Le framework est livré avec un ensemble
de méthodes et composants par défaut, mais il vous permet de mapper vos propres méthodes,
enregistrer vos propres classes, voire remplacer des classes et méthodes existantes.

## Mappage de méthodes

Pour mapper votre propre méthode personnalisée, vous utilisez la fonction `map`:

```php
// Mapper votre méthode
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Appelez votre méthode personnalisée
Flight::hello('Bob');
```

## Enregistrement de Classes / Conteneurisation

Pour enregistrer votre propre classe, vous utilisez la fonction `register`:

```php
// Enregistrez votre classe
Flight::register('user', User::class);

// Obtenez une instance de votre classe
$user = Flight::user();
```

La méthode d'enregistrement vous permet également de transmettre des paramètres à votre classe
constructeur. Ainsi, lorsque vous chargez votre classe personnalisée, elle sera pré-initialisée.
Vous pouvez définir les paramètres du constructeur en passant un tableau supplémentaire.
Voici un exemple de chargement d'une connexion de base de données:

```php
// Enregistrement de classe avec des paramètres de constructeur
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtenez une instance de votre classe
// Cela créera un objet avec les paramètres définis
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

Si vous passez un paramètre de rappel supplémentaire, il sera exécuté immédiatement
après la construction de la classe. Cela vous permet d'effectuer toute procédure de configuration pour votre
nouvel objet. La fonction de rappel prend un paramètre, une instance du nouvel objet.

```php
// L'appel de rappel recevra l'objet qui a été construit
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
Pour obtenir une nouvelle instance d'une classe, il suffit de passer `false` en tant que paramètre:

```php
// Instance partagée de la classe
$partagé = Flight::db();

// Nouvelle instance de la classe
$nouveau = Flight::db(false);
```

Gardez à l'esprit que les méthodes mappées ont la priorité sur les classes enregistrées. Si vous
déclarez les deux avec le même nom, seule la méthode mappée sera invoquée.