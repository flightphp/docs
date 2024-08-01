# ActiveRecord de Flight

Un active record es mapear una entidad de base de datos a un objeto PHP. En pocas palabras, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila de esa tabla a una clase `User` y un objeto `$user` en tu código. Mira el [ejemplo básico](#ejemplo-básico).

Haz clic [aquí](https://github.com/flightphp/active-record) para acceder al repositorio en GitHub.

## Ejemplo Básico

Supongamos que tienes la siguiente tabla:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY,
	name TEXT,
	password TEXT
);
```

Ahora puedes configurar una nueva clase para representar esta tabla:

```php
/**
 * Una clase ActiveRecord suele ser singular
 * 
 * Se recomienda encarecidamente agregar las propiedades de la tabla como comentarios aquí
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// puedes establecerlo de esta manera
		parent::__construct($database_connection, 'users');
		// o de esta manera
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

¡Ahora observa la magia suceder!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión de base de datos real

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nombre_de_usuario', 'contraseña');

// o mysqli
$database_connection = new mysqli('localhost', 'nombre_de_usuario', 'contraseña', 'test_db');
// o mysqli con creación no basada en objetos
$database_connection = mysqli_connect('localhost', 'nombre_de_usuario', 'contraseña', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('una contraseña genial');
$user->insert();
// o $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('¡otra contraseña genial!');
$user->insert();
// ¡no se puede usar $user->save() aquí o pensará que es una actualización!

echo $user->id; // 2
```

¡Y fue tan fácil agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la sacas?

```php
$user->find(1); // encuentra el id = 1 en la base de datos y devuélvelo.
echo $user->name; // 'Bobby Tables'
```

¿Y si quieres encontrar a todos los usuarios?

```php
$users = $user->findAll();
```

¿Y con una condición específica?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

¿Ves lo divertido que es esto? ¡Instalémoslo y empecemos!

## Instalación

Simplemente instala con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto se puede usar como una biblioteca independiente o con el Framework PHP Flight. Completamente depende de ti.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$conexion_pdo = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión de base de datos real

$Usuario = new User($conexion_pdo);
```

> ¿No quieres siempre establecer tu conexión de base de datos en el constructor? Consulta [Gestión de la conexión de base de datos](#gestión-de-la-conexión-de-base-de-datos) para más ideas!

### Registrar como un método en Flight
Si estás utilizando el Framework PHP Flight, puedes registrar la clase ActiveRecord como un servicio, pero honestamente no es necesario.

```php
Flight::register('usuario', 'User', [ $conexion_pdo ]);

// luego puedes usarlo así en un controlador, una función, etc.

Flight::usuario()->find(1);
```

## Métodos `runway`

[runway](https://docs.flightphp.com/awesome-plugins/runway) es una herramienta CLI para Flight que tiene un comando personalizado para esta biblioteca. 

```bash
# Uso
php runway make:record nombre_tabla_base_datos [nombre_clase]

# Ejemplo
php runway make:record usuarios
```

Esto creará una nueva clase en el directorio `app/records/` como `UserRecord.php` con el siguiente contenido:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Clase ActiveRecord para la tabla de usuarios.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 *
 * @property int $id
 * @property string $nombre_usuario
 * @property string $correo_electrónico
 * @property string $contraseña_hash
 * @property string $fecha_creación
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Establece las relaciones para el modelo
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'nombre_relación' => [ self::HAS_MANY, 'ClaseRelacionada', 'clave_foránea' ],
	];

    /**
     * Constructor
     * @param mixed $conexionBaseDatos La conexión a la base de datos
     */
    public function __construct($conexionBaseDatos)
    {
        parent::__construct($conexionBaseDatos, 'users');
    }
}
```

## Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y lo asigna al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave principal con ese valor. Si no se pasa nada, simplemente encontrará el primer registro en la tabla.

Además, puedes pasarle otros métodos auxiliares para consultar tu tabla.

```php
// encontrar un registro con algunas condiciones previas
$user->notNull('password')->orderBy('id DESC')->find();

