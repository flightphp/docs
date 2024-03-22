# Flight Registro Activo

Un registro activo es el mapeo de una entidad de base de datos a un objeto PHP. En pocas palabras, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila en esa tabla a una clase `User` y a un objeto `$user` en tu código base. Consulta [ejemplo básico](#ejemplo-básico).

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
 * Es muy recomendable agregar las propiedades de la tabla como comentarios aquí
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($conexion_base_de_datos)
	{
		// puedes configurarlo de esta manera
		parent::__construct($conexion_base_de_datos, 'users');
		// o de esta manera
		parent::__construct($conexion_base_de_datos, null, [ 'table' => 'users']);
	}
}
```

¡Ahora observa cómo sucede la magia!

```php
// para sqlite
$conexion_base_de_datos = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión de base de datos real

// para mysql
$conexion_base_de_datos = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'usuario', 'contraseña');

// o mysqli
$conexion_base_de_datos = new mysqli('localhost', 'usuario', 'contraseña', 'test_db');
// o mysqli con creación no basada en objetos
$conexion_base_de_datos = mysqli_connect('localhost', 'usuario', 'contraseña', 'test_db');

$user = new User($conexion_base_de_datos);
$user->name = 'Bobby Tables';
$user->password = password_hash('una contraseña genial');
$user->insert();
// o $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('¡otra contraseña genial!');
$user->insert();
// ¡no puedes usar $user->save() aquí o pensará que es una actualización!

echo $user->id; // 2
```

¡Y fue tan fácil agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la extraes?

```php
$user->find(1); // busca el id = 1 en la base de datos y devuélvelo.
echo $user->name; // 'Bobby Tables'
```

¿Y si quieres encontrar todos los usuarios?

```php
$usuarios = $user->findAll();
```

¿Y con una condición determinada?

```php
$usuarios = $user->like('name', '%mamma%')->findAll();
```

¿Ves lo divertido que es esto? ¡Instalémoslo y empecemos!

## Instalación

Simplemente instálalo con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto se puede utilizar como una biblioteca independiente o con el Marco de PHP Flight. Completamente a tu elección.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$conexion_pdo = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión de base de datos real

$Usuario = new User($conexion_pdo);
```

### Marco PHP de Flight
Si estás utilizando el Marco PHP de Flight, puedes registrar la clase ActiveRecord como un servicio (pero honestamente no es necesario).

```php
Flight::register('usuario', 'User', [ $conexion_pdo ]);

// luego puedes usarlo de esta manera en un controlador, una función, etc.

Flight::usuario()->find(1);
```

## Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y asígnalo al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave primaria con ese valor. Si no se pasa nada, simplemente encontrará el primer registro en la tabla.

Además, puedes pasarle otros métodos auxiliares para consultar tu tabla.

```php
// encontrar un registro con algunas condiciones previas
$user->notNull('password')->orderBy('id DESC')->find();

// encontrar un registro con un id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encuentra todos los registros en la tabla que especifiques.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Devuelve `true` si el registro actual ha sido "hidratado" (obtenido de la base de datos).

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

#### `update(): boolean|ActiveRecord`

Actualiza el registro actual en la base de datos.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Inserta o actualiza el registro actual en la base de datos. Si el registro tiene un id, se actualizará, de lo contrario se insertará.

```php
$user = new User($conexion_pdo);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Si tienes relaciones definidas en la clase, guardará recursivamente esas relaciones también si se han definido, instanciado y tienen datos no guardados. (v0.4.0 y superior)

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

Datos "dirty" se refiere a los datos que han sido cambiados en un registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// no hay nada "dirty" hasta este punto.

$user->email = 'test@example.com'; // ahora el correo electrónico se considera "dirty" ya que ha cambiado.
$user->update();
// ahora no hay datos "dirty" porque se han actualizado y persistido en la base de datos

$user->password = password_hash()'newpassword'); // ahora esto es "dirty"
$user->dirty(); // pasar nada limpiará todas las entradas "dirty".
$user->update(); // nada se actualizará porque no se capturó nada como "dirty".

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // tanto el nombre como la contraseña se actualizan.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Este es un alias para el método `dirty()`. Es un poco más claro lo que estás haciendo.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
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

Restablece el registro actual a su estado inicial. Esto es realmente útil de usar en comportamientos de bucle.
Si pasas `true`, también restablecerá los datos de la consulta que se usaron para encontrar el objeto actual (comportamiento predeterminado).

