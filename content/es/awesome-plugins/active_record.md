# Flight Active Record 

Un active record está mapeando una entidad de la base de datos a un objeto PHP. Hablando claramente, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila de esa tabla a una clase `User` y un objeto `$user` en tu código base. Consulta [ejemplo básico](#ejemplo-básico).

## Ejemplo Básico

Vamos a asumir que tienes la siguiente tabla:

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
		// o de esta manera
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

¡Ahora observa cómo ocurre la magia!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión a base de datos real

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nombre_usuario', 'contraseña');

// o mysqli
$database_connection = new mysqli('localhost', 'nombre_usuario', 'contraseña', 'test_db');
// o mysqli con creación no basada en objetos
$database_connection = mysqli_connect('localhost', 'nombre_usuario', 'contraseña', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('una contraseña genial');
$user->insert();
// o $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('¡otra contraseña genial!');
$user->insert();
// ¡no puedes usar $user->save() aquí, o pensará que es una actualización!

echo $user->id; // 2
```

¡Y así de fácil fue agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la extraes?

```php
$user->find(1); // encuentra id = 1 en la base de datos y devuélvelo.
echo $user->name; // 'Bobby Tables'
```

¿Y si quieres encontrar todos los usuarios?

```php
$users = $user->findAll();
```

¿Qué pasa con una condición específica?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

¡Mira lo divertido que es esto! ¡Instalémoslo y empecemos!

## Instalación

Simplemente instálalo con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto puede ser usado como una biblioteca independiente o con el Framework PHP Flight. Completamente depende de ti.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión a base de datos real

$User = new User($pdo_connection);
```

### Framework PHP Flight
Si estás utilizando el Framework PHP Flight, puedes registrar la clase ActiveRecord como un servicio (pero honestamente no es necesario).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// luego puedes usarlo de esta manera en un controlador, una función, etc.

Flight::user()->find(1);
```

## Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y asígnalo al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave principal con ese valor. Si no se pasa nada, solo encontrará el primer registro en la tabla.

Además, puedes pasarle otros métodos auxiliares para consultar tu tabla.

```php
// encuentra un registro con algunas condiciones previamente
$user->notNull('password')->orderBy('id DESC')->find();

// encuentra un registro por un id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encuentra todos los registros en la tabla que especificas.

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

##### Claves primarias basadas en texto

Si tienes una clave primaria basada en texto (como un UUID), puedes establecer el valor de la clave primaria antes de insertarla de dos maneras.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'algún-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // o $user->save();
```

o puedes generar la clave primaria automáticamente a través de eventos.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// también puedes establecer la clave primaria de esta manera en lugar del array anterior.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // o como necesites generar tus ids únicos
	}
}
```

Si no estableces la clave primaria antes de insertar, se establecerá en el `rowid` y la base de datos la generará por ti, pero no persistirá porque ese campo puede que no exista en tu tabla. Por esto se recomienda usar el evento para manejar esto automáticamente.

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
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Si tienes relaciones definidas en la clase, guardará recursivamente esas relaciones también si han sido definidas, instanciadas y tienen datos sucios para actualizar. (v0.4.0 y superior)

#### `delete(): boolean`

Elimina el registro actual de la base de datos.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

También puedes eliminar varios registros ejecutando una búsqueda previa.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Datos "sucios" se refiere a los datos que han cambiado en un registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sucio" hasta este momento.

$user->email = 'test@example.com'; // ahora el correo electrónico se considera "sucio" porque ha cambiado.
$user->update();
// ahora no hay datos sucios porque se han actualizado y persistido en la base de datos

$user->password = password_hash()'nuevacontraseña'); // ahora esto está sucio
$user->dirty(); // al pasar nada se borrarán todas las entradas sucias.
$user->update(); // nada se actualizará porque nada se capturó como sucio.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('una contraseña diferente') ]);
$user->update(); // tanto name como password se actualizarán.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Este es un alias para el método `dirty()`. Es un poco más claro lo que estás haciendo.

```php
$user->copyFrom([ 'name' => 'algo', 'password' => password_hash('una contraseña diferente') ]);
$user->update(); // tanto name como password se actualizarán.
```

#### `isDirty(): boolean` (v0.4.0)

Devuelve `true` si el registro actual ha sido cambiado.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Restablece el registro actual a su estado inicial. Esto es realmente útil para usar en comportamientos de bucle.
Si pasas `true`, también restablecerá los datos de la consulta que se usaron para encontrar el objeto actual (comportamiento predeterminado).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // comenzar con una página en blanco
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Después de ejecutar un método `find()`, `findAll()`, `insert()`, `update()` o `save()`, puedes obtener el SQL que se construyó y usarlo para fines de depuración.

## Métodos de Consulta SQL
#### `select(string $campo1 [, string $campo2 ... ])`

Puedes seleccionar solo algunos de los campos de una tabla si así lo deseas (es más eficiente en tablas realmente anchas con muchos campos)

```php
$user->select('id', 'name')->find();
```

#### `from(string $tabla)`

¡Incluso puedes elegir otra tabla también! ¿Por qué no?

```php
$user->select('id', 'name')->from('usuario')->find();
```

#### `join(string $nombre_tabla, string $condición_join)`

También puedes unirte a otra tabla en la base de datos.

```php
$user->join('contactos', 'contactos.id_usuario = usuarios.id')->find();
```

#### `where(string $condiciones_where)`

Puedes establecer algunos argumentos where personalizados (no puedes establecer parámetros en esta declaración where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de seguridad** - Podrías estar tentado a hacer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ¡Por favor NO HAGAS ESTO! ¡Esto es susceptible a lo que se conoce como ataques de Inyección SQL. Hay muchos artículos en línea, por favor busca en Google "ataques de inyección sql php" y encontrarás muchos artículos sobre este tema. La forma correcta de manejar esto con esta biblioteca es en lugar de este método `where()`, harías algo más como `$user->eq('id', $id)->eq('name', $name)->find();` Si realmente tienes que hacer esto, la biblioteca `PDO` tiene `$pdo->quote($var)` para escaparlo por ti. Solo después de usar `quote()` puedes usarlo en una declaración `where()`.

#### `group(string $grupo_por_declaración)/groupBy(string $grupo_por_declaración)`

Agrupa tus resultados por una condición específica.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $ordenado_por_declaración)/orderBy(string $ordenado_por_declaración)`

Ordena la consulta devuelta de cierta manera.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $límite)/limit(int $desplazamiento, int $límite)`

Limita la cantidad de registros devueltos. Si se da un segundo entero, será un desplazamiento, luego un límite como en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## Condiciones WHERE
#### `equal(string $campo, mixed $valor) / eq(string $campo, mixed $valor)`

Donde `campo = $valor`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $campo, mixed $valor) / ne(string $campo, mixed $valor)`

Donde `campo <> $valor`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $campo)`

