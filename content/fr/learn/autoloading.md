# Chargement automatique

Le chargement automatique est un concept en PHP où vous spécifiez un répertoire ou des répertoires à partir desquels charger des classes. Cela est bien plus bénéfique que d'utiliser `require` ou `include` pour charger des classes. C'est également une exigence pour utiliser des packages Composer.

Par défaut, toute classe `Flight` est chargée automatiquement pour vous grâce à composer. Cependant, si vous voulez charger automatiquement vos propres classes, vous pouvez utiliser la méthode `Flight::path()` pour spécifier un répertoire à partir duquel charger des classes.

## Exemple de base

Supposons que nous avons une structure d'arborescence de répertoires comme suit :

```text
# Chemin d'exemple
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - contient les contrôleurs pour ce projet
│   ├── translations
│   ├── UTILS - contient des classes uniquement pour cette application (tout est en majuscules à des fins d'exemple ultérieur)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Vous avez peut-être remarqué que c'est la même structure de fichiers que ce site de documentation.

Vous pouvez spécifier chaque répertoire à charger à partir de cette façon :

```php
/**
 * public/index.php
 */

// Ajouter un chemin à l'autoload
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');

/**
 * app/controllers/MyController.php
 */

// Pas de nécessité d'espace de noms

// Toutes les classes chargées automatiquement sont recommandées d'être en Pascal Case (chaque mot en majuscule, pas d'espaces)
// À partir de la version 3.7.2, vous pouvez utiliser Pascal_Snake_Case pour vos noms de classes en exécutant Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// faire quelque chose
	}
}
```

## Espaces de noms

Si vous avez des espaces de noms, l'implémentation en devient en fait très facile. Vous devriez utiliser la méthode `Flight::path()` pour spécifier le répertoire racine (pas le document root ou le dossier `public/`) de votre application.

```php
/**
 * public/index.php
 */

// Ajouter un chemin à l'autoload
Flight::path(__DIR__.'/../');
```

Maintenant, voici à quoi pourrait ressembler votre contrôleur. Regardez l'exemple ci-dessous, mais prêtez attention aux commentaires pour des informations importantes.

```php
/**
 * app/controllers/MyController.php
 */

// les espaces de noms sont requis
// les espaces de noms doivent suivre la même structure de répertoire
// les espaces de noms doivent suivre la même casse que la structure de répertoire
// les espaces de noms et les répertoires ne peuvent pas avoir de tirets bas (à moins que Loader::setV2ClassLoading(false) ne soit défini)
namespace app\controllers;

// Toutes les classes chargées automatiquement sont recommandées d'être en Pascal Case (chaque mot en majuscule, pas d'espaces)
// À partir de la version 3.7.2, vous pouvez utiliser Pascal_Snake_Case pour vos noms de classes en exécutant Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// faire quelque chose
	}
}
```

Et si vous vouliez charger automatiquement une classe dans votre répertoire utils, vous feriez essentiellement la même chose :

```php
/**
 * app/UTILS/ArrayHelperUtil.php
 */

// l'espace de noms doit correspondre à la structure du répertoire et à la casse (notez que le répertoire UTILS est tout en majuscules
//     comme dans l'arborescence de fichiers ci-dessus)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// faire quelque chose
	}
}
```

## Tirets bas dans les noms de classes

À partir de la version 3.7.2, vous pouvez utiliser Pascal_Snake_Case pour vos noms de classes en exécutant `Loader::setV2ClassLoading(false);`.
Cela vous permettra d'utiliser des tirets bas dans vos noms de classes.
Ce n'est pas recommandé, mais c'est disponible pour ceux qui en ont besoin.

```php
/**
 * public/index.php
 */

// Ajouter un chemin à l'autoload
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// Pas de nécessité d'espace de noms

class My_Controller {

	public function index() {
		// faire quelque chose
	}
}
```  