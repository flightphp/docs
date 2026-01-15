# Autoloading

## Aperçu

L'autoloading est un concept en PHP où vous spécifiez un répertoire ou des répertoires pour charger les classes. Cela est beaucoup plus avantageux que d'utiliser `require` ou `include` pour charger les classes. C'est également une exigence pour utiliser les paquets Composer.

## Comprendre

Par défaut, toute classe `Flight` est autoloadée automatiquement pour vous grâce à Composer. Cependant, si vous souhaitez autoloader vos propres classes, vous pouvez utiliser la méthode `Flight::path()` pour spécifier un répertoire à partir duquel charger les classes.

L'utilisation d'un autoloader peut simplifier votre code de manière significative. Au lieu d'avoir des fichiers qui commencent par une multitude d'instructions `include` ou `require` en haut pour capturer toutes les classes utilisées dans ce fichier, vous pouvez au lieu de cela appeler dynamiquement vos classes et elles seront incluses automatiquement.

## Utilisation de base

Supposons que nous ayons un arbre de répertoires comme suit :

```text
# Exemple de chemin
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - contient les contrôleurs pour ce projet
│   ├── translations
│   ├── UTILS - contient les classes pour cette application uniquement (tout en majuscules exprès pour un exemple plus tard)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Vous avez peut-être remarqué que c'est la même structure de fichiers que celle de ce site de documentation.

Vous pouvez spécifier chaque répertoire à charger comme ceci :

```php

/**
 * public/index.php
 */

// Ajouter un chemin à l'autoloader
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// pas de namespace requis

// Toutes les classes autoloadées sont recommandées en Pascal Case (chaque mot avec majuscule, sans espaces)
class MyController {

	public function index() {
		// faire quelque chose
	}
}
```

## Namespaces

Si vous avez des namespaces, il devient en fait très facile de les implémenter. Vous devriez utiliser la méthode `Flight::path()` pour spécifier le répertoire racine (pas la racine du document ou le dossier `public/`) de votre application.

```php

/**
 * public/index.php
 */

// Ajouter un chemin à l'autoloader
Flight::path(__DIR__.'/../');
```

Maintenant, voici à quoi pourrait ressembler votre contrôleur. Regardez l'exemple ci-dessous, mais prêtez attention aux commentaires pour des informations importantes.

```php
/**
 * app/controllers/MyController.php
 */

// les namespaces sont requis
// les namespaces sont les mêmes que la structure des répertoires
// les namespaces doivent suivre le même cas que la structure des répertoires
// les namespaces et les répertoires ne peuvent pas avoir de tirets bas (sauf si Loader::setV2ClassLoading(false) est défini)
namespace app\controllers;

// Toutes les classes autoloadées sont recommandées en Pascal Case (chaque mot avec majuscule, sans espaces)
// À partir de 3.7.2, vous pouvez utiliser Pascal_Snake_Case pour les noms de vos classes en exécutant Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// faire quelque chose
	}
}
```

Et si vous vouliez autoloader une classe dans votre répertoire utils, vous feriez essentiellement la même chose :

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// le namespace doit correspondre à la structure des répertoires et au cas (notez que le répertoire UTILS est tout en majuscules
//     comme dans l'arbre de fichiers ci-dessus)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// faire quelque chose
	}
}
```

## Tirets bas dans les noms de classes

À partir de 3.7.2, vous pouvez utiliser Pascal_Snake_Case pour les noms de vos classes en exécutant `Loader::setV2ClassLoading(false);`. 
Cela vous permettra d'utiliser des tirets bas dans les noms de vos classes. 
Cela n'est pas recommandé, mais il est disponible pour ceux qui en ont besoin.

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// Ajouter un chemin à l'autoloader
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// pas de namespace requis

class My_Controller {

	public function index() {
		// faire quelque chose
	}
}
```

## Voir aussi
- [Routing](/learn/routing) - Comment mapper les routes aux contrôleurs et rendre les vues.
- [Why a Framework?](/learn/why-frameworks) - Comprendre les avantages d'utiliser un framework comme Flight.

## Dépannage
- Si vous ne parvenez pas à comprendre pourquoi vos classes avec namespace ne sont pas trouvées, rappelez-vous d'utiliser `Flight::path()` vers le répertoire racine de votre projet, pas votre répertoire `app/` ou `src/` ou équivalent.

### Classe non trouvée (autoloading ne fonctionne pas)

Il pourrait y avoir plusieurs raisons pour cela. Ci-dessous quelques exemples, mais assurez-vous de consulter également la section [autoloading](/learn/autoloading).

#### Nom de fichier incorrect
Le plus courant est que le nom de la classe ne correspond pas au nom du fichier.

Si vous avez une classe nommée `MyClass`, alors le fichier devrait s'appeler `MyClass.php`. Si vous avez une classe nommée `MyClass` et que le fichier s'appelle `myclass.php` 
alors l'autoloader ne pourra pas la trouver.

#### Namespace incorrect
Si vous utilisez des namespaces, alors le namespace devrait correspondre à la structure des répertoires.

```php
// ...code...

// si votre MyController est dans le répertoire app/controllers et qu'il est namespacé
// cela ne fonctionnera pas.
Flight::route('/hello', 'MyController->hello');

// vous devrez choisir l'une de ces options
Flight::route('/hello', 'app\controllers\MyController->hello');
// ou si vous avez une instruction use en haut

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// peut aussi être écrit
Flight::route('/hello', MyController::class.'->hello');
// aussi...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` non défini

Dans l'application squelette, cela est défini dans le fichier `config.php`, mais pour que vos classes soient trouvées, vous devez vous assurer que la méthode `path()`
est définie (probablement vers la racine de votre répertoire) avant d'essayer de l'utiliser.

```php
// Ajouter un chemin à l'autoloader
Flight::path(__DIR__.'/../');
```

## Journal des modifications
- v3.7.2 - Vous pouvez utiliser Pascal_Snake_Case pour les noms de vos classes en exécutant `Loader::setV2ClassLoading(false);`
- v2.0 - Fonctionnalité d'autoload ajoutée.