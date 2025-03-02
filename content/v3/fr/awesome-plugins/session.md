# Ghostff/Session

PHP Gestionnaire de session (non bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement/déchiffrement optionnel des données de session. Prise en charge des fichiers, MySQL, Redis et Memcached.

Cliquez [ici](https://github.com/Ghostff/Session) pour voir le code.

## Installation

Installez avec composer.

```bash
composer require ghostff/session
```

## Configuration de base

Vous n'êtes pas obligé de passer quoi que ce soit pour utiliser les paramètres par défaut de votre session. Vous pouvez en savoir plus sur d'autres paramètres dans le [Lisez-moi Github](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// une chose à retenir est que vous devez valider votre session à chaque chargement de page
// ou vous devrez exécuter auto_commit dans votre configuration.
```

## Exemple Simple

Voici un exemple simple de la manière dont vous pourriez utiliser ceci.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// effectuez votre logique de connexion ici
	// validez le mot de passe, etc.

	// si la connexion réussit
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// à chaque fois que vous écrivez dans la session, vous devez la valider délibérément.
	$session->commit();
});

// Cette vérification pourrait être dans la logique de page restreinte, ou enveloppée dans un middleware.
Flight::route('/page-restreinte', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// effectuez votre logique de page restreinte ici
});

// la version du middleware
Flight::route('/page-restreinte', function() {
	// logique de page régulière
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Exemple Plus Complexe

Voici un exemple plus complexe de la manière dont vous pourriez utiliser ceci.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// définissez un chemin personnalisé pour votre fichier de configuration de session et donnez-lui une chaîne aléatoire pour l'identifiant de session
$app->register('session', Session::class, [ 'chemin/vers/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// ou vous pouvez remplacer manuellement les options de configuration
		$session->updateConfiguration([
			// si vous voulez stocker vos données de session dans une base de données (utile si vous souhaitez quelque chose comme la fonctionnalité "déconnectez-moi de tous les appareils")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mon-S3CR3T-sel-super'), // veuillez changer ceci pour quelque chose d'autre
			Session::CONFIG_AUTO_COMMIT   => true, // faites-le uniquement si c'est nécessaire et/ou s'il est difficile de valider() votre session.
												   // de plus, vous pourriez faire Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Pilote de base de données pour le dns PDO ex.(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Hôte de la base de données
				'db_name'   => 'ma_base_de_données_app',   # Nom de la base de données
				'db_table'  => 'sessions',          # Table de la base de données
				'db_user'   => 'root',              # Nom d'utilisateur de la base de données
				'db_pass'   => '',                  # Mot de passe de la base de données
				'persistent_conn'=> false,          # Éviter les frais généraux de l'établissement d'une nouvelle connexion à chaque fois qu'un script doit dialoguer avec une base de données, ce qui donne une application web plus rapide. TROUVEZ LE CÔTÉ OBSCUR PAR VOUS-MÊME
			]
		]);
	}
);
```

## Au Secours ! Mes données de session ne persistent pas !

Vous définissez vos données de session et elles ne persistent pas entre les requêtes ? Vous avez peut-être oublié de valider vos données de session. Vous pouvez le faire en appelant `$session->commit()` après avoir défini vos données de session.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// effectuez votre logique de connexion ici
	// valider le mot de passe, etc.

	// si la connexion réussit
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// à chaque fois que vous écrivez dans la session, vous devez la valider délibérément.
	$session->commit();
});
```

L'autre façon de contourner cela est lorsque vous configurez votre service de session, vous devez définir `auto_commit` sur `true` dans votre configuration. Cela validera automatiquement vos données de session après chaque requête.

```php

$app->register('session', Session::class, [ 'chemin/vers/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

De plus, vous pourriez faire `Flight::after('start', function() { Flight::session()->commit(); });` pour valider vos données de session après chaque requête.

## Documentation

Consultez le [Lisez-moi Github](https://github.com/Ghostff/Session) pour la documentation complète. Les options de configuration sont [bien documentées dans le fichier default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) lui-même. Le code est simple à comprendre si vous souhaitiez parcourir ce package vous-même.