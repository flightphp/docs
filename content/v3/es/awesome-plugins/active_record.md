# Flight Active Record

Un active record consiste en mapear una entidad de la base de datos a un objeto PHP. Dicho de forma sencilla, si tienes una tabla `users` en tu base de datos, puedes "traducir" una fila de esa tabla a una clase `User` y a un objeto `$user` en tu código. Ver [ejemplo básico](#basic-example).

Haz clic [aquí](https://github.com/flightphp/active-record) para ir al repositorio en GitHub.

## Ejemplo Básico

Supongamos que tienes la siguiente tabla:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Ahora puedes crear una nueva clase para representar esta tabla:

```php
/**
 * Una clase ActiveRecord normalmente está en singular
 * 
 * Es muy recomendable añadir las propiedades de la tabla como comentarios aquí
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
		// o de esta otra
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

¡Ahora observa la magia!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión real a la base de datos

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// o mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// o mysqli con creación basada en funciones
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
// aquí no puedes usar $user->save() o pensará que es una actualización

echo $user->id; // 2
```

¡Y así de fácil fue añadir un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la recuperas?

```php
$user->find(1); // busca id = 1 en la base de datos y lo devuelve.
echo $user->name; // 'Bobby Tables'
```

¿Y si quieres encontrar a todos los usuarios?

```php
$users = $user->findAll();
```

¿Qué tal con una condición específica?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

¿Ves lo divertido que es? ¡Instalémoslo y comencemos!

## Instalación

Simplemente instala con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto se puede usar como una librería independiente o con el Flight PHP Framework. Tú decides.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión real a la base de datos

$User = new User($pdo_connection);
```

> ¿No quieres configurar siempre tu conexión a la base de datos en el constructor? ¡Consulta [Gestión de la conexión a la base de datos](#database-connection-management) para otras ideas!

### Registrar como método en Flight
Si estás usando el Flight PHP Framework, puedes registrar la clase ActiveRecord como un servicio, aunque sinceramente no es necesario.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// luego puedes usarla así en un controlador, una función, etc.

Flight::user()->find(1);
```

## Métodos de `runway`

[runway](/awesome-plugins/runway) es una herramienta CLI para Flight que tiene un comando personalizado para esta librería.

```bash
# Uso
php runway make:record database_table_name [class_name]

# Ejemplo
php runway make:record users
```

Esto creará una nueva clase en el directorio `app/records/` llamada `UserRecord.php` con el siguiente contenido:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Clase ActiveRecord para la tabla users.
 * @link https://docs.flightphp.com/en/v3/awesome-plugins/active-record
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
     * @var array $relations Define las relaciones del modelo
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
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

Encuentra un registro y lo asigna al objeto actual. Si pasas algún tipo de `$id`, realizará una búsqueda en la clave primaria con ese valor. Si no se pasa nada, simplemente encontrará el primer registro de la tabla.

Además, puedes pasarle otros métodos auxiliares para consultar tu tabla.

```php
// encuentra un registro con algunas condiciones antes
$user->notNull('password')->orderBy('id DESC')->find();

// encuentra un registro por un id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encuentra todos los registros en la tabla que especifiques.

```php
$user->findAll();
```

#### `first(): ActiveRecord` (v0.8.0)

Encuentra el primer registro que coincida con tus condiciones. Si no has establecido un orden, ordena por la clave primaria de forma ascendente. Si nada coincide, obtienes el registro sin hidratar, así que verifica `isHydrated()` si no estás seguro de que haya datos.

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

Igual que `first()` pero ordena por la clave primaria de forma descendente. Útil para consultas como "dame el más reciente".

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

Cuenta las filas que coinciden con tus condiciones actuales. Si tienes un `groupBy()` en la consulta, `count()` lo ignora a propósito. Un solo escalar de recuento no puede representar una fila por grupo.

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

Devuelve `true` si algún registro coincide con tus condiciones. Internamente ejecuta un `SELECT 1 ... LIMIT 1` económico.

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

Devuelve un array plano de valores de una sola columna en lugar de hidratar un montón de objetos. Combínalo con `distinct()` para obtener valores únicos.

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

Un atajo para `pluck()` sobre la clave primaria.

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
```

#### `isHydrated(): boolean` (v0.4.0)

Devuelve `true` si el registro actual ha sido hidratado (obtenido de la base de datos).

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

##### Claves primarias basadas en texto

Si tienes una clave primaria basada en texto (como un UUID), puedes establecer el valor de la clave primaria antes de insertar de dos maneras.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // o $user->save();
```

o puedes hacer que la clave primaria se genere automáticamente mediante eventos.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// también puedes establecer primaryKey de esta manera en lugar del array anterior.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // o como necesites generar tus ids únicos
	}
}
```

Si no estableces la clave primaria antes de insertar, se asignará el `rowid` y la base de datos la generará por ti, pero no se persistirá porque ese campo puede no existir en tu tabla. Por eso se recomienda usar el evento para manejar esto automáticamente.

#### `update(): boolean|ActiveRecord`

Actualiza el registro actual en la base de datos.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

Actualiza una sola columna en un registro cargado y lo guarda. Es un atajo para `$user->dirty([ 'name' => $value ])->update()`. Necesitas un registro cargado para esto.

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

Inserta o actualiza el registro actual en la base de datos. Si el registro tiene un id, actualizará; de lo contrario, insertará.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Si tienes relaciones definidas en la clase, también guardará recursivamente esas relaciones si han sido definidas, instanciadas y tienen datos sucios para actualizar. (v0.4.0 y superior)

#### `delete(): boolean`

Elimina el registro actual de la base de datos.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

También puedes eliminar varios registros ejecutando una búsqueda previamente.

```php
$user->like('name', 'Bob%')->delete();
```

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

Actualiza todos los registros que coincidan con tus condiciones en una sola sentencia. No se hidrata ningún registro ni se disparan eventos, por eso es rápido. Devuelve el número de filas afectadas.

Se niega a ejecutarse sin condiciones WHERE a menos que pases `true` como segundo argumento. Tu yo del futuro te lo agradecerá.

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// sí, realmente quieres actualizar todas las filas de la tabla
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

Elimina todos los registros que coincidan con tus condiciones en una sola sentencia. Igual que `updateAll()`: sin hidratación, sin eventos, y requiere condiciones WHERE a menos que pases `true`. Devuelve el número de filas eliminadas. ¡Úsalo con precaución!

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array $dirty = []): ActiveRecord`

Los datos sucios se refieren a los datos que han sido cambiados en un registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sucio" hasta este punto.

$user->email = 'test@example.com'; // ahora email se considera "sucio" porque ha cambiado.
$user->update();
// ahora no hay datos sucios porque se ha actualizado y persistido en la base de datos

$user->password = password_hash('newpassword'); // ahora esto está sucio
$user->dirty(); // pasar nada limpia todas las entradas sucias.
$user->update(); // nada se actualizará porque no se capturó nada como sucio.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // tanto name como password se actualizan.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Este es un alias del método `dirty()`. Es un poco más claro lo que estás haciendo.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // tanto name como password se actualizan.
```

#### `isDirty(): boolean` (v0.4.0)

Devuelve `true` si el registro actual ha sido modificado.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Restablece el registro actual a su estado inicial. Esto es muy útil para usar en comportamientos de tipo bucle.
Si pasas `true`, también restablecerá los datos de consulta que se usaron para encontrar el objeto actual (comportamiento predeterminado).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // empieza con una pizarra limpia
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Después de ejecutar un método `find()`, `findAll()`, `insert()`, `update()` o `save()`, puedes obtener el SQL que se construyó y usarlo para fines de depuración.

## Transacciones

¿Necesitas ejecutar varias escrituras que deben tener éxito juntas? Envuélvelas en `transaction()` (v0.8.0). Pásale un callable y el registro llega como argumento. Si el callable lanza una excepción, todo se revierte y la excepción se vuelve a lanzar. De lo contrario, confirma y devuelve lo que tu callable haya devuelto.

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// aquí se confirma si no se lanzó nada
});
```

Las transacciones anidadas no son compatibles (no hay savepoints), así que mantenlas simples.

## Métodos de consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Puedes seleccionar solo algunas columnas de una tabla si lo deseas (es más eficiente en tablas realmente anchas con muchas columnas).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Técnicamente también puedes elegir otra tabla. ¡Por qué no!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Incluso puedes hacer join con otra tabla de la base de datos.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Puedes establecer algunas condiciones where personalizadas (no puedes establecer parámetros en esta sentencia where).

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de seguridad** - Puede que tengas la tentación de hacer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ¡POR FAVOR, NO HAGAS ESTO! Esto es susceptible a lo que se conoce como ataques de inyección SQL. Hay muchos artículos en línea; por favor busca "sql injection attacks php" y encontrarás muchos artículos sobre este tema. La forma correcta de manejar esto con esta librería es, en lugar de usar el método `where()`, hacer algo más como `$user->eq('id', $id)->eq('name', $name)->find();`. Si absolutamente tienes que hacerlo, la librería `PDO` tiene `$pdo->quote($var)` para escaparlo por ti. Solo después de usar `quote()` puedes usarlo en una sentencia `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupa tus resultados por una condición particular.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Ordena la consulta devuelta de cierta manera.

