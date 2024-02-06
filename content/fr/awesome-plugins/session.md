# Ghostff/Session

Gestionnaire de sessions PHP (non bloquant, flash, segment, chiffrement des sessions). Utilise PHP open_ssl pour le chiffrement/déchiffrement facultatif des données de session. Prise en charge de File, MySQL, Redis et Memcached.

## Installation

Installez avec composer.

```bash
composer require ghostff/session
```

## Configuration de base

Vous n'êtes pas obligé de passer quoi que ce soit pour utiliser les paramètres par défaut avec votre session. Vous pouvez en savoir plus sur d'autres paramètres dans le [Github Readme](https://github.com/Ghostff/Session).

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// Une chose à retenir est que vous devez valider votre session à chaque chargement de page
// ou vous devrez exécuter auto_commit dans votre configuration
```

## Exemple simple

Voici un exemple simple de la façon dont vous pourriez utiliser ceci.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// faites votre logique de connexion ici
	// validez le mot de passe, etc.

	// si la connexion réussit
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// chaque fois que vous écrivez dans la session, vous devez la valider délibérément.
	$session->commit();
});

// Cette vérification pourrait être dans la logique de la page restreinte, ou enveloppée avec un intergiciel.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// faites votre logique de page restreinte ici
});

// la version intergiciel
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

Voici un exemple plus complexe de la façon dont vous pourriez utiliser ceci.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// définissez un chemin personnalisé vers votre fichier de configuration de session et donnez-lui une chaîne aléatoire pour l'ID de session
$app->register('session', Session::class, [ 'chemin/vers/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// ou vous pouvez remplacer manuellement les options de configuration
		$session->updateConfiguration([
			// si vous voulez stocker vos données de session dans une base de données (bien si vous voulez quelque chose comme, fonctionnalité "déconnectez-moi de tous les appareils")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // veuillez changer cela pour quelque chose d'autre
			Session::CONFIG_AUTO_COMMIT   => true, // faites le seulement si c'est nécessaire et/ou il est difficile de valider() votre session
												// de plus, vous pourriez faire Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # Pilote de base de données pour le dns PDO par exemple (mysql:host=...;dbname=...)
				'hôte'      => '127.0.0.1',         # Hôte de la base de données
				'nom_bd'   => 'ma_base_de_données_app',   # Nom de la base de données
				'table_bd'  => 'sessions',          # Table de base de données
				'util_bd'   => 'root',              # Nom d'utilisateur de la base de données
				'pass_bd'   => '',                  # Mot de passe de la base de données
				'persistent_conn'=> false,          # Évitez les frais généraux de création d'une nouvelle connexion à chaque fois qu'un script doit parler à une base de données, ce qui permet d'obtenir une application web plus rapide. TROUVEZ L'ENVERS VOUS-MÊME
			]
		]);
	}
);
```

## Documentation

Consultez le [Github Readme](https://github.com/Ghostff/Session) pour la documentation complète. Les options de configuration sont [bien documentées dans le fichier default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) lui-même. Le code est simple à comprendre si vous voulez parcourir ce package vous-même.