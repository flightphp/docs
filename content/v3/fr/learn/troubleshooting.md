# Dépannage

Cette page vous aidera à résoudre les problèmes courants que vous pourriez rencontrer lors de l'utilisation de Flight.

## Problèmes Courants

### 404 Non Trouvé ou Comportement de Route Inattendu

Si vous voyez une erreur 404 Non Trouvé (mais vous jurez sur votre vie qu'elle est vraiment là et ce n'est pas une faute de frappe), en fait il pourrait s'agir d'un problème avec le fait de renvoyer une valeur dans votre point de terminaison de route au lieu de simplement l'afficher. La raison de ceci est intentionnelle mais pourrait surprendre certains développeurs.

```php

Flight::route('/bonjour', function(){
	// Cela pourrait provoquer une erreur 404 Non Trouvée
	return 'Bonjour le monde';
});

// Ce que vous voulez probablement
Flight::route('/bonjour', function(){
	echo 'Bonjour le monde';
});

```

La raison de ceci est due à un mécanisme spécial intégré dans le routeur qui gère la sortie retournée comme un signal pour "passer à la route suivante". 
Vous pouvez voir le comportement documenté dans la section [Routing](/learn/routing#passing).

### Classe Non Trouvée (chargement automatique ne fonctionne pas)

Il pourrait y avoir plusieurs raisons pour que cela ne se produise pas. Voici quelques exemples, mais assurez-vous également de consulter la section sur le [chargement automatique](/learn/autoloading).

#### Nom de Fichier Incorrect
Le plus courant est que le nom de la classe ne correspond pas au nom du fichier.

Si vous avez une classe nommée `MaClasse`, alors le fichier devrait être nommé `MaClasse.php`. Si vous avez une classe nommée `MaClasse` et que le fichier est nommé `maclasse.php`, alors le chargeur automatique ne pourra pas le trouver.

#### Espace de Noms Incorrect
Si vous utilisez des espaces de noms, alors l'espace de noms doit correspondre à la structure des répertoires.

```php
// code

// si votre MyController est dans le répertoire app/controllers et qu'il est namespaced
// cela ne fonctionnera pas.
Flight::route('/bonjour', 'MyController->bonjour');

// vous devrez choisir l'une de ces options
Flight::route('/bonjour', 'app\controllers\MyController->bonjour');
// ou si vous avez une déclaration use en haut

use app\controllers\MyController;

Flight::route('/bonjour', [ MyController::class, 'bonjour' ]);
// peut également être écrit
Flight::route('/bonjour', MyController::class.'->bonjour');
// aussi...
Flight::route('/bonjour', [ 'app\controllers\MyController', 'bonjour' ]);
```

#### `path()` non défini

Dans l'application squelette, cela est défini à l'intérieur du fichier `config.php`, mais pour que vos classes soient trouvées, vous devez vous assurer que la méthode `path()` est définie (probablement à la racine de votre répertoire) avant d'essayer de l'utiliser.

```php

// Ajouter un chemin à l'autoload
Flight::path(__DIR__.'/../');

```