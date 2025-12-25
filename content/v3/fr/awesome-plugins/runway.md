# Runway

Runway est une application CLI qui vous aide à gérer vos applications Flight. Elle peut générer des contrôleurs, afficher toutes les routes, et plus encore. Elle est basée sur la bibliothèque excellente [adhocore/php-cli](https://github.com/adhocore/php-cli).

Cliquez [ici](https://github.com/flightphp/runway) pour voir le code.

## Installation

Installez avec composer.

```bash
composer require flightphp/runway
```

## Configuration de Base

La première fois que vous exécutez Runway, il vous guidera à travers un processus de configuration et créera un fichier de configuration `.runway.json` à la racine de votre projet. Ce fichier contiendra certaines configurations nécessaires pour que Runway fonctionne correctement.

## Utilisation

Runway dispose d'un certain nombre de commandes que vous pouvez utiliser pour gérer votre application Flight. Il existe deux façons faciles d'utiliser Runway.

1. Si vous utilisez le projet squelette, vous pouvez exécuter `php runway [commande]` depuis la racine de votre projet.
1. Si vous utilisez Runway en tant que package installé via composer, vous pouvez exécuter `vendor/bin/runway [commande]` depuis la racine de votre projet.

Pour toute commande, vous pouvez passer l'option `--help` pour obtenir plus d'informations sur la façon d'utiliser la commande.

```bash
php runway routes --help
```

Voici quelques exemples :

### Générer un Contrôleur

En se basant sur la configuration dans votre fichier `.runway.json`, l'emplacement par défaut générera un contrôleur pour vous dans le répertoire `app/controllers/`.

```bash
php runway make:controller MyController
```

### Générer un Modèle Active Record

En se basant sur la configuration dans votre fichier `.runway.json`, l'emplacement par défaut générera un modèle pour vous dans le répertoire `app/records/`.

```bash
php runway make:record users
```

Si, par exemple, vous avez la table `users` avec le schéma suivant : `id`, `name`, `email`, `created_at`, `updated_at`, un fichier similaire à celui-ci sera créé dans le fichier `app/records/UserRecord.php` :

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe ActiveRecord pour la table users.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // vous pourriez également ajouter des relations ici une fois que vous les définissez dans le tableau $relations
 * @property CompanyRecord $company Exemple de relation
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Définir les relations pour le modèle
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Constructeur
     * @param mixed $databaseConnection La connexion à la base de données
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Afficher Toutes les Routes

Cela affichera toutes les routes qui sont actuellement enregistrées avec Flight.

```bash
php runway routes
```

Si vous souhaitez ne voir que des routes spécifiques, vous pouvez passer une option pour filtrer les routes.

```bash
# Afficher uniquement les routes GET
php runway routes --get

# Afficher uniquement les routes POST
php runway routes --post

# etc.
```

## Personnaliser Runway

Si vous créez un package pour Flight, ou si vous souhaitez ajouter vos propres commandes personnalisées à votre projet, vous pouvez le faire en créant un répertoire `src/commands/`, `flight/commands/`, `app/commands/`, ou `commands/` pour votre projet/package. Si vous avez besoin de personnalisations supplémentaires, consultez la section ci-dessous sur la Configuration.

Pour créer une commande, vous étendez simplement la classe `AbstractBaseCommand`, et implémentez au minimum une méthode `__construct` et une méthode `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Constructeur
     *
     * @param array<string,mixed> $config Configuration JSON de .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Créer un exemple pour la documentation', $config);
        $this->argument('<funny-gif>', 'Le nom du gif drôle');
    }

	/**
     * Exécute la fonction
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Création de l\'exemple...');

		// Faites quelque chose ici

		$io->ok('Exemple créé !');
	}
}
```

Consultez la [Documentation adhocore/php-cli](https://github.com/adhocore/php-cli) pour plus d'informations sur la façon de construire vos propres commandes personnalisées dans votre application Flight !

### Configuration

Si vous devez personnaliser la configuration pour Runway, vous pouvez créer un fichier `.runway-config.json` à la racine de votre projet. Voici quelques configurations supplémentaires que vous pouvez définir :

```js
{

	// C'est l'endroit où se trouve votre répertoire d'application
	"app_root": "app/",

	// C'est le répertoire où se trouve votre fichier index racine
	"index_root": "public/",

	// Ce sont les chemins vers les racines d'autres projets
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// Les chemins de base n'ont probablement pas besoin d'être configurés, mais c'est là si vous en voulez
	"base_paths": {
		"/includes/libs/vendor", // si vous avez un chemin vraiment unique pour votre répertoire vendor ou autre
	},

	// Les chemins finaux sont des emplacements dans un projet pour rechercher les fichiers de commande
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// Si vous voulez simplement ajouter le chemin complet, allez-y (absolu ou relatif à la racine du projet)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```