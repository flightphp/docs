# Runway

Runway es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight. Puede generar controladores, mostrar todas las rutas y más. Se basa en la excelente biblioteca [adhocore/php-cli](https://github.com/adhocore/php-cli).

Haz clic [aquí](https://github.com/flightphp/runway) para ver el código.

## Instalación

Instala con composer.

```bash
composer require flightphp/runway
```

## Configuración Básica

La primera vez que ejecutes Runway, intentará encontrar una configuración `runway` en `app/config/config.php` a través de la clave `'runway'`.

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

> **NOTA** - A partir de **v1.2.0**, `.runway-config.json` está obsoleto. Por favor, migra tu configuración a `app/config/config.php`. Puedes hacerlo fácilmente con el comando `php runway config:migrate`.

### Detección de la Raíz del Proyecto

Runway es lo suficientemente inteligente como para detectar la raíz de tu proyecto, incluso si lo ejecutas desde un subdirectorio. Busca indicadores como `composer.json`, `.git` o `app/config/config.php` para determinar dónde está la raíz del proyecto. Esto significa que puedes ejecutar comandos de Runway desde cualquier lugar en tu proyecto! 

## Uso

Runway tiene una serie de comandos que puedes usar para gestionar tu aplicación Flight. Hay dos formas fáciles de usar Runway.

1. Si estás usando el proyecto esqueleto, puedes ejecutar `php runway [command]` desde la raíz de tu proyecto.
1. Si estás usando Runway como un paquete instalado vía composer, puedes ejecutar `vendor/bin/runway [command]` desde la raíz de tu proyecto.

### Lista de Comandos

Puedes ver una lista de todos los comandos disponibles ejecutando el comando `php runway`.

```bash
php runway
```

### Ayuda de Comando

Para cualquier comando, puedes pasar la bandera `--help` para obtener más información sobre cómo usar el comando.

```bash
php runway routes --help
```

Aquí hay algunos ejemplos:

### Generar un Controlador

Basado en la configuración en `runway.app_root`, la ubicación generará un controlador para ti en el directorio `app/controllers/`.

```bash
php runway make:controller MyController
```

### Generar un Modelo Active Record

Primero asegúrate de haber instalado el plugin [Active Record](/awesome-plugins/active-record). Basado en la configuración en `runway.app_root`, la ubicación generará un registro para ti en el directorio `app/records/`.

```bash
php runway make:record users
```

Por ejemplo, si tienes la tabla `users` con el siguiente esquema: `id`, `name`, `email`, `created_at`, `updated_at`, se creará un archivo similar al siguiente en el archivo `app/records/UserRecord.php`:

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

## Agregar Comandos Personalizados a Runway

Si estás creando un paquete para Flight o quieres agregar tus propios comandos personalizados a tu proyecto, puedes hacerlo creando un directorio `src/commands/`, `flight/commands/`, `app/commands/` o `commands/` para tu proyecto/paquete. Si necesitas más personalización, consulta la sección a continuación sobre Configuración.

Para crear un comando, simplemente extiende la clase `AbstractBaseCommand` e implementa al menos un método `__construct` y un método `execute`.

```php
<?php

declare(strict_types=1);

namespace flight\commands;

class ExampleCommand extends AbstractBaseCommand
{
	/**
     * Constructor
     *
     * @param array<string,mixed> $config Configuración de app/config/config.php
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Crear un ejemplo para la documentación', $config);
        $this->argument('<funny-gif>', 'El nombre del gif gracioso');
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

## Gestión de Configuración

Dado que la configuración se ha movido a `app/config/config.php` a partir de `v1.2.0`, hay algunos comandos de ayuda para gestionar la configuración.

### Migrar Configuración Antigua

Si tienes un archivo `.runway-config.json` antiguo, puedes migrarlo fácilmente a `app/config/config.php` con el siguiente comando:

```bash
php runway config:migrate
```

### Establecer Valor de Configuración

Puedes establecer un valor de configuración usando el comando `config:set`. Esto es útil si quieres actualizar un valor de configuración sin abrir el archivo.

```bash
php runway config:set app_root "app/"
```

### Obtener Valor de Configuración

Puedes obtener un valor de configuración usando el comando `config:get`.

```bash
php runway config:get app_root
```

## Todas las Configuraciones de Runway

Si necesitas personalizar la configuración para Runway, puedes establecer estos valores en `app/config/config.php`. A continuación se muestran algunas configuraciones adicionales que puedes establecer:

```php
<?php
// app/config/config.php
return [
    // ... otros valores de configuración ...

    'runway' => [
        // Aquí es donde se encuentra tu directorio de aplicación
        'app_root' => 'app/',

        // Este es el directorio donde se encuentra tu archivo index raíz
        'index_root' => 'public/',

        // Estas son las rutas a las raíces de otros proyectos
        'root_paths' => [
            '/home/user/different-project',
            '/var/www/another-project'
        ],

        // Las rutas base probablemente no necesiten configurarse, pero está aquí si lo quieres
        'base_paths' => [
            '/includes/libs/vendor', // si tienes una ruta realmente única para tu directorio vendor o algo
        ],

        // Las rutas finales son ubicaciones dentro de un proyecto para buscar los archivos de comandos
        'final_paths' => [
            'src/diff-path/commands',
            'app/module/admin/commands',
        ],

        // Si quieres agregar la ruta completa, adelante (absoluta o relativa a la raíz del proyecto)
        'paths' => [
            '/home/user/different-project/src/diff-path/commands',
            '/var/www/another-project/app/module/admin/commands',
            'app/my-unique-commands'
        ]
    ]
];
```

### Acceso a la Configuración

Si necesitas acceder a los valores de configuración de manera efectiva, puedes acceder a ellos a través del método `__construct` o el método `app()`. También es importante notar que si tienes un archivo `app/config/services.php`, esos servicios también estarán disponibles para tu comando.

```php
public function execute()
{
    $io = $this->app()->io();
    
    // Acceder a la configuración
    $app_root = $this->config['runway']['app_root'];
    
    // Acceder a servicios como quizás una conexión a la base de datos
    $database = $this->config['database']
    
    // ...
}
```

## Envoltorios de Ayudante de IA

Runway tiene algunos envoltorios de ayudante que facilitan que la IA genere comandos. Puedes usar `addOption` y `addArgument` de una manera que se sienta similar a Symfony Console. Esto es útil si estás usando herramientas de IA para generar tus comandos.

```php
public function __construct(array $config)
{
    parent::__construct('make:example', 'Crear un ejemplo para la documentación', $config);
    
    // El argumento mode es nullable y por defecto es completamente opcional
    $this->addOption('name', 'El nombre del ejemplo', null);
}
```