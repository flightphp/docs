# Migraciones

Una migración para tu proyecto es el seguimiento de todos los cambios de base de datos involucrados en tu proyecto. [byjg/php-migration](https://github.com/byjg/php-migration) es una biblioteca central muy útil para comenzar.

## Instalación

### Biblioteca PHP

Si deseas usar solo la Biblioteca PHP en tu proyecto:

```bash
composer require "byjg/migration"
```

### Interfaz de Línea de Comando

La interfaz de línea de comando es independiente y no requiere que la instales con tu proyecto.

Puedes instalarlo globalmente y crear un enlace simbólico.

```bash
composer require "byjg/migration-cli"
```

Por favor visita [byjg/migration-cli](https://github.com/byjg/migration-cli) para obtener más información sobre Migration CLI.

## Bases de datos soportadas

| Base de datos      | Controlador                                                                          | Cadena de conexión                                        |
| ------------------| ----------------------------------------------------------------------------------- | -------------------------------------------------------- |
| Sqlite            | [pdo_sqlite](https://www.php.net/manual/en/ref.pdo-sqlite.php)                     | sqlite:///path/to/file                                   |
| MySql/MariaDb     | [pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)                       | mysql://username:password@hostname:port/database         |
| Postgres          | [pdo_pgsql](https://www.php.net/manual/en/ref.pdo-pgsql.php)                       | pgsql://username:password@hostname:port/database         |
| Sql Server        | [pdo_dblib, pdo_sysbase](https://www.php.net/manual/en/ref.pdo-dblib.php) Linux   | dblib://username:password@hostname:port/database         |
| Sql Server        | [pdo_sqlsrv](http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx) Windows      | sqlsrv://username:password@hostname:port/database        |

## ¿Cómo funciona?

La Migración de Base de Datos utiliza SQL PURO para gestionar la versión de la base de datos. Para que funcione, necesitas:

* Crear los Scripts SQL
* Gestionar usando la Línea de Comando o la API.

### Los Scripts SQL

Los scripts se dividen en tres conjuntos de scripts:

* El script BASE contiene TODOS los comandos SQL para crear una nueva base de datos;
* Los scripts UP contienen todos los comandos de migración SQL para "subir" la versión de la base de datos;
* Los scripts DOWN contienen todos los comandos de migración SQL para "bajar" o revertir la versión de la base de datos;

El directorio de scripts es:

```text
 <root dir>
     |
     +-- base.sql
     |
     +-- /migrations
              |
              +-- /up
                   |
                   +-- 00001.sql
                   +-- 00002.sql
              +-- /down
                   |
                   +-- 00000.sql
                   +-- 00001.sql
```

* "base.sql" es el script base
* La carpeta "up" contiene los scripts para migrar la versión hacia arriba.
   Por ejemplo: 00002.sql es el script para mover la base de datos de la versión '1' a '2'.
* La carpeta "down" contiene los scripts para migrar la versión hacia abajo.
   Por ejemplo: 00001.sql es el script para mover la base de datos de la versión '2' a '1'.
   La carpeta "down" es opcional.

### Entorno de Desarrollo Múltiple

Si trabajas con múltiples desarrolladores y múltiples ramas, es difícil determinar cuál es el siguiente número.

En ese caso, tienes el sufijo "-dev" después del número de versión.

Veamos el escenario:

* El desarrollador 1 crea una rama y la versión más reciente es, por ejemplo, 42.
* El desarrollador 2 crea una rama al mismo tiempo y tiene el mismo número de versión de base de datos.

En ambos casos, los desarrolladores crearán un archivo llamado 43-dev.sql. Ambos desarrolladores migrarán HACIA ARRIBA y HACIA ABAJO sin problemas y tu versión local será 43.

Pero el desarrollador 1 fusionó tus cambios y creó una versión final 43.sql (`git mv 43-dev.sql 43.sql`). Si el desarrollador 2 actualiza su rama local, tendrá un archivo 43.sql (del dev 1) y su archivo 43-dev.sql. Si intenta migrar HACIA ARRIBA o HACIA ABAJO, el script de migración fallará y le alertará que hay DOS versiones 43. En ese caso, el desarrollador 2 tendrá que actualizar su archivo a 44-dev.sql y continuar trabajando hasta fusionar tus cambios y generar una versión final.

## Usando la API PHP e integrándola en tus proyectos

El uso básico es

* Crear una conexión con un objeto ConnectionManagement. Para más información, consulta el componente "byjg/anydataset".
* Crear un objeto de Migración con esta conexión y la carpeta donde se encuentran los scripts SQL.
* Usar el comando apropiado para "resetear", "subir" o "bajar" los scripts de migración.

Veamos un ejemplo:

```php
<?php
// Crear la URI de conexión
// Ver más: https://github.com/byjg/anydataset#connection-based-on-uri
$connectionUri = new \ByJG\Util\Uri('mysql://migrateuser:migratepwd@localhost/migratedatabase');

// Registrar la base de datos o bases de datos que pueden manejar esa URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Crear la instancia de Migración
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Agregar una función de progreso de devolución de llamada para recibir información de la ejecución
$migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
    echo "$action, $currentVersion, ${fileInfo['description']}\n";
});

// Restaurar la base de datos usando el script "base.sql"
// y ejecutar TODOS los scripts existentes para subir la versión de la base de datos a la última versión
$migration->reset();

// Ejecutar TODOS los scripts existentes para subir o bajar la versión de la base de datos
// desde la versión actual hasta el número $version;
// Si el número de versión no está especificado, migrar hasta la última versión de la base de datos
$migration->update($version = null);
```

El objeto de Migración controla la versión de la base de datos.

### Creando un control de versión en tu proyecto

```php
<?php
// Registrar la base de datos o bases de datos que pueden manejar esa URI:
\ByJG\DbMigration\Migration::registerDatabase(\ByJG\DbMigration\Database\MySqlDatabase::class);

// Crear la instancia de Migración
$migration = new \ByJG\DbMigration\Migration($connectionUri, '.');

// Este comando creará la tabla de versiones en tu base de datos
$migration->createVersion();
```

### Obteniendo la versión actual

```php
<?php
$migration->getCurrentVersion();
```

### Agregar Callback para controlar el progreso

```php
<?php
$migration->addCallbackProgress(function ($command, $version, $fileInfo) {
    echo "Ejecutando Comando: $command en la versión $version - ${fileInfo['description']}, ${fileInfo['exists']}, ${fileInfo['file']}, ${fileInfo['checksum']}\n";
});
```

### Obteniendo la instancia del controlador de base de datos

```php
<?php
$migration->getDbDriver();
```

Para usarlo, por favor visita: [https://github.com/byjg/anydataset-db](https://github.com/byjg/anydataset-db)

### Evitando Migraciones Parciales (no disponible para MySQL)

Una migración parcial es cuando el script de migración se interrumpe en medio del proceso debido a un error o una interrupción manual.

La tabla de migración tendrá el estado `partial up` o `partial down` y debe ser corregido manualmente antes de poder migrar nuevamente.

Para evitar esta situación, puedes especificar que la migración se ejecute en un contexto transaccional. 
Si el script de migración falla, la transacción se revertirá y la tabla de migración se marcará como `complete` y 
la versión será la versión anterior inmediata antes del script que causó el error.

Para habilitar esta función, debes llamar al método `withTransactionEnabled` pasando `true` como parámetro:

```php
<?php
$migration->withTransactionEnabled(true);
```

**NOTA: Esta característica no está disponible para MySQL, ya que no admite comandos DDL dentro de una transacción.**
Si utilizas este método con MySQL, la Migración lo ignorará en silencio. 
Más info: [https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html](https://dev.mysql.com/doc/refman/8.0/en/cannot-roll-back.html)

## Consejos sobre cómo escribir migraciones SQL para Postgres

### Al crear triggers y funciones SQL

```sql
-- HACER
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Comprobar que empname y salary están dados
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname no puede ser nulo'; -- no importa si estos comentarios están vacíos o no
        END IF; --
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% no puede tener salary nulo', NEW.empname; --
        END IF; --

        -- ¿Quién trabaja para nosotros cuando tienen que pagarlo?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% no puede tener un salary negativo', NEW.empname; --
        END IF; --

        -- Recuerda quién cambió la nómina y cuándo
        NEW.last_date := current_timestamp; --
        NEW.last_user := current_user; --
        RETURN NEW; --
    END; --
$emp_stamp$ LANGUAGE plpgsql;


-- NO HACER
CREATE FUNCTION emp_stamp() RETURNS trigger AS $emp_stamp$
    BEGIN
        -- Comprobar que empname y salary están dados
        IF NEW.empname IS NULL THEN
            RAISE EXCEPTION 'empname no puede ser nulo';
        END IF;
        IF NEW.salary IS NULL THEN
            RAISE EXCEPTION '% no puede tener salary nulo', NEW.empname;
        END IF;

        -- ¿Quién trabaja para nosotros cuando tienen que pagarlo?
        IF NEW.salary < 0 THEN
            RAISE EXCEPTION '% no puede tener un salary negativo', NEW.empname;
        END IF;

        -- Recuerda quién cambió la nómina y cuándo
        NEW.last_date := current_timestamp;
        NEW.last_user := current_user;
        RETURN NEW;
    END;
$emp_stamp$ LANGUAGE plpgsql;
```

Dado que la capa de abstracción de base de datos `PDO` no puede ejecutar lotes de declaraciones SQL, al leer un archivo de migración, `byjg/migration` tiene que dividir todo el contenido del archivo SQL en los puntos y comas, y ejecutar las declaraciones una por una. Sin embargo, hay un tipo de declaración que puede tener múltiples puntos y comas en su interior: funciones.

Con el fin de poder analizar correctamente las funciones, `byjg/migration` 2.1.0 comenzó a dividir los archivos de migración en la secuencia de `punto y coma + EOL` en lugar de solo el punto y coma. De esta manera, si agregas un comentario vacío después de cada punto y coma interno de una definición de función, `byjg/migration` podrá analizarlo.

Desafortunadamente, si olvidas agregar alguno de estos comentarios, la biblioteca dividirá la declaración `CREATE FUNCTION` en múltiples partes y la migración fallará.

### Evitar el carácter de dos puntos (`:`)

```sql
-- HACER
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (CAST(booked_at AS DATE) <= check_in),
  check_in   DATE NOT NULL
);

-- NO HACER
CREATE TABLE bookings (
  booking_id UUID PRIMARY KEY,
  booked_at  TIMESTAMPTZ NOT NULL CHECK (booked_at::DATE <= check_in),
  check_in   DATE NOT NULL
);
```

Dado que `PDO` utiliza el carácter de dos puntos para prefijar parámetros nombrados en declaraciones preparadas, su uso puede causar problemas en otros contextos.

Por ejemplo, las declaraciones de PostgreSQL pueden usar `::` para convertir valores entre tipos. Por otro lado, `PDO` leerá esto como un parámetro nombrado inválido en un contexto inválido y fallará cuando intente ejecutarlo.

La única forma de solucionar esta inconsistencia es evitar los dos puntos por completo (en este caso, PostgreSQL también tiene una sintaxis alternativa: `CAST(value AS type)`).

### Usar un editor SQL

Finalmente, escribir migraciones SQL manuales puede ser agotador, pero es significativamente más fácil si usas un editor capaz de entender la sintaxis SQL, proporcionando autocompletado, introspección de tu esquema de base de datos actual y/o autoformateo de tu código.

## Manejo de diferentes migraciones dentro de un esquema

Si necesitas crear diferentes scripts de migración y versiones dentro del mismo esquema, es posible, pero es demasiado arriesgado y **no** lo recomiendo en absoluto.

Para hacerlo, necesitas crear diferentes "tablas de migración" pasando el parámetro al constructor.

```php
<?php
$migration = new \ByJG\DbMigration\Migration("db:/uri", "/path", true, "NEW_MIGRATION_TABLE_NAME");
```

Por razones de seguridad, esta función no está disponible en la línea de comandos, pero puedes usar la variable de entorno `MIGRATION_VERSION` para almacenar el nombre.

Recomendamos encarecidamente no utilizar esta función. La recomendación es una migración para un esquema.

## Ejecución de pruebas unitarias

Las pruebas unitarias básicas se pueden ejecutar con:

```bash
vendor/bin/phpunit
```

## Ejecución de pruebas de base de datos

Ejecutar pruebas de integración requiere que las bases de datos estén activas y en funcionamiento. Proporcionamos un `docker-compose.yml` básico que puedes usar para iniciar las bases de datos para pruebas.

### Ejecutando las bases de datos

```bash
docker-compose up -d postgres mysql mssql
```

### Ejecutar las pruebas

```bash
vendor/bin/phpunit
vendor/bin/phpunit tests/SqliteDatabase*
vendor/bin/phpunit tests/MysqlDatabase*
vendor/bin/phpunit tests/PostgresDatabase*
vendor/bin/phpunit tests/SqlServerDblibDatabase*
vendor/bin/phpunit tests/SqlServerSqlsrvDatabase*
```

Opcionalmente puedes establecer el host y la contraseña utilizados por las pruebas unitarias

```bash
export MYSQL_TEST_HOST=localhost     # predeterminado a localhost
export MYSQL_PASSWORD=newpassword    # usa '.' si quieres tener una contraseña nula
export PSQL_TEST_HOST=localhost      # predeterminado a localhost
export PSQL_PASSWORD=newpassword     # usa '.' si quieres tener una contraseña nula
export MSSQL_TEST_HOST=localhost     # predeterminado a localhost
export MSSQL_PASSWORD=Pa55word
export SQLITE_TEST_HOST=/tmp/test.db      # predeterminado a /tmp/test.db
```