```php
$user->orderBy('name DESC')->find();
```

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()` y `orderBy()` aceptan fragmentos SQL crudos, lo cual está bien cuando escribes literalmente `'name DESC'`. Si el nombre de la columna proviene de datos del usuario (por ejemplo, una cabecera de tabla ordenable), usa `orderByColumn()` en su lugar. Solo se permiten nombres de columna simples y rutas `table.column`, y la dirección debe ser `ASC` o `DESC`, así que no hay nada que inyectar.

```php
// $sortColumn viene de la petición
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limita la cantidad de registros devueltos. Si se da un segundo entero, será offset, limit tal como en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

Añade `DISTINCT` a tu siguiente consulta. Funciona en la selección normal y con `pluck()`. `count()` lo ignora, ya que poner `DISTINCT` en una sola fila agregada no hace nada.

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
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

Es posible envolver tus condiciones en una sentencia OR. Esto se hace con los métodos `startWrap()` y `endWrap()` o completando el tercer parámetro de la condición después del campo y el valor.

```php
// Método 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Esto se evaluará como `id = 1 AND (name = 'demo' OR name = 'test')`

// Método 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Esto se evaluará como `id = 1 OR name = 'demo'`
```

## Scopes

Los scopes (v0.8.0) son cadenas de consulta reutilizables, definidas como métodos de instancia normales en tu clase que devuelven `$this`. Una vez que has escrito uno, se encadena como cualquier otro método de consulta.