```php
$usuarios = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_empresa = new UserCompany($conexion_pdo);

foreach($usuarios as $user) {
	$user_empresa->reset(); // comenzar con un lienzo limpio
	$user_empresa->user_id = $user->id;
	$user_empresa->company_id = $algún_id_empresa;
	$user_empresa->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Después de ejecutar un método `find()`, `findAll()`, `insert()`, `update()`, o `save()`, puedes obtener el SQL que se construyó y usarlo para fines de depuración.

## Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Puedes seleccionar solo algunos de los campos en una tabla si así lo deseas (es más eficiente en tablas realmente anchas con muchas columnas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

¡Técnicamente también puedes elegir otra tabla! ¡Por qué diablos no!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Incluso puedes unirte a otra tabla en la base de datos.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Puedes establecer algunos argumentos de `where` personalizados (no puedes establecer parámetros en esta declaración de `where`).

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Seguridad:** Podrías sentirte tentado a hacer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ¡POR FAVOR, NO HAGAS ESTO! ¡Esto es susceptible a lo que se conoce como ataques de inyección de SQL! Hay muchos artículos en línea, por favor, busca en Google "ataques de inyección de sql php" y encontrarás muchos artículos sobre este tema. La forma adecuada de manejar esto con esta biblioteca es, en lugar de este método `where()`, harías algo más como `$user->eq('id', $id)->eq('name', $name)->find();`. Si absolutamente tienes que hacer esto, la biblioteca `PDO` tiene `$pdo->quote($var)` para escaparlo por ti. Solo después de usar `quote()` puedes usarlo en una declaración `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupa tus resultados según una condición particular.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Ordena la consulta retornada de cierta manera.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limita la cantidad de registros retornados. Si se da un segundo entero, tendrá un desplazamiento, igual que en SQL.

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
Puedes configurar varios tipos de relaciones utilizando esta biblioteca. Puedes establecer relaciones uno a muchos y uno a uno entre tablas. Esto requiere un poco más de configuración en la clase de antemano.

Configurar la matriz `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como quieras. Probablemente el nombre de ActiveRecord sea bueno. Ej: usuario, contacto, cliente
	'usuario' => [
		// requerido
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // este es el tipo de relación

		// requerido
		'Alguna_Clase', // esta es la clase ActiveRecord "otra" a la que hará referencia esta

		// requerido
		// dependiendo del tipo de relación
		// self::HAS_ONE = la clave externa que hace referencia a la unión
		// self::HAS_MANY = la clave externa que hace referencia a la unión
		// self::BELONGS_TO = la clave local que hace referencia a la unión
		'clave_local_o_externa',
		// solo FYI, esto también se une a la clave primaria del modelo "otro"

		// opcional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condiciones adicionales que deseas al unir la relación
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'nombre_de_referencia_inversa' // esto es si quieres una referencia inversa de esta relación de nuevo a sí misma Ej: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contactos' => [ self::HAS_MANY, Contacto::class, 'id_usuario' ],
		'contacto' => [ self::HAS_ONE, Contacto::class, 'id_usuario' ],
	];

	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}
}

class Contacto extends ActiveRecord{
	protected array $relations = [
		'usuario' => [ self::BELONGS_TO, Usuario::class, 'id_usuario' ],
		'usuario_con_referencia_atras' => [ self::BELONGS_TO, Usuario::class, 'id_usuario', [], 'contacto' ],
	];
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'contactos');
	}
}
```

¡Ahora tenemos las referencias configuradas para que podamos usarlas muy fácilmente!

```php
$user = new Usuario($pdo_connection);

// encuentra el usuario más reciente
$user->notNull('id')->orderBy('id desc')->find();

// obtener contactos usando la relación:
foreach($user->contactos as $contacto) {
	echo $contacto->id;
}

// o podemos ir en la otra dirección.
$contacto = new Contacto();

// encuentra un contacto
$contacto->find();

// obtener usuario usando la relación:
echo $contacto->usuario->nombre; // este es el nombre del usuario
```

¡Bastante genial, ¿no?

## Estableciendo Datos Personalizados
A veces es posible que necesites adjuntar algo único a tu ActiveRecord, como un cálculo personalizado que podría ser más fácil simplemente adjuntarlo al objeto y luego pasarlo a, digamos, una plantilla.

#### `setCustomData(string $field, mixed $value)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$user->setCustomData('recuento_vistas_página', $recuento_vistas_página);
```

Y luego simplemente lo haces referencia como una propiedad normal del objeto.

```php
echo $usuario->recuento_vistas_página;
```

## Eventos

Otra característica súper impresionante de esta biblioteca son los eventos. Los eventos se activan en ciertos momentos en función de ciertos métodos que llamas. Son muy, muy útiles para configurar datos automáticamente.

####```markdown
# Vuelo Registro Activo

