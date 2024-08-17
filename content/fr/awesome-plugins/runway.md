# Piste

**Runway** est une application CLI qui vous aide à gérer vos applications **Flight**. Il peut générer des contrôleurs, afficher toutes les routes, et bien plus encore. Il est basé sur l'excellente bibliothèque [adhocore/php-cli](https://github.com/adhocore/php-cli).

Cliquez [ici](https://github.com/flightphp/runway) pour voir le code.

## Installation

Installez avec **composer**.

```bash
composer require flightphp/runway
```

## Configuration de Base

La première fois que vous exécutez **Runway**, il vous guidera à travers un processus de configuration et créera un fichier de configuration `.runway.json` à la racine de votre projet. Ce fichier contiendra certaines configurations nécessaires pour que **Runway** fonctionne correctement.

## Utilisation

**Runway** dispose de plusieurs commandes que vous pouvez utiliser pour gérer votre application **Flight**. Il y a deux façons faciles d'utiliser **Runway**.

1. Si vous utilisez le projet de base, vous pouvez exécuter `php runway [commande]` depuis la racine de votre projet.
1. Si vous utilisez **Runway** comme un paquet installé via composer, vous pouvez exécuter `vendor/bin/runway [commande]` depuis la racine de votre projet.

Pour n'importe quelle commande, vous pouvez ajouter le drapeau `--help` pour obtenir plus d'informations sur comment utiliser la commande.

```bash
php runway routes --help
```

Voici quelques exemples :

### Générer un Contrôleur

Selon la configuration dans votre fichier `.runway.json`, l'emplacement par défaut générera un contrôleur pour vous dans le répertoire `app/controllers/`.

```bash
php runway make:controller MyController
```

### Générer un Modèle Active Record

Selon la configuration dans votre fichier `.runway.json`, l'emplacement par défaut générera un contrôleur pour vous dans le répertoire `app/records/`.

```bash
php runway make:record users
```

Par exemple, si vous avez la table `users` avec le schéma suivant : `id`, `name`, `email`, `created_at`, `updated_at`, un fichier similaire au suivant sera créé dans le fichier `app/records/UserRecord.php` :

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Class ActiveRecord pour la table des utilisateurs.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // vous pouvez également ajouter des relations ici une fois que vous les définissez dans le tableau $relations
 * @property CompanyRecord $company Exemple d'une relation
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

Cela affichera toutes les routes actuellement enregistrées avec **Flight**.

```bash
php runway routes
```

Si vous souhaitez uniquement visualiser des routes spécifiques, vous pouvez ajouter un drapeau pour filtrer les routes.

```bash
# Afficher uniquement les routes GET
php runway routes --get

# Afficher uniquement les routes POST
php runway routes --post

# etc.
```

## Personnaliser **Runway**

Si vous êtes en train de créer un paquet pour **Flight**, ou si vous voulez ajouter vos propres commandes personnalisées dans votre projet, vous pouvez le faire en créant un répertoire `src/commands/`, `flight/commands/`, `app/commands/`, ou `commands/` pour votre projet/paquet.

Pour créer une commande, vous étendez simplement la classe `AbstractBaseCommand` et implémentez au minimum une méthode `__construct` et une méthode `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Constructeur
     *
     * @param array<string,mixed> $config Config JSON de .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Crée un exemple pour la documentation', $config);
        $this->argument('<funny-gif>', 'Le nom du gif drôle');
    }

	/**
     * Exécute la fonction
     *
     * @return void
     */
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('Création de l\'exemple...');

		// Faites quelque chose ici

		$io->ok('Exemple créé !');
	}
}
```

Consultez la [Documentation de **adhocore/php-cli**](https://github.com/adhocore/php-cli) pour plus d'informations sur la façon de créer vos propres commandes personnalisées dans votre application **Flight** !