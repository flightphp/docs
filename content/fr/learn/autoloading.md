# Chargement automatique

Le chargement automatique est un concept en PHP où vous spécifiez un répertoire ou des répertoires à charger des classes. Cela est bien plus bénéfique que d'utiliser `require` ou `include` pour charger des classes. C'est également une exigence pour utiliser les packages Composer.

Par défaut, toute classe `Flight` est chargée automatiquement grâce à Composer. Cependant, si vous souhaitez charger automatiquement vos propres classes, vous pouvez utiliser la méthode `Flight::path` pour spécifier un répertoire à partir duquel charger des classes.

## Exemple de base

Supposons que nous ayons une arborescence de répertoires comme suit :

```text
# Chemin d'exemple
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - contient les contrôleurs pour ce projet
│   ├── translations
│   ├── UTILS - contient des classes uniquement pour cette application (tout en majuscules à des fins d'exemple ultérieur)
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

Vous avez peut-être remarqué que cette structure de fichiers est similaire à celle de ce site de documentation.

Vous pouvez spécifier chaque répertoire à partir duquel charger comme ceci :

```php

/**
 * public/index.php
 */

// Ajouter un chemin au chargeur automatique
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// pas de namespace requis

// Il est recommandé que toutes les classes chargées automatiquement soient en Pascal Case (chaque mot en majuscule, sans espaces)
// Il est obligatoire de ne pas avoir de trait de soulignement dans le nom de votre classe
class MyController {

	public function index() {
		// faire quelque chose
	}
}
```

## Espaces de noms

Si vous avez des espaces de noms, il devient en fait très facile de les implémenter. Vous devriez utiliser la méthode `Flight::path()` pour spécifier le répertoire racine (pas le répertoire du document ou le dossier `public/`) de votre application.

```php

/**
 * public/index.php
 */

// Ajouter un chemin au chargeur automatique
Flight::path(__DIR__.'/../');
```

Maintenant, voici à quoi pourrait ressembler votre contrôleur. Regardez l'exemple ci-dessous, mais faites attention aux commentaires pour des informations importantes.

```php
/**
 * app/controllers/MyController.php
 */

// les espaces de noms sont requis
// les espaces de noms sont identiques à la structure du répertoire
// les espaces de noms doivent suivre la même casse que la structure du répertoire
// les espaces de noms et les répertoires ne peuvent pas contenir de traits de soulignement
namespace app\controllers;

// Il est recommandé que toutes les classes chargées automatiquement soient en Pascal Case (chaque mot en majuscule, sans espaces)
// Il est obligatoire de ne pas avoir de trait de soulignement dans le nom de votre classe
class MyController {

	public function index() {
		// faire quelque chose
	}
}
```

Et si vous vouliez charger automatiquement une classe dans votre répertoire utils, vous feriez à peu près la même chose :

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// l'espace de noms doit correspondre à la structure du répertoire et à la casse (notez que le répertoire UTILS est en majuscules
//     comme dans l'arborescence des fichiers ci-dessus)
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// faire quelque chose
	}
}
```