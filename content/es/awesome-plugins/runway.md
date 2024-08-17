# Pista

Pista es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight. Puede generar controladores, mostrar todas las rutas y más. Está basado en la excelente biblioteca [adhocore/php-cli](https://github.com/adhocore/php-cli).

Haz clic [aquí](https://github.com/flightphp/runway) para ver el código.

## Instalación

Instala con composer.

```bash
composer require flightphp/runway
```

## Configuración Básica

La primera vez que ejecutes Pista, te guiará a través de un proceso de configuración y creará un archivo de configuración `.runway.json` en la raíz de tu proyecto. Este archivo contendrá algunas configuraciones necesarias para que Pista funcione correctamente.

## Uso

Pista tiene varios comandos que puedes usar para gestionar tu aplicación Flight. Hay dos formas fáciles de usar Pista.

1. Si estás usando el proyecto esqueleto, puedes ejecutar `php runway [comando]` desde la raíz de tu proyecto.
1. Si estás usando Pista como un paquete instalado a través de composer, puedes ejecutar `vendor/bin/runway [comando]` desde la raíz de tu proyecto.

Para cualquier comando, puedes agregar la bandera `--help` para obtener más información sobre cómo usar el comando.

```bash
php runway routes --help
```

Aquí tienes algunos ejemplos:

### Generar un Controlador

Basado en la configuración en tu archivo `.runway.json`, la ubicación predeterminada generará un controlador para ti en el directorio `app/controllers/`.

```bash
php runway make:controller MiControlador
```

### Generar un Modelo de Active Record

Basado en la configuración en tu archivo `.runway.json`, la ubicación predeterminada generará un modelo de Active Record para ti en el directorio `app/records/`.

```bash
php runway make:record usuarios
```

Si por ejemplo tienes la tabla `usuarios` con el siguiente esquema: `id`, `nombre`, `correo`, `creado_en`, `actualizado_en`, se creará un archivo similar al siguiente en el archivo `app/records/UserRecord.php`:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Clase ActiveRecord para la tabla de usuarios.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $nombre
 * @property string $correo
 * @property string $creado_en
 * @property string $actualizado_en
 * // también puedes añadir relaciones aquí una vez las definas en el array $relations
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
     * @param mixed $conexionBaseDeDatos La conexión a la base de datos
     */
    public function __construct($conexionBaseDeDatos)
    {
        parent::__construct($conexionBaseDeDatos, 'usuarios');
    }
}
```

### Mostrar Todas las Rutas

Esto mostrará todas las rutas que están actualmente registradas en Flight.

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

## Personalización de Pista

Si estás creando un paquete para Flight, o deseas añadir tus propios comandos personalizados a tu proyecto, puedes hacerlo creando un directorio `src/commands/`, `flight/commands/`, `app/commands/` o `commands/` para tu proyecto/paquete.

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
     * @param array<string,mixed> $config JSON config de .runway-config.json
     */
    public function __construct(array $config)
    {
        parent::__construct('make:example', 'Crear un ejemplo para la documentación', $config);
        $this->argument('<gif-divertido>', 'El nombre del gif divertido');
    }

	/**
     * Ejecuta la función
     *
     * @return void
     */
    public function execute(string $controlador)
    {
        $io = $this->app()->io();

		$io->info('Creando ejemplo...');

		// Haz algo aquí

		$io->ok('¡Ejemplo creado!');
	}
}
```

Consulta la [Documentación de adhocore/php-cli](https://github.com/adhocore/php-cli) para obtener más información sobre cómo crear tus propios comandos personalizados en tu aplicación Flight.