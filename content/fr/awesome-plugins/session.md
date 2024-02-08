# Ghostff/Session

Gestionnaire de sessions PHP (non bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement/déchiffrement optionnel des données de session. Prend en charge les fichiers, MySQL, Redis et Memcached.

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

// une chose à retenir est que vous devez valider votre session à chaque chargement de page
// ou vous devrez exécuter un auto-commit dans votre configuration.
```

## Exemple simple

Voici un exemple simple de la façon dont vous pourriez l'utiliser.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// effectuez votre logique de connexion ici
	// valider le mot de passe, etc.

	// si la connexion réussit
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// à chaque écriture dans la session, vous devez la valider délibérément.
	$session->commit();
});

// Cette vérification pourrait être dans la logique de page restreinte, ou enveloppée dans un intergiciel.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// effectuez votre logique de page restreinte ici
});

// la version intergiciel
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

Voici un exemple plus complexe de la façon dont vous pourriez l'utiliser.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// définir un chemin personnalisé vers le fichier de configuration de votre session et attribuer une chaîne aléatoire pour l'identifiant de session
$app->register('session', Session::class, [ 'chemin/vers/fichier_configuration_session.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// ou vous pouvez remplacer manuellement les options de configuration
		$session->updateConfiguration([
			// si vous voulez stocker vos données de session dans une base de données (utile si vous voulez quelque chose comme "déconnectez-moi de tous les appareils")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'mon-super-S3CR3T-sel'), // veuillez changer ceci pour quelque chose d'autre
			Session::CONFIG_AUTO_COMMIT   => true, // faites-le seulement s'il est nécessaire et/ou s'il est difficile de commettre() votre session.
												// de plus, vous pourriez faire Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Pilote de base de données pour dns PDO par exemple (mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # Hôte de la base de données
				'db_name'   => 'ma_base_de_données_app',   # Nom de la base de données
				'db_table'  => 'sessions',          # Table de la base de données
				'db_user'   => 'root',              # Nom d'utilisateur de la base de données
				'db_pass'   => '',                  # Mot de passe de la base de données
				'persistent_conn'=> false,          # Évitez les frais généraux liés à l'établissement d'une nouvelle connexion à chaque fois qu'un script doit parler à une base de données, ce qui se traduit par une application web plus rapide. TROUVEZ LE PENDANT TOI-MÊME
			]
		]);
	}
);
```

## Documentation

Visitez le [Lisez-moi Github](https://github.com/Ghostff/Session) pour la documentation complète. Les options de configuration sont [bien documentées dans le fichier default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) lui-même. Le code est simple à comprendre si vous souhaitez parcourir ce package vous-même.