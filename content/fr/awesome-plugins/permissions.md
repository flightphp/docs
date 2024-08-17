# FlightPHP/Autorisations

Il s'agit d'un module d'autorisations qui peut être utilisé dans vos projets si vous avez plusieurs rôles dans votre application et que chaque rôle a une fonctionnalité légèrement différente. Ce module vous permet de définir des autorisations pour chaque rôle, puis de vérifier si l'utilisateur actuel a l'autorisation d'accéder à une certaine page ou d'effectuer une certaine action.

Cliquez [ici](https://github.com/flightphp/permissions) pour accéder au dépôt sur GitHub.

Installation
-------
Exécutez `composer require flightphp/permissions` et c'est parti!

Utilisation
-------
Tout d'abord, vous devez configurer vos autorisations, puis vous indiquez à votre application ce que signifient les autorisations. En fin de compte, vous vérifierez vos autorisations avec `$Permissions->has()`, `->can()`, ou `is()`. `has()` et `can()` ont la même fonctionnalité, mais sont nommées différemment pour rendre votre code plus lisible.

## Exemple de base

Supposons que vous ayez une fonctionnalité dans votre application qui vérifie si un utilisateur est connecté. Vous pouvez créer un objet d'autorisations comme ceci:

```php
// index.php
require 'vendor/autoload.php';

// some code 

// then you probably have something that tells you who the current role is of the person
// likely you have something where you pull the current role
// from a session variable which defines this
// after someone logs in, otherwise they will have a 'guest' or 'public' role.
$current_role = 'admin';

// setup permissions
$permission = new \flight\Permission($current_role);
$permission->defineRule('loggedIn', function($current_role) {
	return $current_role !== 'guest';
});

// You'll probably want to persist this object in Flight somewhere
Flight::set('permission', $permission);
```

Ensuite, dans un contrôleur quelque part, vous pourriez avoir quelque chose comme ceci.

```php
<?php

// some controller
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('loggedIn')) {
			// do something
		} else {
			// do something else
		}
	}
}
```

Vous pouvez également utiliser ceci pour suivre s'ils ont l'autorisation de faire quelque chose dans votre application. Par exemple, si vous avez un moyen pour les utilisateurs d'interagir avec des publications sur votre logiciel, vous pouvez vérifier s'ils ont l'autorisation d'effectuer certaines actions.

```php
$current_role = 'admin';

// setup permissions
$permission = new \flight\Permission($current_role);
$permission->defineRule('post', function($current_role) {
	if($current_role === 'admin') {
		$permissions = ['create', 'read', 'update', 'delete'];
	} else if($current_role === 'editor') {
		$permissions = ['create', 'read', 'update'];
	} else if($current_role === 'author') {
		$permissions = ['create', 'read'];
	} else if($current_role === 'contributor') {
		$permissions = ['create'];
	} else {
		$permissions = [];
	}
	return $permissions;
});
Flight::set('permission', $permission);
```

Ensuite, dans un contrôleur quelque part...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.create')) {
			// do something
		} else {
			// do something else
		}
	}
}
```

## Injection de dépendances
Vous pouvez injecter des dépendances dans la fonction de fermeture qui définit les autorisations. C'est utile si vous avez un type de bascule, d'identifiant ou tout autre point de données que vous voulez vérifier. Le même principe s'applique aux appels de type Classe->Méthode, sauf que vous définissez les arguments dans la méthode.

### Fonctions de fermeture

```php
$Permission->defineRule('order', function(string $current_role, MyDependency $MyDependency = null) {
	// ... code
});

// dans votre fichier de contrôleur
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.create', $MyDependency)) {
		// do something
	} else {
		// do something else
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
Vous pouvez également utiliser des classes pour définir vos autorisations. C'est utile si vous avez beaucoup d'autorisations et que vous voulez garder votre code propre. Vous pouvez faire quelque chose comme ceci:
```php
<?php

// code de démarrage
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('order', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// En supposant que vous avez configuré cela au préalable
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'read' ]; // tout le monde peut consulter une commande
		if($current_role === 'manager') {
			$allowed_permissions[] = 'create'; // les gestionnaires peuvent créer des commandes
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'update'; // si l'utilisateur a une bascule spéciale, il peut mettre à jour des commandes
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'delete'; // les administrateurs peuvent supprimer des commandes
		}
		return $allowed_permissions;
	}
}
```
L'astuce est que vous pouvez également utiliser un raccourci (qui peut également être mis en cache!!!) où vous dites simplement à la classe d'autorisations de mapper toutes les méthodes d'une classe en autorisations. Ainsi, si vous avez une méthode nommée `order()` et une méthode nommée `company()`, cela sera automatiquement cartographié pour que vous puissiez simplement exécuter `$Permissions->has('order.read')` ou `$Permissions->has('company.read')` et cela fonctionnera. Définir cela est très difficile, donc suivez moi ici. Vous avez juste besoin de faire ceci:

Créez la classe des autorisations que vous souhaitez regrouper.
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// code pour déterminer les autorisations
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// code pour déterminer les autorisations
		return $permissions_array;
	}
}
```

Puis rendez les autorisations découvrables en utilisant cette bibliothèque.

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

Enfin, appelez l'autorisation dans votre code pour vérifier si l'utilisateur est autorisé à effectuer une autorisation donnée.

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.create') === false) {
			die('Vous ne pouvez pas créer une commande. Désolé!');
		}
	}
}
```

### Mise en cache

Pour activer la mise en cache, consultez la [bibliothèque simple wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache). Un exemple pour l'activer est ci-dessous.
```php

// cet $app peut faire partie de votre code, ou
// vous pouvez simplement passer null et il
// récupérera de Flight::app() dans le constructeur
$app = Flight::app();

// Pour l'instant, cela accepte cela comme cache de fichier. D'autres peuvent facilement
// être ajoutés à l'avenir. 
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 est le nombre de secondes pendant lesquels conserver cette mise en cache. Laissez ceci vide pour ne pas utiliser la mise en cache
```