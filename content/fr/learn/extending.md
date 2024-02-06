# Étendre / Conteneurs

Flight est conçu pour être un framework extensible. Le framework est livré avec un ensemble
de méthodes et de composants par défaut, mais il vous permet de mapper vos propres méthodes,
enregistrer vos propres classes, ou même remplacer les classes et méthodes existantes.

## Mapping des méthodes

Pour mapper votre propre méthode personnalisée, utilisez la fonction `map`:

```php
// Mapper votre méthode
Flight::map('bonjour', function (string $nom) {
  echo "bonjour $nom!";
});

// Appeler votre méthode personnalisée
Flight::bonjour('Bob');
```

## Enregistrement des Classes / Conteneurisation

Pour enregistrer votre propre classe, utilisez la fonction `register`:

```php
// Enregistrer votre classe
Flight::register('utilisateur', Utilisateur::class);

// Obtenir une instance de votre classe
$user = Flight::utilisateur();
```

La méthode d'enregistrement vous permet également de transmettre des paramètres à votre classe
constructeur. Ainsi, lorsque vous chargez votre classe personnalisée, elle sera pré-initialisée.
Vous pouvez définir les paramètres du constructeur en passant un tableau supplémentaire.
Voici un exemple de chargement d'une connexion à la base de données:

```php
// Enregistrer la classe avec des paramètres de constructeur
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'utilisateur', 'motdepasse']);

// Obtenir une instance de votre classe
//
$db = Flight::db();
```

Si vous passez en plus un paramètre de rappel, il sera exécuté immédiatement
après la construction de la classe. Cela vous permet d'effectuer toute procédure de configuration pour votre
nouvel objet. La fonction de rappel prend un paramètre, une instance du nouvel objet.

```php
// Le rappel recevra l'objet qui a été construit
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'utilisateur', 'motdepasse'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Par défaut, chaque fois que vous chargez votre classe, vous obtiendrez une instance partagée.
Pour obtenir une nouvelle instance d'une classe, il suffit de transmettre `false` en paramètre:

```php
// Instance partagée de la classe
$partagé = Flight::db();

// Nouvelle instance de la classe
$nouveau = Flight::db(false);
```

Gardez à l'esprit que les méthodes mappées ont la préséance sur les classes enregistrées. Si vous
déclarez les deux en utilisant le même nom, seule la méthode mappée sera invoquée.