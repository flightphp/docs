# Ghostff/Session

Gestionnaire de sessions PHP (non-bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement/déchiffrement optionnel des données de session. Prend en charge File, MySQL, Redis et Memcached.

Cliquez [ici](https://github.com/Ghostff/Session) pour voir le code.

## Installation

Installez avec composer.

```bash
composer require ghostff/session
```

## Configuration de base

Vous n'êtes pas obligé de passer quoi que ce soit pour utiliser les paramètres par défaut avec votre session. Vous pouvez en lire plus sur les paramètres dans le [Github Readme](https://github.com/Ghostff/Session).

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// une chose à retenir est que vous devez valider votre session à chaque chargement de page
// ou vous devrez exécuter auto_commit dans votre configuration.
```

## Exemple simple

Voici un exemple simple de la façon dont vous pourriez utiliser cela.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// effectuez votre logique de connexion ici
	// valider le mot de passe, etc.

	// si la connexion est réussie
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// à tout moment que vous écrivez dans la session, vous devez la valider délibérément.
	$session->commit();
});

// Cette vérification pourrait se faire dans la logique de la page restreinte, ou être enveloppée dans un middleware.
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// effectuez votre logique de page restreinte ici
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

Voici un exemple plus complexe de la façon dont vous pourriez utiliser cela.

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// définissez un chemin personnalisé vers votre fichier de configuration de session en tant que premier argument
// ou donnez-lui le tableau personnalisé
$app->register('session', Session::class, [ 
	[
		// si vous voulez stocker vos données de session dans une base de données (utile pour quelque chose comme, "me déconnecter de tous les appareils" fonctionnalité)
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // veuillez changer cela pour quelque chose d'autre
		Session::CONFIG_AUTO_COMMIT   => true, // ne le faites que si c'est nécessaire et/ou si c'est difficile de faire commit() sur votre session.
												// de plus, vous pourriez faire Flight::after('start', function() { Flight::session()->commit(); });
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # Pilote de base de données pour PDO dns ex.(mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # Hôte de la base de données
			'db_name'   => 'my_app_database',   # Nom de la base de données
			'db_table'  => 'sessions',          # Table de la base de données
			'db_user'   => 'root',              # Nom d'utilisateur de la base de données
			'db_pass'   => '',                  # Mot de passe de la base de données
			'persistent_conn'=> false,          # Éviter le surcoût d'établir une nouvelle connexion à chaque fois qu'un script doit communiquer avec une base de données, ce qui accélère l'application web. TROUVEZ LE DESSUS VOUS-MÊME
		]
	] 
]);
```

## Aide ! Mes données de session ne persistent pas !

Vous configurez vos données de session et elles ne persistent pas entre les requêtes ? Vous avez peut-être oublié de valider vos données de session. Vous pouvez le faire en appelant `$session->commit()` après avoir défini vos données de session.

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// effectuez votre logique de connexion ici
	// valider le mot de passe, etc.

	// si la connexion est réussie
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// à tout moment que vous écrivez dans la session, vous devez la valider délibérément.
	$session->commit();
});
```

L'autre moyen d'y remédier est, lorsque vous configurez votre service de session, de définir `auto_commit` sur `true` dans votre configuration. Cela validera automatiquement vos données de session après chaque requête.

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

Visitez le [Github Readme](https://github.com/Ghostff/Session) pour la documentation complète. Les options de configuration sont [bien documentées dans le fichier default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) lui-même. Le code est simple à comprendre si vous vouliez parcourir ce package vous-même.