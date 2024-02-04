# Ghostff/Session

cadre de session PHP (non bloquant, flash, segment, chiffrement de session). Utilise PHP open_ssl pour le chiffrement/déchiffrement facultatif des données de session. Prise en charge de File, MySQL, Redis et Memcached.

## Installation

Installer avec le compositeur.

```bash
compositeur require ghostff/session
```

## Configuration de base

Vous n'êtes pas obligé de passer quoi que ce soit pour utiliser les paramètres par défaut avec votre session. Vous pouvez en lire plus sur les paramètres dans le [Github Readme](https://github.com/Ghostff/Session).

```php

utilisation du cadre de session Ghostff\Session\Session;

requiert 'vendor/autoload.php';

$app = Vol::app();

$app->registerer('session', Session::class);

// une chose à retenir est que vous devez valider votre session à chaque chargement de page
// or vous devrez exécuter auto_commit dans votre configuration.
```

## Exemple simple

Voici un exemple simple de comment vous pourriez utiliser ceci.

```php
Vol::route('POST /login', fonction() {
	$session = Vol::session();

	// faites votre logique de connexion ici
        // valider le mot de passe, etc.

	// si la connexion réussit
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// chaque fois que vous écrivez dans la session, vous devez la valider délibérément.
	$session->commit();
});

// Cette vérification pourrait être dans la logique de la page restreinte, ou enveloppée dans un middleware.
Vol::route('page-restreinte', fonction() {
	$session = Vol::session();

	if(!$session->get('is_logged_in')) {
		Vol::rediriger('/login');
	}

	// faites votre logique de page restreinte ici
});

// la version middleware
Vol::route('page-restreinte', fonction() {
	// logique de page régulière
})->addMiddleware(fonction() {
	$session = Vol::session();

	if(!$session->get('is_logged_in')) {
		Vol::rediriger('/login');
	}
});
```

## Exemple plus complexe

Voici un exemple plus complexe de comment vous pourriez utiliser ceci.

```php

utilisation du cadre de session Ghostff\Session\Session;

requiert 'vendor/autoload.php';

$app = Vol::app();

// définir un chemin personnalisé vers votre fichier de configuration de session et donnez-lui une chaîne aléatoire pour l'ID de session
$app->register('session', Session::class, [ 'chemin/vers/session_config.php', bin2hex(random_bytes(32)) ], fonction(Session $session) {
		// ou vous pouvez remplacer manuellement les options de configuration
		$session->updateConfiguration([
			// si vous voulez stocker vos données de session dans une base de données (utile si vous voulez quelque chose comme la fonctionnalité "déconnectez-moi de tous les appareils")
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // veuillez changer ceci pour en mettre un autre
			Session::CONFIG_AUTO_COMMIT   => true, // faites ceci uniquement si c'est nécessaire et/ou s'il est difficile de valider() votre session.
												// de plus, vous pourriez faire Vol::after('start', fonction() { Vol::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'pilote'    => 'mysql',             # Pilote de base de données pour le dsn PDO par exemple (mysql:host=...;dbname=...)
				'hôte'      => '127.0.0.1',         # Hôte de la base de données
				'n_db'   => 'ma_base_de_données_app',   # Nom de la base de données
				'table_db'  => 'sessions',          # Table de la base de données
				'utilisateur_db'   => 'root',              # Nom d'utilisateur de la base de données
				'pass_db'   => '',                  # Mot de passe de la base de données
				'persistent_conn'=> faux,          # Évitez les frais généraux de l'établissement d'une nouvelle connexion chaque fois qu'un script doit parler à une base de données, ce qui donne une application web plus rapide. TROUVEZ L'ENVERS VOUS-MÊME
			]
		]);
	}
);
```

## Documentation

Visitez le [Github Readme](https://github.com/Ghostff/Session) pour une documentation complète. Les options de configuration sont [bien documentées dans le fichier default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) lui-même. Le code est simple à comprendre si vous voulez parcourir ce package vous-même.