```php
class User extends flight\ActiveRecord {

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	public function active(): self
	{
		return $this->eq('status', 'active');
	}

	public function recent(int $days = 7): self
	{
		return $this->ge('created_at', date('Y-m-d', strtotime("-{$days} days")));
	}
}

// y ahora tus consultas se leen como frases
(new User($pdo_connection))->active()->recent(30)->findAll();
```

También puedes llamar a un scope por nombre con `scope()`, lo cual es útil cuando el nombre del scope proviene de otro lugar de tu código. Lanza una `BadMethodCallException` si el método no existe.

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## Relaciones

Puedes establecer varios tipos de relaciones con esta librería. Puedes establecer relaciones uno->muchos y uno->uno entre tablas. Esto requiere una configuración adicional en la clase.

Configurar el array `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como quieras. El nombre del ActiveRecord probablemente sea bueno. Ej: user, contact, client
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
		// solo para que lo sepas, esto también solo se une a la clave primaria del "otro" modelo

		// opcional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' => 5 ], // condiciones adicionales que quieres al unir la relación
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'back_reference_name' // esto es si quieres hacer una referencia inversa de esta relación hacia sí misma. Ej: $user->contact->user;
	]
];
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

¡Ahora tenemos las referencias configuradas y podemos usarlas muy fácilmente!

```php
$user = new User($pdo_connection);

