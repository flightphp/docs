# Pista

Pista es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight. Puede generar controladores, mostrar todas las rutas y más. Está basado en la excelente biblioteca [adhocore/php-cli](https://github.com/adhocore/php-cli).

## Instalación

Instala con composer.

```bash
composer require flightphp/runway
```

## Configuración Básica

La primera vez que ejecutes Pista, te guiará a través de un proceso de configuración y creará un archivo de configuración `.runway.json` en la raíz de tu proyecto. Este archivo contendrá algunas configuraciones necesarias para que Pista funcione correctamente.

## Uso

Pista tiene varios comandos que puedes utilizar para gestionar tu aplicación Flight. Hay dos formas fáciles de usar Pista.

1. Si estás usando el proyecto esqueleto, puedes ejecutar `php runway [comando]` desde la raíz de tu proyecto.
1. Si estás utilizando Pista como un paquete instalado a través de composer, puedes ejecutar `vendor/bin/runway [comando]` desde la raíz de tu proyecto.

Para cualquier comando, puedes agregar la bandera `--help` para obtener más información sobre cómo usar el comando.

```bash
php runway routes --help
```

Aquí tienes algunos ejemplos:

### Generar un Controlador

Basado en la configuración en tu archivo `.runway.json`, la ubicación predeterminada generará un controlador para ti en el directorio `app/controllers/`.

```bash
php runway make:controller MyController
```

### Generar un Modelo de Registro Activo

Basado en la configuración en tu archivo `.runway.json`, la ubicación predeterminada generará un controlador para ti en el directorio `app/records/`.

```bash
php runway make:record users
```

Si por ejemplo tienes la tabla `users` con el esquema siguiente: `id`, `name`, `email`, `created_at`, `updated_at`, se creará un archivo similar al siguiente en el archivo `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Clase ActiveRecord para la tabla de usuarios.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
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

Si deseas ver solo rutas específicas, puedes agregar una bandera para filtrar las rutas.

```bash
# Mostrar solo rutas GET
php runway routes --get

# Mostrar solo rutas POST
php runway routes --post

# etc.
```

## Personalizar Pista

Si estás creando un paquete para Flight, o deseas agregar tus propios comandos personalizados en tu proyecto, puedes hacerlo creando un directorio `src/commands/`, `flight/commands/`, `app/commands/` o `commands/` para tu proyecto/paquete.

Para crear un comando, simplemente extiende la clase `AbstractBaseCommand` e implementa como mínimo un método `__construct` y un método `execute`.

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
    public function execute(string $controller)
    {
        $io = $this->app()->io();

		$io->info('Creando ejemplo...');

		// Haz algo aquí

		$io->ok('¡Ejemplo creado!');
	}
}
```

Consulta la [Documentación de adhocore/php-cli](https://github.com/adhocore/php-cli) para obtener más información sobre cómo crear tus propios comandos personalizados en tu aplicación Flight!