Donde `campo IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $campo) / notNull(string $campo)`

Donde `campo IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $campo, mixed $valor) / gt(string $campo, mixed $valor)`

Donde `campo > $valor`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $campo, mixed $valor) / lt(string $campo, mixed $valor)`

Donde `campo < $valor`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Donde `campo >= $valor`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $campo, mixed $valor) / le(string $campo, mixed $valor) / lte(string $campo, mixed $valor)`

Donde `campo <= $valor`

```php
$user->le('id', 1)->find();
```

#### `like(string $campo, mixed $valor) / notLike(string $campo, mixed $valor)`

Donde `campo LIKE $valor` o `campo NOT LIKE $valor`

```php
$user->like('name', 'de')->find();
```

#### `in(string $campo, array $valores) / notIn(string $campo, array $valores)`

Donde `campo IN($valor)` o `campo NOT IN($valor)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $campo, array $valores)`

Donde `campo BETWEEN $valor AND $valor1`

```php
$user->between('id', [1, 2])->find();
```

## Relaciones
Puedes establecer varios tipos de relaciones usando esta biblioteca. Puedes establecer relaciones uno a muchos y uno a uno entre tablas. Esto requiere un poco de configuración adicional en la clase de antemano.

Configurar el arreglo `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como quieras. El nombre del ActiveRecord es probablemente bueno. Ejemplo: user, contact, client
	'user' => [
		// requerido
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // este es el tipo de relación

		// requerido
		'Alguna_Clase', // este es el ActiveRecord "otro" que esta referencia

		// requerido
		// según el tipo de relación
		// self::HAS_ONE = la clave externa que hace referencia a la unión
		// self::HAS_MANY = la clave externa que hace referencia a la unión
		// self::BELONGS_TO = la clave local que hace referencia a la unión
		'clave_local_o_externa',
		// solo como información, esto también se une a la clave primaria del modelo "otro"

		// opcional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condiciones adicionales que desees al unir la relación
		// $registro->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'nombre_referencia_trasera' // esto es si deseas hacer referencia a esta relación hacia atrás en sí misma Ej: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'id_user' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'id_user' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'id_user' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'id_user', [], 'contact' ],
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

// encuentra al usuario más reciente
$user->notNull('id')->orderBy('id desc')->find();

// obtén contactos usando la relación:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// o podemos ir en la otra dirección.
$contact = new Contact();

// encuentra un$user = new User(configuración_pdo);

// encuentra un contacto
$contact->find();

// obtén al usuario usando la relación:
echo $contact->user->name; // este es el nombre de usuario
```