// encuentra el usuario más reciente.
$user->notNull('id')->orderBy('id desc')->find();

// obtén los contactos usando la relación:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// o podemos ir al revés.
$contact = new Contact();

// encuentra un contacto
$contact->find();

// obtén el usuario usando la relación:
echo $contact->user->name; // este es el nombre del usuario
```

¿Bastante genial, verdad?

### Carga ansiosa

#### Descripción general
La carga ansiosa resuelve el problema de las consultas N+1 al cargar las relaciones con antelación. En lugar de ejecutar una consulta separada para las relaciones de cada registro, la carga ansiosa obtiene todos los datos relacionados en una sola consulta adicional por relación.

> **Nota:** La carga ansiosa solo está disponible en v0.7.0 y superiores.

#### Uso básico
Usa el método `with()` para especificar qué relaciones cargar de forma ansiosa:
```php
// Carga los usuarios con sus contactos en 2 consultas en lugar de N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // ¡No hay consulta adicional!
    }
}
```

#### Múltiples relaciones
Carga varias relaciones a la vez:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Tipos de relación

##### HAS_MANY
```php
// Carga ansiosamente todos los contactos para cada usuario
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts ya está cargado como un array
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// Carga ansiosamente un contacto para cada usuario
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact ya está cargado como un objeto
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Carga ansiosamente los usuarios padres para todos los contactos
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user ya está cargado
    echo $c->user->name;
}
```
##### Con find()
La carga ansiosa funciona tanto con `findAll()` como con `find()`:

```php
$user = $user->with('contacts')->find(1);
// El usuario y todos sus contactos cargados en 2 consultas
```

#### Beneficios de rendimiento
Sin carga ansiosa (problema N+1):
```php
$users = $user->findAll(); // 1 consulta
foreach ($users as $u) {
    $contacts = $u->contacts; // N consultas (¡una por usuario!)
}
// Total: 1 + N consultas
```

Con carga ansiosa:

```php
$users = $user->with('contacts')->findAll(); // 2 consultas en total
foreach ($users as $u) {
    $contacts = $u->contacts; // ¡0 consultas adicionales!
}
// Total: 2 consultas (1 para usuarios + 1 para todos los contactos)
```
Para 10 usuarios, esto reduce las consultas de 11 a 2: ¡una reducción del 82 %!

#### Notas importantes
- La carga ansiosa es completamente opcional; la carga perezosa sigue funcionando como antes.
- Las relaciones ya cargadas se omiten automáticamente.
- Las referencias inversas funcionan con la carga ansiosa.
- Los callbacks de relación se respetan durante la carga ansiosa.

#### Limitaciones
- La carga ansiosa anidada (por ejemplo, `with(['contacts.addresses'])`) no es compatible actualmente.
- Las restricciones de carga ansiosa mediante closures no son compatibles en esta versión.

## Configuración de datos personalizados

A veces puedes necesitar adjuntar algo único a tu ActiveRecord, como un cálculo personalizado que podría ser más fácil adjuntar al objeto y luego pasar, por ejemplo, a una plantilla.

#### `setCustomData(string $field, mixed $value)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Y luego simplemente lo referencias como una propiedad normal del objeto.

```php
echo $user->page_view_count;
```

## Marcas de tiempo

