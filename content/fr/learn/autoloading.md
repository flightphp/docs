# Chargement automatique

Le chargement automatique est un concept en PHP où vous spécifiez un répertoire ou des répertoires à partir desquels charger des classes. Cela est bien plus avantageux que d'utiliser `require` ou `include` pour charger des classes. C'est également une exigence pour utiliser des paquets Composer.

Par défaut, toute classe `Flight` est chargée automatiquement pour vous grâce à Composer. Cependant, si vous souhaitez charger automatiquement vos propres classes, vous pouvez utiliser la méthode `Flight::path` pour spécifier un répertoire à partir duquel charger des classes.

## Exemple Basique

Supposons que nous avons une arborescence de répertoires comme suit :

```text
# Chemin d'exemple
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - contient les contrôleurs de ce projet
│   ├── translations
│   ├── UTILS - contient des classes uniquement pour cette application (tout en majuscules à des fins d'exemple ultérieur)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Vous avez peut-être remarqué que c'est la même structure de fichiers que ce site de documentation.

Vous pouvez spécifier chaque répertoire à charger à partir de cette manière :

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

// Toutes les classes chargées automatiquement sont recommandées d'être en Pascal Case (chaque mot est en majuscule, pas d'espaces)
// À partir de la version 3.7.2, vous pouvez utiliser Pascal_Snake_Case pour vos noms de classe en exécutant Loader::setV2ClassLoading(false);
class MyController {

	public function index() {
		// faire quelque chose
	}
}
```

## Espaces de noms

Si vous avez des espaces de noms, il devient en fait très facile de les implémenter. Vous devriez utiliser la méthode `Flight::path()` pour spécifier le répertoire racine (pas le document root ou le dossier `public/`) de votre application.

```php

/**
 * public/index.php
 */

// Ajouter un chemin à l'autoloader
Flight::path(__DIR__.'/../');
```

Maintenant, voici à quoi pourrait ressembler votre contrôleur. Regardez l'exemple ci-dessous, mais faites attention aux commentaires pour des informations importantes.

```php
/**
 * app/controllers/MyController.php
 */

// les espaces de noms sont requis
// les espaces de noms sont les mêmes que la structure du répertoire
// les espaces de noms doivent suivre la même casse que la structure du répertoire
// les espaces de noms et les répertoires ne peuvent pas avoir de traits de soulignement (sauf si Loader::setV2ClassLoading(false) est défini)
namespace app\controllers;

// Toutes les classes chargées automatiquement sont recommandées d'être en Pascal Case (chaque mot est en majuscule, pas d'espaces)
// À partir de la version 3.7.2, vous pouvez utiliser Pascal_Snake_Case pour vos noms de classe en exécutant Loader::setV2ClassLoading(false);
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
//     comme dans l'arborescence des fichiers ci-dessus)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// faire quelque chose
	}
}
```

## Traits de soulignement dans les Noms de Classe

À partir de la version 3.7.2, vous pouvez utiliser Pascal_Snake_Case pour vos noms de classe en exécutant `Loader::setV2ClassLoading(false);`. Cela vous permettra d'utiliser des traits de soulignement dans vos noms de classe. Ce n'est pas recommandé, mais c'est disponible pour ceux qui en ont besoin.

```php

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