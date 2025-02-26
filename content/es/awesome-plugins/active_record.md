# Flight Active Record 

Un registro activo es el mapeo de una entidad de base de datos a un objeto PHP. Hablando en términos sencillos, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila en esa tabla a una clase `User` y a un objeto `$user` en tu base de código. Consulta [ejemplo básico](#basic-example).

Haz clic [aquí](https://github.com/flightphp/active-record) para el repositorio en GitHub.

## Ejemplo Básico

Asumamos que tienes la siguiente tabla:

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
		// puedes configurarlo de esta manera
		parent::__construct($database_connection, 'users');
		// o de esta manera
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

¡Ahora observa cómo sucede la magia!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión de base de datos real

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// o mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// o mysqli con creación no basada en objeto
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// o $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// ¡no puedes usar $user->save() aquí o pensará que es una actualización!

echo $user->id; // 2
```

¡Y fue así de fácil agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo puedes sacarla?

```php
$user->find(1); // encuentra id = 1 en la base de datos y lo devuelve.
echo $user->name; // 'Bobby Tables'
```

¿Y qué pasa si quieres encontrar todos los usuarios?

```php
$users = $user->findAll();
```

¿Qué tal con una condición específica?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

¿Ves cuánto diversión es esto? ¡Instalémoslo y comencemos!

## Instalación

Simplemente instala con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto se puede usar como una biblioteca independiente o con el marco de PHP Flight. Totalmente a tu elección.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión de base de datos real

$User = new User($pdo_connection);
```

> ¿No quieres configurar siempre tu conexión a la base de datos en el constructor? Consulta [Administración de Conexiones a la Base de Datos](#database-connection-management) para otras ideas.

### Registrar como un método en Flight
Si estás usando el marco de PHP Flight, puedes registrar la clase ActiveRecord como un servicio, pero honestamente no tienes que hacerlo.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// luego puedes usarlo así en un controlador, una función, etc.

Flight::user()->find(1);
```

## Métodos `runway`

[runway](/awesome-plugins/runway) es una herramienta de línea de comandos para Flight que tiene un comando personalizado para esta biblioteca.

```bash
# Uso
php runway make:record database_table_name [class_name]

# Ejemplo
php runway make:record users
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
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $created_dt
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Establece las relaciones para el modelo
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

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

## Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y asígnalo al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave primaria con ese valor. Si no se pasa nada, simplemente encontrará el primer registro en la tabla.

Además, puedes pasarle otros métodos auxiliares para consultar tu tabla.

```php
// encontrar un registro con algunas condiciones de antemano
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

Devuelve `true` si el registro actual ha sido hidratado (recuperado de la base de datos).

```php
$user->find(1);
// si se encuentra un registro con datos...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Inserta el registro actual en la base de datos.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Claves Primarias Basadas en Texto

Si tienes una clave primaria basada en texto (como un UUID), puedes establecer el valor de la clave primaria antes de insertar de una de dos maneras.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // o $user->save();
```

o puedes hacer que la clave primaria se genere automáticamente para ti a través de eventos.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// también puedes establecer la primaryKey de esta manera en lugar del arreglo de arriba.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // o como necesites generar tus identificadores únicos
	}
}
```

Si no estableces la clave primaria antes de insertar, se establecerá en el `rowid` y la 
base de datos la generará para ti, pero no se persistirá porque ese campo puede no existir
en tu tabla. Por eso se recomienda utilizar el evento para manejar esto automáticamente.

#### `update(): boolean|ActiveRecord`

Actualiza el registro actual en la base de datos.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Inserta o actualiza el registro actual en la base de datos. Si el registro tiene un id, actualizará; de lo contrario, insertará.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Si tienes relaciones definidas en la clase, también se guardarán recursivamente esas relaciones si han sido definidas, instanciadas y tienen datos sucios para actualizar. (v0.4.0 y superior)

#### `delete(): boolean`

Elimina el registro actual de la base de datos.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

También puedes eliminar múltiples registros ejecutando una búsqueda previamente.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Los datos sucios se refieren a los datos que han sido cambiados en un registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sucio" en este momento.

$user->email = 'test@example.com'; // ahora el email se considera "sucio" ya que ha cambiado.
$user->update();
// ahora no hay datos que estén sucios porque ha sido actualizado y persistido en la base de datos

$user->password = password_hash('newpassword'); // ahora esto está sucio
$user->dirty(); // pasar nada limpiará todas las entradas sucias.
$user->update(); // nada se actualizará porque no se capturó nada como sucio.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('una contraseña diferente') ]);
$user->update(); // tanto el nombre como la contraseña se actualizan.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Este es un alias para el método `dirty()`. Es un poco más claro lo que estás haciendo.

```php
$user->copyFrom([ 'name' => 'algo', 'password' => password_hash('una contraseña diferente') ]);
$user->update(); // tanto el nombre como la contraseña se actualizan.
```

#### `isDirty(): boolean` (v0.4.0)

Devuelve `true` si el registro actual ha cambiado.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Reinicia el registro actual a su estado inicial. Esto es realmente bueno para usar en comportamientos de tipo bucle.
Si pasas `true`, también restablecerá los datos de consulta que se usaron para encontrar el objeto actual (comportamiento por defecto).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // comenzar con una pizarra limpia
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Después de ejecutar un `find()`, `findAll()`, `insert()`, `update()`, o `save()`, puedes obtener el SQL que se construyó y usarlo para fines de depuración.

## Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Puedes seleccionar solo algunas de las columnas en una tabla si lo deseas (es más eficiente en tablas realmente anchas con muchas columnas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

¡Técnicamente puedes elegir otra tabla también! ¿Por qué no?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Incluso puedes unirte a otra tabla en la base de datos.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Puedes establecer algunos argumentos where personalizados (no puedes establecer parámetros en esta declaración where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Seguridad** - Podrías sentirte tentado a hacer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ¡Por favor, NO HAGAS ESTO! Esto es susceptible a lo que se conoce como ataques de Inyección SQL. Hay muchos artículos en línea, por favor Google "ataques de inyección sql php" y encontrarás muchos artículos sobre este tema. La manera correcta de manejar esto con esta biblioteca es en lugar de este método `where()`, harías algo más como `$user->eq('id', $id)->eq('name', $name)->find();` Si absolutamente tienes que hacer esto, la biblioteca `PDO` tiene `$pdo->quote($var)` para escapar por ti. Solo después de usar `quote()` puedes usarlo en una declaración `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupa tus resultados por una condición particular.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Ordena la consulta devuelta de una manera determinada.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limita la cantidad de registros devueltos. Si se da un segundo entero, será un offset, límite igual que en SQL.

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

### Condiciones OR

Es posible envolver tus condiciones en una declaración OR. Esto se hace con el método `startWrap()` y `endWrap()` o llenando el tercer parámetro de la condición después del campo y el valor.

```php
// Método 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Esto evaluará a `id = 1 AND (name = 'demo' OR name = 'test')`

// Método 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Esto evaluará a `id = 1 OR name = 'demo'`
```

## Relaciones
Puedes establecer varios tipos de relaciones usando esta biblioteca. Puedes establecer relaciones uno->muchos y uno->uno entre tablas. Esto requiere un poco de configuración adicional en la clase de antemano.

Configurar el arreglo `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como desees. El nombre del ActiveRecord probablemente es bueno. Ej: user, contact, client
	'user' => [
		// requerido
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // este es el tipo de relación

		// requerido
		'Some_Class', // esta es la clase ActiveRecord "otra" a la que hará referencia

		// requerido
		// dependiendo del tipo de relación
		// self::HAS_ONE = la clave foránea que referencia la unión
		// self::HAS_MANY = la clave foránea que referencia la unión
		// self::BELONGS_TO = la clave local que referencia la unión
		'local_or_foreign_key',
		// solo FYI, esto también solo se une a la clave primaria del "otro" modelo

		// opcional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condiciones adicionales que deseas al unirte a la relación
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'back_reference_name' // esto es si deseas hacer una referencia inversa a esta relación de regreso a sí misma Ej: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'contacts');
	}
}
```

¡Ahora tenemos las referencias configuradas para que podamos usarlas muy fácilmente!

```php
$user = new User($pdo_connection);

// encontrar el usuario más reciente.
$user->notNull('id')->orderBy('id desc')->find();

// obtener contactos usando la relación:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// o podemos ir al otro lado.
$contact = new Contact();

// encontrar un contacto
$contact->find();

// obtener usuario usando la relación:
echo $contact->user->name; // este es el nombre de usuario
```

¿Bastante genial, ¿eh?

## Estableciendo Datos Personalizados
A veces, es posible que necesites adjuntar algo único a tu ActiveRecord, como un cálculo personalizado que podría ser más fácil de adjuntar al objeto que luego se pasaría, digamos, a una plantilla.

#### `setCustomData(string $field, mixed $value)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Y luego simplemente te refieres a él como una propiedad normal del objeto.

```php
echo $user->page_view_count;
```

## Eventos

Una característica más impresionante de esta biblioteca es sobre los eventos. Los eventos se activan en ciertos momentos en función de ciertos métodos que llamas. Son muy útiles para establecer datos automáticamente para ti.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Esto es realmente útil si necesitas establecer una conexión predeterminada o algo así.

```php
// index.php o bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // no olvides la referencia &
		// podrías hacer esto para establecer automáticamente la conexión
		$config['connection'] = Flight::db();
		// o esto
		$self->transformAndPersistConnection(Flight::db());
		
		// También puedes establecer el nombre de la tabla de esta manera.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Esto probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// siempre ejecutar id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este probablemente sea más útil si siempre necesitas ejecutar alguna lógica cada vez que se recupera este registro. ¿Necesitas desencriptar algo? ¿Necesitas ejecutar una consulta de conteo personalizada cada vez (no es eficiente, pero bueno)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// desencriptando algo
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// tal vez almacenando algo personalizado como una consulta???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Esto probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// siempre ejecutar id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

¡Similar a `afterFind()` pero puedes hacerlo con todos los registros!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// hacer algo genial como afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas establecer algunos valores predeterminados cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// establece algunos valores predeterminados sólidos
		if(!$self->created_date) {
			$self->created_date = gmdate('Y-m-d');
		}

		if(!$self->password) {
			$self->password = password_hash((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

Quizás tengas un caso de uso para cambiar datos después de que se inserte.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// hazlo como quieras
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// o lo que sea....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas establecer algunos valores predeterminados cada vez en una actualización.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeUpdate(self $self) {
		// establece algunos valores predeterminados sólidos
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Quizás tengas un caso de uso para cambiar datos después de que se actualice.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterUpdate(self $self) {
		// hazlo como quieras
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// o lo que sea....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Esto es útil si quieres que los eventos sucedan tanto cuando se inserten como cuando se actualicen. Te ahorraré la larga explicación, pero estoy seguro de que puedes adivinar lo que es.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

¡No estoy seguro de qué querrías hacer aquí, pero no juzgo! ¡Adelante!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Él fue un valiente soldado... :cry-face:';
	} 
}
```

## Administración de Conexiones a la Base de Datos

Cuando utilizas esta biblioteca, puedes establecer la conexión a la base de datos de varias maneras diferentes. Puedes establecer la conexión en el constructor, puedes configurarla a través de una variable de configuración `$config['connection']` o puedes establecerla a través de `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // por ejemplo
$user = new User($pdo_connection);
// o
$user = new User(null, [ 'connection' => $pdo_connection ]);
// o
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Si deseas evitar configurar siempre un `$database_connection` cada vez que llames a un registro activo, ¡hay formas de hacerlo!

```php
// index.php o bootstrap.php
// Establece esto como una clase registrada en Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// ¡Y ahora, no se requieren argumentos!
$user = new User();
```

> **Nota:** Si planeas hacer pruebas unitarias, hacerlo de esta manera puede presentar algunos desafíos para las pruebas unitarias, pero en general, como puedes inyectar tu 
conexión con `setDatabaseConnection()` o `$config['connection']` no es tan malo.

Si necesitas refrescar la conexión a la base de datos, por ejemplo, si estás ejecutando un script CLI de larga duración y necesitas refrescar la conexión cada cierto tiempo, puedes restablecer la conexión con `$your_record->setDatabaseConnection($pdo_connection)`.

## Contribuciones

Por favor, hazlo. :D

### Configuración

Cuando contribuyas, asegúrate de ejecutar `composer test-coverage` para mantener una cobertura de pruebas del 100% (esto no es una verdadera cobertura de pruebas unitarias, más como pruebas de integración).

También asegúrate de ejecutar `composer beautify` y `composer phpcs` para corregir cualquier error de sintaxis. 

## Licencia

MIT