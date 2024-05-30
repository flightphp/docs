# Ghostff/Session

Gestionnaire de sessions PHP (non bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement / déchiffrement facultatif des données de session. Prise en charge des fichiers, MySQL, Redis et Memcached.

## Installation

Installez avec composer.

```bash
composer require ghostff/session
```

## Configuration de base

Vous n'êtes pas obligé de passer quoi que ce soit pour utiliser les paramètres par défaut avec votre session. Vous pouvez en savoir plus sur d'autres paramètres dans le [Lisez-moi Github](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// une chose à se rappeler est que vous devez valider votre session à chaque chargement de page
// ou vous devrez exécuter auto_commit dans votre configuration.
```

## Exemple simple

Voici un exemple simple de comment vous pourriez utiliser ceci.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faites votre logique de connexion ici
	// valider le mot de passe, etc.

	// si la connexion réussit
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// chaque fois que vous écrivez dans la session, vous devez la valider délibérément.
	$session->commit();
});

// Cette vérification pourrait être dans la logique de page restreinte, ou enveloppée dans un middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// faites votre logique de page restreinte ici
});

// la version middleware
Flight::route('/some-restricted-page', function() {
	// logique de page régulière
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## Exemple plus complexe

Voici un exemple plus complexe de comment vous pourriez utiliser ceci.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// définir un chemin personnalisé vers votre fichier de configuration de session et donnez-lui une chaîne aléatoire pour l'ID de session
$app->register('session', Session::class, [ 'chemin/vers/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// ou vous pouvez remplacer manuellement les options de configuration
		$session->updateConfiguration([
			// si vous souhaitez stocker vos données de session dans une base de données (utile si vous voulez quelque chose comme "déconnectez-moi de tous les appareils" fonctionnalité)
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mon-super-sel-S3CR3T'), // veuillez le changer pour quelque chose d'autre
			Session::CONFIG_AUTO_COMMIT   => true, // faites-le seulement si c'est nécessaire et / ou s'il est difficile de valider () votre session.
												   // de plus, vous pourriez faire Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Pilote de base de données pour le dns PDO par exemple (mysql:host=...;dbname=...)
				'hôte'      => '127.0.0.1',         # Hôte de la base de données
				'nom_db'   => 'ma_base_de_données_app',   # Nom de la base de données
				'table_db'  => 'sessions',          # Table de la base de données
				'utilisateur_db'   => 'root',              # Nom d'utilisateur de la base de données
				'mot_de_passe_db'   => '',                  # Mot de passe de la base de données
				'connexion_persistante'=> false,          # Évitez les frais généraux de l'établissement d'une nouvelle connexion à chaque fois qu'un script doit parler à une base de données, ce qui donne une application Web plus rapide. TROUVER LE DERRIÈRE PAR VOUS-MÊME
			]
		]);
	}
);
```

## Aide ! Mes données de session ne persistent pas !

Définissez-vous vos données de session et elles ne persistent pas entre les requêtes ? Vous pourriez avoir oublié de valider vos données de session. Vous pouvez le faire en appelant `$session->commit()` après avoir défini vos données de session.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faites votre logique de connexion ici
	// valider le mot de passe, etc.

	// si la connexion réussit
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// chaque fois que vous écrivez dans la session, vous devez la valider délibérément.
	$session->commit();
});
```

L'autre moyen d'y remédier est lorsque vous configurez votre service de session, vous devez définir `auto_commit` sur `true` dans votre configuration. Cela validera automatiquement vos données de session après chaque requête.

```php

$app->register('session', Session::class, [ 'chemin/vers/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

En outre, vous pourriez faire `Flight::after('start', function() { Flight::session()->commit(); });` pour valider vos données de session après chaque requête.

## Documentation

Visitez le [Lisez-moi Github](https://github.com/Ghostff/Session) pour une documentation complète. Les options de configuration sont [bien documentées dans le fichier default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) lui-même. Le code est facile à comprendre si vous voulez examiner ce package vous-même.