// encontrar un registro por un id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encuentra todos los registros en la tabla que especifiques.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Devuelve `true` si el registro actual ha sido recuperado (extraído de la base de datos).

```php
$user->find(1);
// si se encuentra un registro con datos...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Inserta el registro actual en la base de datos.

```php
$user = new User($conexion_pdo);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Claves primarias basadas en texto

Si tienes una clave primaria basada en texto (como un UUID), puedes establecer el valor de la clave primaria antes de insertar de dos maneras.

```php
$user = new User($conexion_pdo, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // o $user->save();
```

o puedes generar automáticamente la clave primaria para ti a través de eventos.

```php
class User extends flight\ActiveRecord {
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'users', [ 'primaryKey' => 'uuid' ]);
		// también puedes establecer la clave primaria de esta manera en lugar del arreglo anterior.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // o como sea que necesites generar tus identificadores únicos
	}
}
```

Si no estableces la clave primaria antes de insertar, se establecerá en `rowid` y la base de datos la generará automáticamente por ti, pero no persistirá porque ese campo puede no existir en tu tabla. Por eso se recomienda usar el evento para manejar esto automáticamente.

#### `update(): boolean|ActiveRecord`

Actualiza el registro actual en la base de datos.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->$email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Inserta o actualiza el registro actual en la base de datos. Si el registro tiene un id, se actualizará; de lo contrario, se insertará.

```php
$user = new User($conexion_pdo);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Si tienes relaciones definidas en la clase, se guardarán recursivamente esas relaciones también si se han definido, instanciado y tienen datos sucios para actualizar. (v0.4.0 y superior)

#### `delete(): boolean`

Elimina el registro actual de la base de datos.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

También puedes eliminar varios registros ejecutando una búsqueda antes.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Datos sucios se refiere a los datos que han cambiado en un registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sucio" hasta este punto.

$user->email = 'test@example.com'; // ahora el email se considera "sucio" ya que ha cambiado.
$user->update();
// ahora no hay datos que estén "sucios" porque se han actualizado y persistido en la base de datos

$user->password = password_hash()'newpassword'); // ahora esto está sucio
$user->dirty(); // pasar nada limpiará todas las entradas sucias.
$user->update(); // nada se actualizará porque no se capturó nada como sucio.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // tanto el nombre como la contraseña se actualizarán.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Este es un alias del método `dirty()`. Es un poco más claro lo que estás haciendo.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // tanto el nombre como la contraseña se actualizarán.
```

#### `isDirty(): boolean` (v0.4.0)

Devuelve `true` si el registro actual ha cambiado.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Restablece el registro actual a su estado inicial. Esto es realmente bueno usarlo en comportamientos de bucle.
Si pasas `true`, también restablecerá los datos de la consulta que se utilizaron para encontrar el objeto actual (comportamiento predeterminado).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($conexion_pdo);

