# Runway

Runway est une application CLI qui vous aide à gérer vos applications Flight. Elle peut générer des contrôleurs, afficher toutes les routes, et plus encore. Elle est basée sur la bibliothèque excellente [adhocore/php-cli](https://github.com/adhocore/php-cli).

Cliquez [ici](https://github.com/flightphp/runway) pour voir le code.

## Installation

Installez avec composer.

```bash
composer require flightphp/runway
```

## Configuration de Base

La première fois que vous exécutez Runway, il essaiera de trouver une configuration `runway` dans `app/config/config.php` via la clé `'runway'`.

```php
<?php
// app/config/config.php
return [
    'runway' => [
        'app_root' => 'app/',
		'public_root' => 'public/',
    ],
];
```

> **NOTE** - À partir de **v1.2.0**, `.runway-config.json` est déprécié. Veuillez migrer votre configuration vers `app/config/config.php`. Vous pouvez le faire facilement avec la commande `php runway config:migrate`.

### Détection de la Racine du Projet

Runway est suffisamment intelligent pour détecter la racine de votre projet, même si vous l'exécutez depuis un sous-répertoire. Il recherche des indicateurs comme `composer.json`, `.git`, ou `app/config/config.php` pour déterminer où se trouve la racine du projet. Cela signifie que vous pouvez exécuter les commandes Runway depuis n'importe où dans votre projet ! 

## Utilisation

Runway dispose d'un certain nombre de commandes que vous pouvez utiliser pour gérer votre application Flight. Il existe deux façons faciles d'utiliser Runway.

1. Si vous utilisez le projet squelette, vous pouvez exécuter `php runway [command]` depuis la racine de votre projet.
1. Si vous utilisez Runway comme un package installé via composer, vous pouvez exécuter `vendor/bin/runway [command]` depuis la racine de votre projet.

### Liste des Commandes

Vous pouvez voir une liste de toutes les commandes disponibles en exécutant la commande `php runway`.

```bash
php runway
```

### Aide sur les Commandes

Pour n'importe quelle commande, vous pouvez passer l'option `--help` pour obtenir plus d'informations sur la façon d'utiliser la commande.

```bash
php runway routes --help
```

Voici quelques exemples :

### Générer un Contrôleur

Basé sur la configuration dans `runway.app_root`, l'emplacement générera un contrôleur pour vous dans le répertoire `app/controllers/`.

```bash
php runway make:controller MyController
```

### Générer un Modèle Active Record

Assurez-vous d'abord d'avoir installé le plugin [Active Record](/awesome-plugins/active-record). Basé sur la configuration dans `runway.app_root`, l'emplacement générera un enregistrement pour vous dans le répertoire `app/records/`.

```bash
php runway make:record users
```

Par exemple, si vous avez la table `users` avec le schéma suivant : `id`, `name`, `email`, `created_at`, `updated_at`, un fichier similaire à celui-ci sera créé dans le fichier `app/records/UserRecord.php` :

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

Si vous souhaitez voir uniquement des routes spécifiques, vous pouvez passer un drapeau pour filtrer les routes.

```bash
# Afficher uniquement les routes GET
php runway routes --get

# Afficher uniquement les routes POST
php runway routes --post

# etc.
```

## Ajouter des Commandes Personnalisées à Runway

Si vous créez un package pour Flight, ou si vous souhaitez ajouter vos propres commandes personnalisées à votre projet, vous pouvez le faire en créant un répertoire `src/commands/`, `flight/commands/`, `app/commands/`, ou `commands/` pour votre projet/package. Si vous avez besoin de personnalisation supplémentaire, voir la section ci-dessous sur la Configuration.

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
     * @param array<string,mixed> $config Configuration de app/config/config.php
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

## Gestion de la Configuration

Puisque la configuration a été déplacée vers `app/config/config.php` à partir de `v1.2.0`, il existe quelques commandes d'aide pour gérer la configuration.

### Migrer l'Ancienne Configuration

Si vous avez un ancien fichier `.runway-config.json`, vous pouvez facilement le migrer vers `app/config/config.php` avec la commande suivante :

```bash
php runway config:migrate
```

### Définir une Valeur de Configuration

Vous pouvez définir une valeur de configuration en utilisant la commande `config:set`. Cela est utile si vous souhaitez mettre à jour une valeur de configuration sans ouvrir le fichier.

```bash
php runway config:set app_root "app/"
```

### Obtenir une Valeur de Configuration

Vous pouvez obtenir une valeur de configuration en utilisant la commande `config:get`.

```bash
php runway config:get app_root
```

## Toutes les Configurations Runway

Si vous devez personnaliser la configuration pour Runway, vous pouvez définir ces valeurs dans `app/config/config.php`. Voici quelques configurations supplémentaires que vous pouvez définir :

```php
<?php
// app/config/config.php
return [
    // ... autres valeurs de configuration ...

    'runway' => [
        // C'est là que se trouve votre répertoire d'application
        'app_root' => 'app/',

        // C'est le répertoire où se trouve votre fichier index racine
        'index_root' => 'public/',

        // Ce sont les chemins vers les racines d'autres projets
        'root_paths' => [
            '/home/user/different-project',
            '/var/www/another-project'
        ],

        // Les chemins de base n'ont probablement pas besoin d'être configurés, mais ils sont là si vous en voulez
        'base_paths' => [
            '/includes/libs/vendor', // si vous avez un chemin vraiment unique pour votre répertoire vendor ou autre
        ],

        // Les chemins finaux sont des emplacements dans un projet pour rechercher les fichiers de commandes
        'final_paths' => [
            'src/diff-path/commands',
            'app/module/admin/commands',
        ],

        // Si vous voulez simplement ajouter le chemin complet, allez-y (absolu ou relatif à la racine du projet)
        'paths' => [
            '/home/user/different-project/src/diff-path/commands',
            '/var/www/another-project/app/module/admin/commands',
            'app/my-unique-commands'
        ]
    ]
];
```

### Accéder à la Configuration

Si vous devez accéder efficacement aux valeurs de configuration, vous pouvez les accéder via la méthode `__construct` ou la méthode `app()`. Il est également important de noter que si vous avez un fichier `app/config/services.php`, ces services seront également disponibles pour votre commande.

```php
public function execute()
{
    $io = $this->app()->io();
    
    // Accéder à la configuration
    $app_root = $this->config['runway']['app_root'];
    
    // Accéder aux services comme peut-être une connexion à la base de données
    $database = $this->config['database']
    
    // ...
}
```

## Wrappers d'Aide IA

Runway dispose de quelques wrappers d'aide qui facilitent la génération de commandes par l'IA. Vous pouvez utiliser `addOption` et `addArgument` d'une manière qui ressemble à Symfony Console. Cela est utile si vous utilisez des outils IA pour générer vos commandes.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Créer un exemple pour la documentation', $config);
    
    // L'argument mode est nullable et par défaut complètement optionnel
    $this->addOption('name', 'Le nom de l\'exemple', null);
}
```