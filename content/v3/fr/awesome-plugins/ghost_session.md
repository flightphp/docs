# Ghostff/Session

Gestionnaire de session PHP (non-bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement/déchiffrement optionnel des données de session. Prend en charge les fichiers, MySQL, Redis et Memcached.

Cliquez [ici](https://github.com/Ghostff/Session) pour voir le code.

## Installation

Installez avec Composer.

```bash
composer require ghostff/session
```

## Configuration de base

Vous n'êtes pas obligé de passer quoi que ce soit pour utiliser les paramètres par défaut avec votre session. Vous pouvez lire davantage sur les paramètres dans le [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// une chose à retenir est que vous devez valider votre session à chaque chargement de page
// sinon, vous devrez exécuter auto_commit dans votre configuration. 
```

## Exemple simple

Voici un exemple simple de la façon dont vous pourriez utiliser cela.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faites votre logique de connexion ici
	// validez le mot de passe, etc.

	// si la connexion est réussie
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// chaque fois que vous écrivez dans la session, vous devez la valider délibérément.
	$session->commit();
});

// Cette vérification pourrait être dans la logique de la page restreinte, ou encapsulée avec un middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// faites votre logique de page restreinte ici
});

// la version middleware
Flight::route('/some-restricted-page', function() {
	// logique de page normale
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Exemple plus complexe

Voici un exemple plus complexe de la façon dont vous pourriez utiliser cela.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// définissez un chemin personnalisé vers votre fichier de configuration de session et donnez-lui une chaîne aléatoire pour l'identifiant de session
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// ou vous pouvez remplacer manuellement les options de configuration
		$session->updateConfiguration([
			// si vous souhaitez stocker vos données de session dans une base de données (bien si vous voulez quelque chose comme, "déconnectez-moi de tous les appareils" fonctionnalité)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // veuillez changer cela pour quelque chose d'autre
			Session::CONFIG_AUTO_COMMIT   => true, // ne le faites que si cela le nécessite et/ou s'il est difficile de valider() votre session.
												   // de plus, vous pourriez faire Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Pilote de base de données pour DNS PDO par ex (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Hôte de base de données
				'db_name'   => 'my_app_database',   # Nom de la base de données
				'db_table'  => 'sessions',          # Table de base de données
				'db_user'   => 'root',              # Nom d'utilisateur de la base de données
				'db_pass'   => '',                  # Mot de passe de la base de données
				'persistent_conn'=> false,          # Évitez le surcoût d'établir une nouvelle connexion chaque fois qu'un script doit communiquer avec une base de données, ce qui entraîne une application web plus rapide. TROUVEZ L'ARRIÈRE-PLAN VOUS-MÊME
			]
		]);
	}
);
```

## Aide ! Mes données de session ne persistent pas !

Vous définissez vos données de session et elles ne persistent pas entre les requêtes ? Vous avez peut-être oublié de valider vos données de session. Vous pouvez le faire en appelant `$session->commit()` après avoir défini vos données de session.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faites votre logique de connexion ici
	// validez le mot de passe, etc.

	// si la connexion est réussie
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// chaque fois que vous écrivez dans la session, vous devez la valider délibérément.
	$session->commit();
});
```

L'autre solution est que lorsque vous configurez votre service de session, vous devez définir `auto_commit` sur `true` dans votre configuration. Cela validera automatiquement vos données de session après chaque requête.

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

De plus, vous pourriez faire `Flight::after('start', function() { Flight::session()->commit(); });` pour valider vos données de session après chaque requête.

## Documentation

Visitez le [Github Readme](https://github.com/Ghostff/Session) pour la documentation complète. Les options de configuration sont [bien documentées dans le fichier default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php). Le code est simple à comprendre si vous souhaitez parcourir ce package vous-même.