Un registro activo es el mapeo de una entidad de base de datos a un objeto PHP. En pocas palabras, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila en esa tabla a una clase `User` y a un objeto `$user` en tu código base. Consulta [ejemplo básico](#ejemplo-básico).

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
 * Es muy recomendable agregar las propiedades de la tabla como comentarios aquí
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($conexion_base_de_datos)
	{
		// puedes configurarlo de esta manera
		parent::__construct($conexion_base_de_datos, 'users');
		// o de esta manera
		parent::__construct($conexion_base_de_datos, null, [ 'table' => 'users']);
	}
}
```

¡Ahora observa cómo sucede la magia!

```php
// para sqlite
$conexion_base_de_datos = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión de base de datos real

// para mysql
$conexion_base_de_datos = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'usuario', 'contraseña');

// o mysqli
$conexion_base_de_datos = new mysqli('localhost', 'usuario', 'contraseña', 'test_db');
// o mysqli con creación no basada en objetos
$conexion_base_de_datos = mysqli_connect('localhost', 'usuario', 'contraseña', 'test_db');

$user = new User($conexion_base_de_datos);
$user->name = 'Bobby Tables';
$user->password = password_hash('una contraseña genial');
$user->insert();
// o $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('¡otra contraseña genial!');
$user->insert();
// ¡no puedes usar $user->save() aquí o pensará que es una actualización!

echo $user->id; // 2
```

¡Y fue tan fácil agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la extraes?

```php
$user->find(1); // busca el id = 1 en la base de datos y devuélvelo.
echo $user->name; // 'Bobby Tables'
```

¿Y si quieres encontrar todos los usuarios?

```php
$usuarios = $user->findAll();
```

¿Y con una condición determinada?

```php
$usuarios = $user->like('name', '%mamma%')->findAll();
```

¿Ves lo divertido que es esto? ¡Instalémoslo y empecemos!

## Instalación

Simplemente instálalo con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto se puede utilizar como una biblioteca independiente o con el Marco de PHP Flight. Completamente a tu elección.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$conexion_pdo = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión de base de datos real

$Usuario = new User($conexion_pdo);
```

### Marco PHP de Flight
Si estás utilizando el Marco PHP de Flight, puedes registrar la clase ActiveRecord como un servicio (pero honestamente no es necesario).

```php
Flight::register('usuario', 'User', [ $conexion_pdo ]);

// luego puedes usarlo de esta manera en un controlador, una función, etc.

Flight::usuario()->find(1);
```

## Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y asígnalo al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave primaria con ese valor. Si no se pasa nada, simplemente encontrará el primer registro en la tabla.

Además, puedes pasarle otros métodos auxiliares para consultar tu tabla.

```php
// encontrar un registro con algunas condiciones previas
$user->notNull('password')->orderBy('id DESC')->find();

// encontrar un registro con un id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encuentra todos los registros en la tabla que especifiques.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Devuelve `true` si el registro actual ha sido "hidratado" (obtenido de la base de datos).

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

#### `update(): boolean|ActiveRecord`

Actualiza el registro actual en la base de datos.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Inserta o actualiza el registro actual en la base de datos. Si el registro tiene un id, se actualizará, de lo contrario se insertará.

```php
$user = new User($conexion_pdo);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** If you have relationships defined in the class, it will recursively save those relations as well if they have been defined, instantiated and have dirty data to update. (v0.4.0 and above)

#### `delete(): boolean`

Deletes the current record from the database.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

You can also delete multiple records executing a search before hand.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Dirty data refers to the data that has been changed in a record.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nothing is "dirty" as of this point.

$user->email = 'test@example.com'; // now email is considered "dirty" since it's changed.
$user->update();
// now there is no data that is dirty because it's been updated and persisted in the database

$user->password = password_hash()'newpassword'); // now this is dirty
$user->dirty(); // passing nothing will clear all the dirty entries.
$user->update(); // nothing will update cause nothing was captured as dirty.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // both name and password are updated.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