foreach($users as $user) {
	$user_company->reset(); // comienza de nuevo
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Después de ejecutar un método `find()`, `findAll()`, `insert()`, `update()` o `save()`, puedes obtener el SQL que se construyó y usarlo con fines de depuración.

## Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Puedes seleccionar solo algunos de los campos en una tabla si lo deseas (es más eficiente en tablas muy anchas con muchos campos)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Técnicamente puedes elegir otra tabla también. ¡¿Por qué no?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Incluso puedes unirte a otra tabla en la base de datos.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Puedes establecer algunas condiciones where personalizadas (no puedes establecer parámetros en esta sentencia where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Seguridad** - Es posible que te sientas tentado a hacer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ¡Por favor, ¡NO LO HAGAS! Esto es susceptible a lo que se conoce como ataques de inyección de SQL. Hay muchos artículos en línea, por favor busca en Google "ataques de inyección de sql php" y encontrarás muchos artículos sobre este tema. La forma adecuada de manejar esto con esta biblioteca es, en lugar de este método `where()`, harías algo más como `$user->eq('id', $id)->eq('name', $name)->find();` Si realmente necesitas hacer esto, la librería `PDO` tiene `$pdo->quote($var)` para escaparlo por ti. Solo después de usar `quote()` puedes usarlo en una instrucción `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupa tus resultados por una condición específica.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Ordena la consulta devuelta de una manera específica.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limita la cantidad de registros devueltos. Si se da un segundo entero, funcionará como desplazamiento, limit justo como en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## Condiciones WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Donde `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Donde `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Donde `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Donde `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Donde `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Donde `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Donde `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Donde `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Donde `field LIKE $value` o `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Donde `field IN($value)` o `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Donde `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

## Relaciones
Puedes establecer varios tipos de relaciones usando esta biblioteca. Puedes establecer relaciones uno a muchos y uno a uno entre tablas. Esto requiere una configuración adicional en la clase de antemano.

Configurar el array `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como desees. El nombre del ActiveRecord es probablemente bueno. Ej: usuario, contacto, cliente
	'usuario' => [
		// necesario
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // este es el tipo de relación

		// necesario
		'Otra_Clase', // esta es la clase de ActiveRecord "otra" a la que se hará referencia

		// necesario
		// dependiendo del tipo de relación
		// self::HAS_ONE = la clave foránea que referencia la unión
		// self::HAS_MANY = la clave foránea que referencia la unión
		// self::BELONGS_TO = la clave local que referencia la unión
		'clave_local_o_foránea',
		// solo para que sepas, esto tambiénse unirá a la clave principal del modelo "otro"

		// opcional
		[ 'eq' => [ 'id_cliente', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condiciones adicionales que deseas al unir la relación
		// $registro->eq('id_cliente', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'nombre_referencia_inversa' // esto es si quieres referir esta relación de vuelta a sí misma Ej: $usuario->contacto->usuario;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contactos' => [ self::HAS_MANY, Contacto::class, 'id_usuario' ],
		'contacto' => [ self::HAS_ONE, Contacto::class, 'id_usuario' ],
	];

	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}
}

class Contacto extends ActiveRecord{
	protected array $relations = [
		'usuario' => [ self::BELONGS_TO, Usuario::class, 'id_usuario' ],
		'usuario_con_referencia_inversa' => [ self::BELONGS_TO, Usuario::class, 'id_usuario', [], 'contacto' ],
	];
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'contactos');
	}
}
```

¡Ahora tenemos las referencias configuradas para que las usemos muy fácilmente!

```php
$usuario = new User($conexion_pdo);

// encuentra el usuario más reciente.
$usuario->notNull('id')->orderBy('id desc')->find();

// obtener contactos usando una relación:
foreach($usuario->contactos as $contacto) {
	echo $contacto->id;
}

// o podemos hacerlo al revés.
$contacto = new Contacto();

// encuentra un contacto
$contacto->find();

// obtener usuario usando una relación:
echo $contacto->usuario->nombre; // este es el nombre del usuario
```

¡Bastante interesante, ¿verdad?

## Estableciendo Datos Personalizados
A veces puede que necesites adjuntar algo único a tu ActiveRecord como un cálculo personalizado que podría ser más fácil simplemente adjuntar al objeto y pasar luego a, digamos, una plantilla.

#### `setCustomData(string $field, mixed $value)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$user->setCustomData('recuento_vistas_pagina', $recuento_vistas_pagina);
```

Y luego simplemente lo referencias como una propiedad normal del objeto.

```php
echo $usuario->recuento_vistas_pagina;
```

## Eventos

Otra característica súper increíble acerca de esta biblioteca son los eventos. Los eventos se activan en ciertos momentos basados en ciertos métodos que llamas. Son muy útiles para configurar datos automáticamente.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Esto es realmente útil si necesitas establecer una conexión predeterminada o algo así.

```php
// index.php o bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// Usuario.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // no olvides la referencia &
		// podrías hacer esto para establecer automáticamente la conexión
		$config['connection'] = Flight::db();
		// o esto
		$self->transformAndPersistConnection(Flight::db());
		
		// También puedes establecer el nombre de la tabla de esta manera.
		$config['tabla'] = 'usuarios';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}

	protected function beforeFind(self $self) {
		// siempre ejecutar id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Probablemente sea más útil si necesitas ejecutar alguna lógica cada vez que se recupera este registro. ¿Necesitas descifrar algo? ¿Necesitas ejecutar una consulta de recuento personalizada cada vez (no eficiente pero lo que sea)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}

	protected function afterFind(self $self) {
		// descifrando algo
		$self->secreto = tuFuncionDescifrar($self->secreto, $alguna_clave);

		// tal vez almacenar algo personalizado como una consulta???
		$self->setCustomData('recuento_vistas', $self->select('COUNT(*) count')->from('vistas_usuario')->eq('id_usuario', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}

	protected function beforeFindAll(self $self) {
		// siempre ejecutar id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar a `afterFind()` ¡pero puedes hacerlo a todos los registros en lugar de uno!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// haz algo genial como en afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas establecer algunos valores predeterminados cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}

	protected function beforeInsert(self $self) {
		// establecer algunos valores predeterminados
		if(!$self->fecha_creacion) {
			$self->fecha_creacion = gmdate('Y-m-d');
		}

		if(!$self->contraseña) {
			$self->contraseña = password_hash((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

¿Quizás tienes un caso de uso para cambiar datos después de insertarlos?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// ¡haz lo que quieras!
		Flight::cache()->set('id_mas_reciente_insertado', $self->id);
		// ¡o lo que sea....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas establecer algunos valores predeterminados cada vez que se actualizan.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}

	protected function beforeInsert(self $self) {
		// establecer algunos valores predeterminados
		if(!$self->fecha_actualizacion) {
			$self->fecha_actualizacion = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

¿Quizás tienes un caso de uso para cambiar datos después de actualizarlos?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// ¡haz lo que quieras!
		Flight::cache()->set('id_usuario_actualizado_recientemente', $self->id);
		// ¡o lo que sea....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Esto es útil si deseas que eventos sucedan tanto cuando se realizan inserciones como actualizaciones. Te ahorraré la larga explicación, pero seguro que puedes adivinar qué es.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}

	protected function beforeSave(self $self) {
		$self->ultima_actualizacion = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

¡No estoy seguro de qué querrás hacer aquí, pero ¡sin juicios aquí! ¡Adelante!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexionBaseDatos)
	{
		parent::__construct($conexionBaseDatos, 'usuarios');
	}

	protected function beforeDelete(self $self) {
		echo 'Fue un valiente soldado... :cry-face:';
	} 
}
```

## Gestión de la conexión de base de datos

Cuando estás usando esta biblioteca, puedes configurar la conexión de base de datos de varias maneras. Puedes establecer la conexión en el constructor, puedes establecerla a través de una variable de configuración `$config['connection']` o puedes establecerla a través de `setDatabaseConnection()` (v0.4.1). 

```php
$conexion_pdo = new PDO('sqlite:test.db'); // por ejemplo
$usuario = new User($conexion_pdo);
// o
$usuario = new User(null, [ 'connection' => $conexion_pdo ]);
// o
$usuario = new User();
$usuario->setDatabaseConnection($conexion_pdo);
```

Si necesitas actualizar la conexión de base de datos, por ejemplo si estás ejecutando un script CLI que tarda mucho tiempo y necesitas actualizar la conexión cada cierto tiempo, puedes restablecer la conexión con `$tu_registro->setDatabaseConnection($conexion_pdo)`.

## Contribuciones

Por favor, hazlo. :D

### Configuración

Cuando contribuyas, asegúrate de ejecutar `composer test-coverage` para mantener una cobertura de pruebas del 100% (esto no es una cobertura de pruebas unitarias reales, más bien como pruebas de integración).

También asegúrate de ejecutar `composer beautify` y `composer phpcs` para corregir cualquier error de linting.

## Licencia

MIT