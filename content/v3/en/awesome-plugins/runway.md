# Runway

Runway is a CLI application that helps you manage your Flight applications. It can generate controllers, display all routes, and more. It is based on the excellent [adhocore/php-cli](https://github.com/adhocore/php-cli) library.

Click [here](https://github.com/flightphp/runway) to view the code.

## Installation

Install with composer.

```bash
composer require flightphp/runway
```

## Basic Configuration

The first time you run Runway, it will run you through a setup process and create a `.runway.json` configuration file in the root of your project. This file will contain some necessary configurations for Runway to work properly. 

## Usage

Runway has a number of commands that you can use to manage your Flight application. There are two easy ways to use Runway.

1. If you are using the skeleton project, you can run `php runway [command]` from the root of your project.
1. If you are using Runway as a package installed via composer, you can run `vendor/bin/runway [command]` from the root of your project.

For any command, you can pass in the `--help` flag to get more information on how to use the command.

```bash
php runway routes --help
```

Here are a few examples:

### Generate a Controller

Based on the configuration in your `.runway.json` file, the default location will generate a controller for you in the `app/controllers/` directory.

```bash
php runway make:controller MyController
```

### Generate an Active Record Model

Based on the configuration in your `.runway.json` file, the default location will generate a controller for you in the `app/records/` directory.

```bash
php runway make:record users
```

If for instance you have the `users` table with the following schema: `id`, `name`, `email`, `created_at`, `updated_at`, a file similar to the following will be created in the `app/records/UserRecord.php` file:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord class for the users table.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // you could also add relationships here once you define them in the $relations array
 * @property CompanyRecord $company Example of a relationship
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Set the relationships for the model
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Constructor
     * @param mixed $databaseConnection The connection to the database
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Display All Routes

This will display all of the routes that are currently registered with Flight.

```bash
php runway routes
```

If you would like to only view specific routes, you can pass in a flag to filter the routes.

```bash
# Display only GET routes
php runway routes --get

# Display only POST routes
php runway routes --post

# etc.
```

## Customizing Runway

If you are either creating a package for Flight, or want to add your own custom commands into your project, you can do so by creating a `src/commands/`, `flight/commands/`, `app/commands/`, or `commands/` directory for your project/package. If you need further customization, see the section below on Configuration.

To create a command, you simple extend the `AbstractBaseCommand` class, and implement at a minimum a `__construct` method and an `execute` method.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Construct
     *
     * @param array<string,mixed> $config JSON config from .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Create an example for the documentation', $config);
        $this->argument('<funny-gif>', 'The name of the funny gif');
    }

	/**
     * Executes the function
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Creating example...');

		// Do something here

		$io->ok('Example created!');
	}
}
```

See the [adhocore/php-cli Documentation](https://github.com/adhocore/php-cli) for more information on how to build your own custom commands into your Flight application!

### Configuration

If you need to customize the configuration for Runway, you can create a `.runway-config.json` file in the root of your project. Below are some additional configurations that you can set:

```js
{

	// This is where your application directory is located
	"app_root": "app/",

	// This is the directory where your root index file is located
	"index_root": "public/",

	// These are the paths to the roots of other projects
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// Base paths most likely don't need to be configured, but it's here if you want it
	"base_paths": {
		"/includes/libs/vendor", // if you have a really unique path for your vendor directory or something
	},

	// Final paths are locations within a project to search for the command files
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// If you want to just add the full path, go right ahead (absolute or relative to project root)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```