Si tu tabla tiene columnas `created_at` y `updated_at`, puedes hacer que la librería las rellene por ti (v0.8.0). Configura `protected bool $timestamps = true;` en tu clase y establecerá `created_at` y `updated_at` al insertar, y `updated_at` al actualizar. El formato es `Y-m-d H:i:s`. Si estableces alguna de las columnas tú mismo, la librería deja tu valor en paz.

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

Tu tabla necesita tener esas columnas, o las inserciones y actualizaciones fallarán.

## Eventos

Una característica súper increíble de esta librería son los eventos. Los eventos se activan en ciertos momentos según los métodos que llames. Son muy, muy útiles para configurar datos automáticamente.

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
		// podrías hacer esto para establecer la conexión automáticamente
		$config['connection'] = Flight::db();
		// o esto
		$self->transformAndPersistConnection(Flight::db());
		
		// También puedes establecer el nombre de la tabla de esta manera.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Esto probablemente solo es útil si necesitas una manipulación de la consulta cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// siempre ejecuta id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este probablemente sea más útil si siempre necesitas ejecutar alguna lógica cada vez que se obtiene este registro. ¿Necesitas descifrar algo? ¿Necesitas ejecutar una consulta de recuento personalizada cada vez (no es eficiente, pero da igual)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// descifrando algo
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// quizás almacenar algo personalizado como una consulta???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Esto probablemente solo es útil si necesitas una manipulación de la consulta cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// siempre ejecuta id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar a `afterFind()`, ¡pero puedes hacerlo con todos los registros a la vez!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
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
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// establece algunos valores predeterminados sensatos
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

¿Quizás tienes un caso para cambiar datos después de que se inserten?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// tú haces lo tuyo
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

	protected function beforeInsert(self $self) {
		// establece algunos valores predeterminados sensatos
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

¿Quizás tienes un caso para cambiar datos después de que se actualicen?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// tú haces lo tuyo
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// o lo que sea....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Esto es útil si quieres que los eventos ocurran tanto cuando se inserta como cuando se actualiza. Te ahorraré la explicación larga, pero estoy seguro de que puedes adivinar qué es.

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

No estoy seguro de qué querrías hacer aquí, pero no te juzgamos. ¡Adelante!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Fue un soldado valiente... :cry-face:';
	} 
}
```

## Gestión de la conexión a la base de datos

Cuando usas esta librería, puedes configurar la conexión a la base de datos de varias maneras. Puedes establecer la conexión en el constructor, puedes configurarla a través de una variable de configuración `$config['connection']` o puedes configurarla con `setDatabaseConnection()` (v0.4.1).

```php
$pdo_connection = new PDO('sqlite:test.db'); // por ejemplo
$user = new User($pdo_connection);
// o
$user = new User(null, [ 'connection' => $pdo_connection ]);
// o
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Si quieres evitar tener que establecer siempre una `$database_connection` cada vez que llamas a un active record, ¡hay formas de evitarlo!

```php
// index.php o bootstrap.php
// Configúrala como una clase registrada en Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// Y ahora, ¡no se requieren argumentos!
$user = new User();
```

> **Nota:** Si planeas hacer pruebas unitarias, hacerlo de esta manera puede añadir algunos desafíos a las pruebas, pero en general, como puedes inyectar tu conexión con `setDatabaseConnection()` o `$config['connection']`, no es demasiado malo.

Si necesitas actualizar la conexión a la base de datos, por ejemplo, si estás ejecutando un script CLI de larga duración y necesitas actualizar la conexión de vez en cuando, puedes restablecer la conexión con `$your_record->setDatabaseConnection($pdo_connection)`.

## Contribuyendo

Por favor, hazlo. :D

### Configuración

Cuando contribuyas, asegúrate de ejecutar `composer test-coverage` para mantener el 100 % de cobertura de pruebas (esto no es cobertura de pruebas unitarias real, sino más bien pruebas de integración).

También asegúrate de ejecutar `composer beautify` y `composer phpcs` para corregir cualquier error de linting.

## Licencia

MIT
