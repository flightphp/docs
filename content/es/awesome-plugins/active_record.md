# FlightPHP Registro Activo

Un registro activo es el mapeo de una entidad de base de datos a un objeto PHP. En pocas palabras, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila en esa tabla a una clase `User` y un objeto `$user` en tu código base. Consulta el [ejemplo básico](#ejemplo-básico).

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
 * Una clase ActiveRecord suele ser en singular
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

¡Ahora mira cómo sucede la magia!

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

¡Y fue tan fácil agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la extraes?

```php
$user->find(1); // encontrar id = 1 en la base de datos y devolverlo.
echo $user->name; // 'Bobby Tables'
```

¿Y si quieres encontrar todos los usuarios?

```php
$users = $user->findAll();
```

¿Qué tal con una cierta condición?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

¿Ves lo divertido que es esto? ¡Vamos a instalarlo y empezar!

## Instalación

Simplemente instálalo con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto se puede usar como una biblioteca independiente o con el Marco de PHP Flight. Completamente a tu elección.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión de base de datos real

$User = new User($pdo_connection);
```

### Marco de PHP Flight
Si estás utilizando el Marcos de PHP Flight, puedes registrar la clase ActiveRecord como un servicio (pero honestamente no es necesario).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// luego puedes usarlo de esta manera en un controlador, una función, etc.

Flight::user()->find(1);
```

## Referencia de la API
### Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y asígnalo al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave principal con ese valor. Si no se pasa nada, simplemente encontrará el primer registro en la tabla.

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

#### `insert(): boolean|ActiveRecord`

Inserta el registro actual en la base de datos.

```php
$user = new User($pdo_connection);
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

#### `delete(): boolean`

Elimina el registro actual de la base de datos.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Los datos "dirty" se refieren a los datos que han sido cambiados en un registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "dirty" hasta este punto.

$user->email = 'test@example.com'; // ahora el correo electrónico se considera "dirty" ya que ha cambiado.
$user->update();
// ahora no hay datos que estén dirty porque se han actualizado y persistido en la base de datos

$user->password = password_hash()'newpassword'); // ahora esto está dirty
$user->dirty(); // pasar nada eliminará todas las entradas dirty.
$user->update(); // no se actualizará nada porque no se capturó nada como dirty.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('una contraseña diferente') ]);
$user->update(); // tanto el nombre como la contraseña se han actualizado.
```

### Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Puedes seleccionar solo algunas de las columnas de una tabla si así lo deseas (es más eficiente en tablas realmente extensas con muchas columnas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Técnicamente también puedes elegir otra tabla. ¿Por qué no?

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

**Nota de seguridad** - Puede que tengas la tentación de hacer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ¡POR FAVOR NO HAGAS ESTO! ¡Esto es susceptible a lo que se conoce como ataques de inyección SQL! Hay muchos artículos en línea, por favor busca "ataques de inyección SQL php" en Google y encontrarás muchos artículos sobre este tema. La forma correcta de manejar esto con esta biblioteca es en lugar de este método `where()`, harías algo así como `$user->eq('id', $id)->eq('name', $name)->find();`

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

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limita la cantidad de registros devueltos. Si se da un segundo entero, será un desplazamiento, el límite tal como en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### Condiciones WHERE
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

### Relaciones
Puedes establecer varios tipos de relaciones usando esta biblioteca. Puedes establecer relaciones uno a muchos y uno a uno entre tablas. Esto requiere una configuración adicional en la clase de antemano.

Configurar la matriz `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como quieras. El nombre de la ActiveRecord probablemente sea bueno. Ej: usuario, contacto, cliente
	'activerecord_de_lo_que_sea' => [
		// requerido
		self::HAS_ONE, // este es el tipo de relación

		// requerido
		'Alguna_Clase', // esta es la clase ActiveRecord "otra" a la que hará referencia

		// requerido
		'clave_local', // esta es la clave local que hace referencia a la unión.
		// solo como información, esto también se une solo a la clave principal del modelo "otro"

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que deseas ejecutar. [] si no deseas ninguno.

		// opcional
		'nombre_de_referencia_hacia_atrás' // esto es si deseas hacer referencia a esta relación hacia atrás hacia sí misma Ej: $usuario->contacto->usuario;
	];
]
```

```php
class Usuario extends ActiveRecord{
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
		'usuario_con_referencia_hacia_atrás' => [ self::BELONGS_TO, Usuario::class, 'id_usuario', [], 'contacto' ],
	];
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'contactos');
	}
}
```

¡Ahora tenemos las referencias configuradas para que puedas usarlas muy fácilmente!

```php
$usuario = new Usuario($conexion_pdo);

// encontrar el usuario más reciente.
$usuario->notNull('id')->orderBy('id desc')->find();

// obtener contactos usando la relación:
foreach($usuario->contactos as $contacto) {
	echo $contacto->id;
}

// o podemos ir en la otra dirección.
$contacto = new Contacto();

// encontrar un contacto
$contacto->find();

// obtener un usuario usando la relación:
echo $contacto->usuario->nombre; // este es el nombre de usuario
```

¡Bastante genial, ¿no?

### Estableciendo Datos Personalizados
A veces es posible que necesites adjuntar algo único a tu ActiveRecord, como un cálculo personalizado que podría ser más fácil de adjuntar al objeto y que luego se pasaría, digamos a una plantilla.

#### `setCustomData(string $campo, mixed $valor)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$user->setCustomData('conteo_de_vistas_de_página', $conteo_de_vistas_de_página);
```

Y luego simplemente lo referencias como una propiedad normal del objeto.

```php
echo $user->conteo_de_vistas_de_página;
```

### Eventos

Otra función súper impresionante de esta biblioteca se trata de eventos. Los eventos se activan en ciertos momentos en función de ciertos métodos que llamas. Son muy, muy útiles para configurar datos de forma automática.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Esto es realmente útil si necesitas establecer una conexión predeterminada o algo así.

```php
// index.php o bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// Usuario.php
class Usuario extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // no olvides la referencia &
		// podrías hacer esto para establecer automáticamente la conexión
		$config['conexion'] = Flight::db();
		// o esto
		$self->transformAndPersistConnection(Flight::db());
		
		// También puedes establecer el nombre de la tabla de esta manera.
		$config['tabla'] = 'usuarios';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Esto probablemente solo es útil si necesitas manipulación de la consulta cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}

	protected function beforeFind(self $self) {
		// siempre ejecutar id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este probablemente es más útil si siempre necesitas ejecutar alguna lógica cada vez que se recupera este registro. ¿Necesitas descifrar algo? ¿Necesitas ejecutar una consulta de recuento personalizada cada vez (que no es eficiente pero lo que quieras)?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}

	protected function afterFind(self $self) {
		// descifrando algo
		$self->secreto = tuFuncionDescifrar($self->secreto, $alguna_clave);

		// ¡tal vez almacenar algo personalizado como una consulta?
		$self->setCustomData('conteo_de_vistas', $self->select('COUNT(*) count')->from('vistas_de_usuario')->eq('id_usuario', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Probablemente solo útil si necesitas manipulación de la consulta cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}

	protected function beforeFindAll(self $self) {
		// siempre ejecutar id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar a `afterFind()` ¡pero puedes hacerlo a todos los registros en lugar de solo a uno!

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// haz algo genial como después de encontrar()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Esto es muy útil si necesitas establecer algunos valores predeterminados cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
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

¿Quizás tienes un caso de uso para cambiar los datos después de que se inserten?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// ¡haz lo que quieras!
		Flight::cache()->set('id_de_inserción_más_reciente', $self->id);
		// ¡o lo que sea....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas establecer algunos valores predeterminados cada vez en una actualización.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}

	protected function beforeInsert(self $self) {
		// establecer algunos valores predeterminados
		if(!$self->fecha_actualización) {
			$self->fecha_actualización = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

¿Quizás tienes un caso de uso para cambiar los datos después de que se actualicen?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// ¡haz lo que quieras!
		Flight::cache()->set('id_de_usuario_actualizado_más_recientemente', $self->id);
		// ¡o lo que sea....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Esto es útil si deseas que se produzcan eventos tanto cuando se realizan inserciones como actualizaciones. Te ahorraré la larga explicación, pero estoy seguro de que puedes adivinar qué es.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}

	protected function beforeSave(self $self) {
		$self->ultima_actualización = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

No estoy seguro de qué querrías hacer aquí, ¡pero no juzgo! ¡Adelante!

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'usuarios');
	}

	protected function beforeDelete(self $self) {
		echo 'Era un valiente soldado... :llorar:';
	} 
}
```

## Contribuir

Por favor hazlo.

### Configuración

Cuando contribuyas, asegúrate de ejecutar `composer test-coverage` para mantener una cobertura del 100% en las pruebas (esto no es una cobertura de pruebas unitarias real, más bien como pruebas de integración).

También asegúrate de ejecutar `composer beautify` y `composer phpcs` para corregir cualquier error de formato.

## Licencia

MIT