This is an alias for the `dirty()` method. It's a little more clear what you are doing.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // both name and password are updated.
```

#### `isDirty(): boolean` (v0.4.0)

Returns `true` if the current record has been changed.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Resets the current record to it's initial state. This is really good to use in loop type behaviors.
If you pass `true` it will also reset the query data that was used to find the current object (default behavior).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($conexion_pdo);

foreach($users as $user) {
	$user_company->reset(); // start with a clean slate
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

After you run a `find()`, `findAll()`, `insert()`, `update()`, or `save()` method you can get the SQL that was built and use it for debugging purposes.

## SQL Query Methods
#### `select(string $field1 [, string $field2 ... ])`

You can select only a few of the columns in a table if you'd like (it is more performant on really wide tables with many columns)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

You can technically choose another table too! Why the heck not?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

You can even join to another table in the database.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

You can set some custom where arguments (you cannot set params in this where statement)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Security Note** - You might be tempted to do something like `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Please DO NOT DO THIS!!! This is susceptible to what is knows as SQL Injection attacks. There are lots of articles online, please Google "sql injection attacks php" and you'll find a lot of articles on this subject. The proper way to handle this with this library is instead of this `where()` method, you would do something more like `$user->eq('id', $id)->eq('name', $name)->find();` If you absolutely have to do this, the `PDO` library has `$pdo->quote($var)` to escape it for you. Only after you use `quote()` can you use it in a `where()` statement.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Group your results by a particular condition.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Sort the returned query a certain way.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limit the amount of records returned. If a second int is given, it will be offset, limit just like in SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE conditions
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Where `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Where `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Where `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Where `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Where `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Where `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Where `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Where `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Where `field LIKE $value` or `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Where `field IN($value)` or `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Where `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

## Relaciones
Puedes configurar varios tipos de relaciones utilizando esta biblioteca. Puedes establecer relaciones uno a muchos y uno a uno entre tablas. Esto requiere un poco más de configuración en la clase de antemano.

Configurar la matriz `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como quieras. Probablemente el nombre de ActiveRecord sea bueno. Ej: usuario, contacto, cliente
	'usuario' => [
		// requerido
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // este es el tipo de relación

		// requerido
		'Alguna_Clase', // esta es la clase ActiveRecord "otra" a la que hará referencia esta

		// requerido
		// dependiendo del tipo de relación
		// self::HAS_ONE = la clave externa que hace referencia a la unión
		// self::HAS_MANY = la clave externa que hace referencia a la unión
		// self::BELONGS_TO = la clave local que hace referencia a la unión
		'clave_local_o_externa',
		// solo FYI, esto también se une a la clave primaria del modelo "otro"

		// opcional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condiciones adicionales que deseas al unir la relación
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'nombre_de_referencia_inversa' // esto es si quieres una referencia inversa de esta relación de nuevo a sí misma Ej: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contactos' => [ self::HAS_MANY, Contacto::class, 'id_usuario' ],
		'contacto' => [ self::HAS_ONE, Contacto::class, 'id_usuario' ],
	];

	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}
}

class Contacto extends ActiveRecord{
	protected array $relations = [
		'usuario' => [ self::BELONGS_TO, Usuario::class, 'id_usuario' ],
		'usuario_con_referencia_atras' => [ self::BELONGS_TO, Usuario::class, 'id_usuario', [], 'contacto' ],
	];
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'contactos');
	}
}
```

¡Ahora tenemos las referencias configuradas para que podamos usarlas muy fácilmente!

```php
$user = new Usuario($conexion_pdo);

// encuentra el usuario más reciente
$user->notNull('id')->orderBy('id desc')->find();

// obtener contactos usando la relación:
foreach($user->contactos as $contacto) {
	echo $contacto->id;
}

// o podemos ir en la otra dirección.
$contacto = new Contacto();

// encuentra un contacto
$contacto->find();

// obtener usuario usando la relación:
echo $contacto->usuario->nombre; // este es el nombre del usuario
```

¡Bastante genial, ¿no?

## Estableciendo Datos Personalizados
A veces es posible que necesites adjuntar algo único a tu ActiveRecord, como un cálculo personalizado que podría ser más fácil simplemente adjuntarlo al objeto y luego pasarlo a, digamos, una plantilla.

#### `setCustomData(string $field, mixed $value)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$user->setCustomData('recuento_vistas_página', $recuento_vistas_página);
```

Y luego simplemente lo haces referencia como una propiedad normal del objeto.

```php
echo $usuario->recuento_vistas_página;
```

## Eventos

Otra característica súper impresionante de esta biblioteca son los eventos. Los eventos se activan en ciertos momentos en función de ciertos métodos que llamas. Son muy, muy útiles para configurar datos automáticamente.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Esto es realmente útil si necesitas establecer una conexión predeterminada o algo así.

```php
// index.php or bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // don't forget the & reference
		// you could do this to automatically set the connection
		$config['connection'] = Flight::db();
		// or this
		$self->transformAndPersistConnection(Flight::db());
		
		```markdown
## Contribución

Por favor, hazlo. :D

## Configuración

Cuando contribuyas, asegúrate de ejecutar `composer test-coverage` para mantener una cobertura del 100% de las pruebas (esto no es una cobertura de pruebas unitarias real, más bien pruebas de integración).

Además, asegúrate de ejecutar `composer beautify` y `composer phpcs` para corregir cualquier error de formato.

## Licencia

MIT
```