# Pista

La pista es una aplicación CLI que te ayuda a gestionar tus aplicaciones Flight. Puede generar controladores, mostrar todas las rutas y más. Está basado en la excelente biblioteca [adhocore/php-cli](https://github.com/adhocore/php-cli).

## Instalación

Instala con composer.

```bash
composer require flightphp/runway
```

## Configuración Básica

La primera vez que ejecutas Runway, te guiará a través de un proceso de configuración y creará un archivo de configuración `.runway.json` en la raíz de tu proyecto. Este archivo contendrá algunas configuraciones necesarias para que Runway funcione correctamente.

## Uso

Runway tiene varios comandos que puedes usar para gestionar tu aplicación Flight. Hay dos formas sencillas de utilizar Runway.

1. Si estás utilizando el proyecto esqueleto, puedes ejecutar `php runway [comando]` desde la raíz de tu proyecto.
1. Si estás utilizando Runway como un paquete instalado a través de composer, puedes ejecutar `vendor/bin/runway [comando]` desde la raíz de tu proyecto.

Para cualquier comando, puedes pasar la bandera `--help` para obtener más información sobre cómo usar el comando.

```bash
php runway rutas --help
```

Aquí hay algunos ejemplos:

### Generar un Controlador

Según la configuración en tu archivo `.runway.json`, la ubicación predeterminada generará un controlador para ti en el directorio `app/controladores/`.

```bash
php runway make:controller MiControlador
```

### Generar un Modelo de Registro Activo

Según la configuración en tu archivo `.runway.json`, la ubicación predeterminada generará un controlador para ti en el directorio `app/registros/`.

```bash
php runway make:record usuarios
```

Si, por ejemplo, tienes la tabla `usuarios` con el siguiente esquema: `id`, `nombre`, `correo electrónico`, `creado en`, `actualizado en`, se creará un archivo similar al siguiente en el archivo `app/registros/RegistroUsuario.php`:

```php
<?php

declara(tipos estrictos = 1);

namespace app\registros;

/**
 * Clase de registro activo para la tabla de usuarios.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $created_at
 * @property string $updated_at
 * // también podrías agregar relaciones aquí una vez que las definas en la matriz $relations
 * @property RegistroCompañía $company Ejemplo de una relación
 */
clase RegistroUsuario extendida \flight\ActiveRecord
{
    /**
     * @var array $relations Establece las relaciones para el modelo
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protegido conjunto de matrices $relations = [];

    /**
     * Constructor
     * @param mixed $databaseConnection La conexión a la base de datos
     */
    public function __construct($databaseConnection)
    {
        padre::__construct($databaseConnection, 'usuarios');
    }
}
```

### Mostrar Todas las Rutas

Esto mostrará todas las rutas que están actualmente registradas con Flight.

```bash
php runway rutas
```

Si deseas ver solo rutas específicas, puedes pasar una bandera para filtrar las rutas.

```bash
# Mostrar solo rutas GET
php runway rutas --get

# Mostrar solo rutas POST
php runway rutas --post

# etc.
```

## Personalización de Runway

Si estás creando un paquete para Flight o deseas agregar tus propios comandos personalizados en tu proyecto, puedes hacerlo creando un directorio `src/comandos/`, `flight/comandos/`, `app/comandos/` o `comandos/` para tu proyecto/paquete.

Para crear un comando, simplemente extiende la clase `AbstractBaseComando` e implementa como mínimo un método `__construct` y un método `execute`.

```php
<?php

declara(tipos estrictos = 1);

namespace flight\comandos;

clase ComandoEjemplo extiende AbstractBaseCommand
{
	/**
     * Construct
     *
     * @param array<string,mixed> $config Configuración JSON de .runway-config.json
     */
    public function __construct(array $config)
    {
        padre::__construct('make:example', 'Crear un ejemplo para la documentación', $config);
        $this->argumento('<gif-divertido>', 'El nombre del gif divertido');
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

Consulta la [Documentación de adhocore/php-cli](https://github.com/adhocore/php-cli) para obtener más información sobre cómo incorporar tus propios comandos personalizados en tu aplicación Flight!