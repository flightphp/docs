# FlightPHP/Autorisations

Il s'agit d'un module d'autorisations qui peut être utilisé dans vos projets si vous avez plusieurs rôles dans votre application et que chaque rôle a une fonctionnalité légèrement différente. Ce module vous permet de définir des autorisations pour chaque rôle, puis de vérifier si l'utilisateur actuel a l'autorisation d'accéder à une certaine page ou d'effectuer une certaine action.

Installation
-------
Exécutez `composer require flightphp/permissions` et c'est parti !

Utilisation
-------
Tout d'abord, vous devez configurer vos autorisations, puis vous indiquez à votre application ce que signifient les autorisations. En fin de compte, vous vérifierez vos autorisations avec `$ Autorisations->a()`, `->peut()` ou `is()`. `a()` et `peut()` ont la même fonctionnalité, mais sont nommés différemment pour rendre votre code plus lisible.

## Exemple de base

Supposons que vous ayez une fonctionnalité dans votre application qui vérifie si un utilisateur est connecté. Vous pouvez créer un objet d'autorisations comme ceci :

```php
// index.php
require 'vendor/autoload.php';

// some code

// puis vous avez probablement quelque chose qui vous indique quel est le rôle actuel de la personne
// probablement vous avez quelque chose où vous extrayez le rôle actuel
// à partir d'une variable de session qui le définit
// après la connexion de quelqu'un, sinon ils auront un rôle 'invité' ou 'public'.
$current_role = 'admin';

// configuration des autorisations
$autorisation = new \flight\Permission($current_role);
$autorisation->defineRule('connecté', function($current_role) {
	return $current_role !== 'invité';
});

// Vous voudrez probablement persister cet objet quelque part dans Flight
Flight::set('autorisation', $autorisation);
```

Ensuite, dans un contrôleur quelque part, vous pourriez avoir quelque chose comme ceci.

```php
<?php

// some controller
class SomeController {
	public function someAction() {
		$autorisation = Flight::get('autorisation');
		if ($autorisation->a('connecté')) {
			// faire quelque chose
		} else {
			// faire autre chose
		}
	}
}
```

Vous pouvez également l'utiliser pour suivre s'ils ont l'autorisation de faire quelque chose dans votre application.
Par exemple, si vous avez un moyen pour les utilisateurs d'interagir avec des publications sur votre logiciel, vous pouvez vérifier s'ils ont l'autorisation d'effectuer certaines actions.

```php
$current_role = 'admin';

// configuration des autorisations
$autorisation = new \flight\Permission($current_role);
$autorisation->defineRule('publication', function($current_role) {
	if($current_role === 'admin') {
		$autorisations = ['créer', 'lire', 'mettre à jour', 'supprimer'];
	} else if($current_role === 'éditeur') {
		$autorisations = ['créer', 'lire', 'mettre à jour'];
	} else if($current_role === 'auteur') {
		$autorisations = ['créer', 'lire'];
	} else if($current_role === 'contributeur') {
		$autorisations = ['créer'];
	} else {
		$autorisations = [];
	}
	return $autorisations;
});
Flight::set('autorisation', $autorisation);
```

Ensuite, dans un contrôleur quelque part…

```php
class PostController {
	public function create() {
		$autorisation = Flight::get('autorisation');
		if ($autorisation->peut('publication.créer')) {
			// faire quelque chose
		} else {
			// faire autre chose
		}
	}
}
```

## Injection de dépendances
Vous pouvez injecter des dépendances dans la fermeture qui définit les autorisations. C'est utile si vous avez une sorte de bascule, un identifiant ou tout autre point de données que vous souhaitez vérifier. La même chose fonctionne pour les appels de type Classe->Méthode, sauf que vous définissez les arguments dans la méthode.

### Fermetures

```php
$Permission->defineRule('commande', function(string $current_role, MyDependency $MyDependency = null) {
	// ... code
});

// dans votre fichier de contrôleur
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$autorisation = Flight::get('autorisation');
	if ($autorisation->peut('commande.créer', $MyDependency)) {
		// faire quelque chose
	} else {
		// faire autre chose
	}
}
```

### Classes

```php
namespace MyApp;

class Permissions {

	public function order(string $current_role, MyDependency $MyDependency = null) {
		// ... code
	}
}
```

## Raccourci pour définir des autorisations avec des classes
Vous pouvez également utiliser des classes pour définir vos autorisations. C'est utile si vous avez beaucoup d'autorisations et que vous voulez garder votre code propre. Vous pouvez faire quelque chose comme ceci :
```php
<?php

// code d'amorçage
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('commande', 'MyApp\Permissions->commande');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// En supposant que vous l'avez configuré au préalable
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$autorisations_autorisées = [ 'lire' ]; // tout le monde peut afficher une commande
		if($current_role === 'manager') {
			$autorisations_autorisées[] = 'créer'; // les responsables peuvent créer des commandes
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$autorisations_autorisées[] = 'mettre à jour'; // si l'utilisateur a un bascule spécial, il peut mettre à jour les commandes
		}
		if($current_role === 'admin') {
			$autorisations_autorisées[] = 'supprimer'; // les administrateurs peuvent supprimer des commandes
		}
		return $autorisations_autorisées;
	}
}
```
La partie cool est qu'il existe également un raccourci que vous pouvez utiliser (qui peut également être mis en cache !!!) où vous indiquez simplement à la classe d'autorisations de mapper automatiquement toutes les méthodes d'une classe en autorisations. Donc, si vous avez une méthode nommée `commande()` et une méthode nommée `entreprise()`, celles-ci seront automatiquement mappées pour que vous puissiez simplement exécuter `$Permissions->a('commande.lire')` ou `$Permissions->a('entreprise.lire')` et cela fonctionnera. Définir cela est très difficile, alors restez avec moi ici. Vous devez simplement faire ceci :

Créez la classe d'autorisations que vous souhaitez regrouper.
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// code pour déterminer les autorisations
		return $tableau_autorisations;
	}

	public function company(string $current_role, int $company_id): array {
		// code pour déterminer les autorisations
		return $tableau_autorisations;
	}
}
```

Ensuite, rendez les autorisations découvrables en utilisant cette bibliothèque.

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

Enfin, appelez l'autorisation dans votre code pour vérifier si l'utilisateur est autorisé à effectuer une autorisation donnée.

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('commande.créer') === false) {
			die('Vous ne pouvez pas créer une commande. Désolé !');
		}
	}
}
```

### Mise en cache

Pour activer la mise en cache, voir la simple [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) bibliothèque. Un exemple pour l'activer est ci-dessous.
```php

// cette $app peut faire partie de votre code, ou
// vous pouvez simplement passer null et il
// récupérera de Flight::app() dans le constructeur
$app = Flight::app();

// Pour l'instant, il accepte cela comme un cache de fichier. D'autres peuvent facilement
// être ajoutés à l'avenir.
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 est le nombre de secondes pendant lesquels cela sera mis en cache. Laissez-le de côté pour ne pas utiliser le cache
```

Et c'est parti !