¡Bastante genial!

## Configuración de Datos Personalizados
A veces puede que necesites adjuntar algo único a tu ActiveRecord como un cálculo personalizado que podría ser más fácil simplemente adjuntarlo al objeto que luego se pasaría, digamos, a una plantilla.

#### `setCustomData(string $campo, mixed $valor)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$user->setCustomData('numero_visualizaciones_pagina', $numero_visualizaciones_pagina);
```

Y luego simplemente lo mencionas como una propiedad de objeto normal.

```php
echo $user->numero_visualizaciones_pagina;
```

## Eventos

Otra característica súper impresionante de esta biblioteca son los eventos. Los eventos se desencadenan en ciertos momentos basados en ciertos métodos que llamas. Son muy, muy útiles para configurar datos automáticamente.

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
		$config['table'] = 'usuarios';
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
		// siempre se ejecuta id >= 0 si es lo que prefieres
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este es probablemente más útil si necesitas ejecutar alguna lógica cada vez que se recupera este registro. ¿Necesitas descifrar algo? ¿Necesitas ejecutar una consulta de recuento personalizada cada vez (no es eficiente, pero lo que sea)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// descifrando algo
		$self->secreto = tuFuncionParaDescifrar($self->secreto, $alguna_clave);

		// quizás almacenar algo personalizado como una consulta???
		$self->setCustomData('visualizaciones', $self->select('COUNT(*) count')->from('visualizaciones_usuario')->eq('id_usuario', $self->id)['count']; 
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
		// siempre se ejecuta id >= 0 si es lo que prefieres
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar a `afterFind()` ¡Pero puedes hacerlo con todos los registros!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// haz algo genial como después de encontrar()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas que se establezcan algunos valores predeterminados cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// establece algunos valores predeterminados sólidos
		if(!$self->fecha_creacion) {
			$self->fecha_creacion = gmdate('Y-m-d');
		}

		if(!$self->contrasena) {
			$self->contrasena = password_hash((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

¿Quizás tienes un caso de uso para cambiar datos después de insertarlos?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// haz lo que necesites
		Flight::cache()->set('id_mas_reciente_insertado', $self->id);
		// o lo que sea....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas que se establezcan algunos valores predeterminados cada vez al actualizar.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// establece algunos valores predeterminados sólidos
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
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// haz lo que necesites
		Flight::cache()->set('id_usuario_mas_recientemente_actualizado', $self->id);
		// o lo que sea....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Es útil si quieres que eventos sucedan tanto cuando se inserta como cuando se actualiza. Te ahorraré la larga explicación, pero estoy seguro de que puedes adivinar lo que es.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeSave(self $self) {
		$self->ultima_actualizacion = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

¡No está claro qué podrías querer hacer aquí, pero no hay juicios aquí! ¡Adelante!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Era un valiente soldado... :cara_llorando:';
	} 
}
```

## Gestión de Conexión a Base de Datos

Cuando estás utilizando esta biblioteca, puedes configurar la conexión a la base de datos de varias maneras. Puedes configurar la conexión en el constructor, puedes configurarla a través de una variable de configuración `$config['connection']` o puedes configurarla a través de `setDatabaseConnection()` (v0.4.1). 

```php
$conexion_pdo = new PDO('sqlite:test.db'); // por ejemplo
$user = new User(consulta_pdo);

// o
$user = new User(null, [ 'conexion' => $conexion_pdo ]);
// o
$user = new User();
$user->setDatabaseConnection($conexion_pdo);
```

Si necesitas actualizar la conexión a la base de datos, por ejemplo, si estás ejecutando un script CLI de larga duración y necesitas actualizar la conexión cada cierto tiempo, puedes restablecer la conexión con `$tu_registro->setDatabaseConnection($conexion_pdo)`.

## Contribuciones

Por favor hazlo. :D

## Configuración

Cuando contribuyas, asegúrate de ejecutar `composer test-coverage` para mantener una cobertura del 100% en las pruebas (esto no es una cobertura de prueba unitaria real, es más como pruebas de integración).

También asegúrate de ejecutar `composer beautify` y `composer phpcs` para corregir cualquier error de linting.

## Licencia

MIT