# Runway

Runway es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight. Puede generar controladores, mostrar todas las rutas y más. Se basa en la excelente biblioteca [adhocore/php-cli](https://github.com/adhocore/php-cli).

Haz clic [aquí](https://github.com/flightphp/runway) para ver el código.

## Instalación

Instala con composer.

```bash
composer require flightphp/runway
```

## Configuración Básica

La primera vez que ejecutes Runway, te guiará a través de un proceso de configuración y creará un archivo de configuración `.runway.json` en la raíz de tu proyecto. Este archivo contendrá algunas configuraciones necesarias para que Runway funcione correctamente.

## Uso

Runway tiene una serie de comandos que puedes usar para gestionar tu aplicación Flight. Hay dos formas fáciles de usar Runway.

1. Si estás usando el proyecto esqueleto, puedes ejecutar `php runway [command]` desde la raíz de tu proyecto.
1. Si estás usando Runway como un paquete instalado vía composer, puedes ejecutar `vendor/bin/runway [command]` desde la raíz de tu proyecto.

Para cualquier comando, puedes pasar la bandera `--help` para obtener más información sobre cómo usar el comando.

```bash
php runway routes --help
```

Aquí hay algunos ejemplos:

### Generar un Controlador

Basado en la configuración en tu archivo `.runway.json`, la ubicación predeterminada generará un controlador para ti en el directorio `app/controllers/`.

```bash
php runway make:controller MyController
```

### Generar un Modelo Active Record

Basado en la configuración en tu archivo `.runway.json`, la ubicación predeterminada generará un controlador para ti en el directorio `app/records/`.

```bash
php runway make:record users
```

Si, por ejemplo, tienes la tabla `users` con el siguiente esquema: `id`, `name`, `email`, `created_at`, `updated_at`, se creará un archivo similar al siguiente en el archivo `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Clase ActiveRecord para la tabla users.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // también podrías agregar relaciones aquí una vez que las definas en el array $relations
 * @property CompanyRecord $company Ejemplo de una relación
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Establece las relaciones para el modelo
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [];

    /**
     * Constructor
     * @param mixed $databaseConnection La conexión a la base de datos
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

### Mostrar Todas las Rutas

Esto mostrará todas las rutas que están actualmente registradas con Flight.

```bash
php runway routes
```

Si deseas ver solo rutas específicas, puedes pasar una bandera para filtrar las rutas.

```bash
# Mostrar solo rutas GET
php runway routes --get

# Mostrar solo rutas POST
php runway routes --post

# etc.
```

## Personalizando Runway

Si estás creando un paquete para Flight, o quieres agregar tus propios comandos personalizados a tu proyecto, puedes hacerlo creando un directorio `src/commands/`, `flight/commands/`, `app/commands/`, o `commands/` para tu proyecto/paquete. Si necesitas una personalización adicional, consulta la sección a continuación sobre Configuración.

Para crear un comando, simplemente extiende la clase `AbstractBaseCommand` e implementa, como mínimo, un método `__construct` y un método `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Constructor
     *
     * @param array<string,mixed> $config Configuración JSON de .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Crear un ejemplo para la documentación', $config);
        $this->argument('<funny-gif>', 'El nombre del gif divertido');
    }

	/**
     * Ejecuta la función
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

		$io->info('Creando ejemplo...');

		// Haz algo aquí

		$io->ok('¡Ejemplo creado!');
	}
}
```

Consulta la [Documentación de adhocore/php-cli](https://github.com/adhocore/php-cli) para obtener más información sobre cómo construir tus propios comandos personalizados en tu aplicación Flight!

### Configuración

Si necesitas personalizar la configuración para Runway, puedes crear un archivo `.runway-config.json` en la raíz de tu proyecto. A continuación se muestran algunas configuraciones adicionales que puedes establecer:

```js
{

	// Aquí es donde se encuentra el directorio de tu aplicación
	"app_root": "app/",

	// Este es el directorio donde se encuentra tu archivo index raíz
	"index_root": "public/",

	// Estas son las rutas a las raíces de otros proyectos
	"root_paths": [
		"/home/user/different-project",
		"/var/www/another-project"
	],

	// Las rutas base probablemente no necesiten configurarse, pero está aquí si lo quieres
	"base_paths": {
		"/includes/libs/vendor", // si tienes una ruta realmente única para tu directorio vendor o algo
	},

	// Las rutas finales son ubicaciones dentro de un proyecto para buscar los archivos de comandos
	"final_paths": {
		"src/diff-path/commands",
		"app/module/admin/commands",
	},

	// Si quieres agregar la ruta completa, adelante (absoluta o relativa a la raíz del proyecto)
	"paths": [
		"/home/user/different-project/src/diff-path/commands",
		"/var/www/another-project/app/module/admin/commands",
		"app/my-unique-commands"
	]
}
```