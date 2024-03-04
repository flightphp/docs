# Flight Active Record 

Un registro activo es mapear una entidad de base de datos a un objeto PHP. Hablando claramente, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila en esa tabla a una clase `User` y a un objeto `$user` en tu código base. Ver [ejemplo básico](#ejemplo-básico).

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
class Usuario extends flight\ActiveRecord {
	public function __construct($conexion_base_datos)
	{
		// puedes configurarlo de esta manera
		parent::__construct($conexion_base_datos, 'users');
		// o de esta manera
		parent::__construct($conexion_base_datos, null, [ 'table' => 'users']);
	}
}
```

¡Ahora mira la magia suceder!

```php
// para sqlite
$conexion_base_datos = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión real a la base de datos

// para mysql
$conexion_base_datos = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nombre_usuario', 'contraseña');

// o mysqli
$conexion_base_datos = new mysqli('localhost', 'nombre_usuario', 'contraseña', 'test_db');
// o mysqli con creación no basada en objetos
$conexion_base_datos = mysqli_connect('localhost', 'nombre_usuario', 'contraseña', 'test_db');

$usuario = new Usuario($conexion_base_datos);
$usuario->name = 'Bobby Tables';
$usuario->password = password_hash('una contraseña genial');
$usuario->insert();
// o $user->save();

echo $usuario->id; // 1

$usuario->name = 'Joseph Mamma';
$usuario->password = password_hash('¡otra contraseña genial!!!');
$usuario->insert();
// ¡no se puede usar $usuario->save() aquí o pensará que es una actualización!

echo $usuario->id; // 2
```

¡Y fue así de fácil agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la extraes?

```php
$usuario->find(1); // encuentra id = 1 en la base de datos y devuélvelo.
echo $usuario->name; // 'Bobby Tables'
```

¿Y si quieres encontrar todos los usuarios?

```php
$usuarios = $usuario->findAll();
```

¿Qué pasa con una cierta condición?

```php
$usuarios = $usuario->like('name', '%mamma%')->findAll();
```

¿Ves lo divertido que es esto? ¡Vamos a instalarlo y empezar!

## Instalación

Simplemente instala con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto se puede usar como una biblioteca independiente o con el Framework de PHP Flight. Completamente a tu elección.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$conexion_pdo = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión real a la base de datos

$Usuario = new Usuario($conexion_pdo);
```

### Framework de PHP Flight
Si estás usando el Framework de PHP Flight, puedes registrar la clase ActiveRecord como un servicio (pero honestamente no es necesario).

```php
Flight::register('usuario', 'Usuario', [ $conexion_pdo ]);

// entonces puedes usarlo de esta manera en un controlador, una función, etc.

Flight::usuario()->find(1);
```

## Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y asígnalo al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave principal con ese valor. Si no se pasa nada, simplemente encontrará el primer registro en la tabla.

Adicionalmente, puedes pasarle otros métodos auxiliares para consultar tu tabla.

```php
// encuentra un registro con algunas condiciones previas
$usuario->notNull('password')->orderBy('id DESC')->find();

// encuentra un registro por un id específico
$id = 123;
$usuario->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encuentra todos los registros en la tabla que especifiques.

```php
$usuario->findAll();
```

#### `insert(): boolean|ActiveRecord`

Inserta el registro actual en la base de datos.

```php
$usuario = new Usuario($conexion_pdo);
$usuario->name = 'demo';
$usuario->password = md5('demo');
$usuario->insert();
```

#### `update(): boolean|ActiveRecord`

Actualiza el registro actual en la base de datos.

```php
$usuario->greaterThan('id', 0)->orderBy('id desc')->find();
$usuario->email = 'test@example.com';
$usuario->update();
```

#### `delete(): boolean`

Elimina el registro actual de la base de datos.

```php
$usuario->gt('id', 0)->orderBy('id desc')->find();
$usuario->delete();
```

También puedes eliminar varios registros ejecutando una búsqueda antes.

```php
$usuario->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Datos sucios se refieren a los datos que se han cambiado en un registro.

```php
$usuario->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sucio" hasta este punto.

$usuario->email = 'test@example.com'; // ahora el correo electrónico se considera "sucio" ya que ha cambiado.
$usuario->update();
// ahora no hay datos sucios porque se han actualizado y persistido en la base de datos

$usuario->password = password_hash()'nuevacontraseña'); // ahora esto es sucio
$usuario->dirty(); // no pasar nada limpiará todas las entradas sucias.
$usuario->update(); // nada se actualizará porque no se capturó nada como sucio.

$usuario->dirty([ 'name' => 'algo', 'password' => password_hash('una contraseña diferente') ]);
$usuario->update(); // tanto el nombre como la contraseña se actualizan.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Restablece el registro actual a su estado inicial. Esto es realmente útil para usar en comportamientos de bucle.
Si pasas `true`, también restablecerá los datos de la consulta que se usó para encontrar el objeto actual (comportamiento predeterminado).

```php
$usuarios = $usuario->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($conexion_pdo);

foreach($usuarios as $user) {
	$user_company->reset(); // empezar con una pizarra limpia
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

## Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Puedes seleccionar solo unos pocos de las columnas en una tabla si lo deseas (es más eficiente en tablas realmente anchas con muchas columnas)

```php
$usuario->select('id', 'name')->find();
```

#### `from(string $table)`

¡Técnicamente también puedes elegir otra tabla! ¿¡Por qué no!?

```php
$usuario->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Incluso puedes unirte a otra tabla en la base de datos.

```php
$usuario->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Puedes establecer algunos argumentos where personalizados (no puedes establecer parámetros en esta declaración where)

```php
$usuario->where('id=1 AND name="demo"')->find();
```

**Nota de seguridad** - Podrías sentirte tentado a hacer algo como `$usuario->where("id = '{$id}' AND name = '{$name}'")->find();`. ¡Por favor NO HAGAS ESTO! ¡Esto es susceptible a lo que se conoce como ataques de inyección SQL! Hay muchos artículos en línea, por favor busca en Google "sql injection attacks php" y encontrarás muchos artículos sobre este tema. La forma correcta de manejar esto con esta biblioteca es en lugar de este método `where()`, harías algo más parecido a `$usuario->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Grupo tus resultados por una condición particular.

```php
$usuario->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Ordena la consulta devuelta de cierta manera.

```php
$usuario->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limita la cantidad de registros devueltos. Si se da un segundo entero, será un desplazamiento, limitará igual que en SQL.

```php
$usuario->orderby('name DESC')->limit(0, 10)->findAll();
```

## Condiciones WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Donde `field = $value`

```php
$usuario->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Donde `field <> $value`

```php
$usuario->ne('id', 1)->find();
```

#### `isNull(string $field)`

Donde `field IS NULL`

```php
$usuario->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Donde `field IS NOT NULL`

```php
$usuario->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Donde `field > $value`

```php
$usuario->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Donde `field < $value`

```php
$usuario->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Donde `field >= $value`

```php
$usuario->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Donde `field <= $value`

```php
$usuario->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Donde `field LIKE $value` o `field NOT LIKE $value`

```php
$usuario->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Donde `field IN($value)` o `field NOT IN($value)`

```php
$usuario->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Donde `field BETWEEN $value AND $value1`

```php
$usuario->between('id', [1, 2])->find();
```

## Relaciones
Puedes establecer varios tipos de relaciones usando esta biblioteca. Puedes establecer relaciones uno a muchos y uno a uno entre tablas. Esto requiere un poco de configuración adicional en la clase previamente.

Configurar la matriz `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como quieras. El nombre del ActiveRecord probablemente sea bueno. Por ejemplo: usuario, contacto, cliente
	'usuario' => [
		// requerido
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // este es el tipo de relación

		// requerido
		'Otra_Clase', // esta es la clase ActiveRecord "otra" a la que hará referencia

		// requerido
		// dependiendo del tipo de relación
		// self::HAS_ONE = la clave externa que hace referencia al vínculo
		// self::HAS_MANY = la clave externa que hace referencia al vínculo
		// self::BELONGS_TO = la clave local que hace referencia al vínculo
		'clave_local_o_externa',
		// solo como nota, esto también se une solo a la clave principal del modelo "otro"

		// opcional
		[ 'eq' => [ 'id_cliente', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condiciones adicionales que quieres al unir la relación
		// $registro->eq('id_cliente', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'nombre_referencia_circular' // esto es si quieres que esta relación de referencia circular regrese a sí misma Ej: $usuario->contacto->usuario;
	];
]
```

```php
class Usuario extends ActiveRecord{
	protected array $relations = [
		'contactos' => [ self::HAS_MANY, Contacto::class, 'id_usuario' ],
		'contacto' => [ self::HAS_ONE, Contacto::class, 'id_usuario' ],
	];

	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}
}

class Contacto extends ActiveRecord{
	protected array $relations = [
		'usuario' => [ self::BELONGS_TO, Usuario::class, 'id_usuario' ],
		'usuario_con_referencia_circular' => [ self::BELONGS_TO, Usuario::class, 'id_usuario', [], 'contacto' ],
	];
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'contactos');
	}
}
```

¡Ahora tenemos las referencias configuradas para que podamos usarlas muy fácilmente!

```php
$usuario = new Usuario($conexion_pdo);

// encuentre el usuario más reciente.
$usuario->notNull('id')->orderBy('id desc')->find();

// obtén contactos usando la relación:
foreach($usuario->contactos as $contacto) {
	echo $contacto->id;
}

// o podemos ir en la otra dirección.
$contacto = new Contacto();

// encuentra un contacto
$contacto->find();

// obtener usuario usando la relación:
echo $contacto->usuario->name; // este es el nombre de usuario
```

¡Bastante genial, ¿verdad?

## Configuración de Datos Personalizados
A veces puede que necesites adjuntar algo único a tu ActiveRecord como un cálculo personalizado que podría ser más fácil simplemente adjuntarlo al objeto que luego se pase a un plantilla.

#### `setCustomData(string $field, mixed $value)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$usuario->setCustomData('cantidad_vistas_pagina', $cantidad_vistas_pagina);
```

Y luego simplemente lo referencias como una propiedad de objeto normal.

```php
echo $usuario->pagina_vistas_cantidad;
```

## Eventos

Otra característica súper impresionante sobre esta biblioteca son los eventos. Los eventos se activan en ciertos momentos basados en ciertos métodos que llamas. Son muy útiles para configurar datos automáticamente.

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
		// podrías hacer esto para configurar automáticamente la conexión
		$config['conexion'] = Flight::db();
		// o esto
		$self->transformAndPersistConnection(Flight::db());
		
		// También puedes configurar el nombre de la tabla de esta manera.
		$config['tabla'] = 'usuarios';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Esto probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeFind(self $self) {
		// siempre ejecutar id >= 0 si eso es lo que prefieres
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este probablemente sea más útil si necesitas ejecutar alguna lógica cada vez que se obtiene este registro. ¿Necesitas descifrar algo? ¿Necesitas ejecutar una consulta de conteo personalizada cada vez (no es eficiente pero bueno)?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function afterFind(self $self) {
		// descifrando algo
		$self->secreto = tuFuncionDesencriptar($self->secreto, $some_key);

		// tal vez guardar algo personalizado como una consulta???
		$self->setCustomData('cantidad_vistas', $self->select('COUNT(*) count')->from('visitas_usuario')->eq('id_usuario', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Esto probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeFindAll(self $self) {
		// siempre ejecutar id >= 0 si eso es lo que prefieres
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar a `afterFind()` ¡pero puedes hacerlo con todos los registros en cambio!

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// haz algo genial como después de encontrar()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas establecer algunos valores predeterminados cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeInsert(self $self) {
		// establecer algunos valores predeterminados
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

¿Quizás tienes un caso de uso para cambiar los datos después de ser insertados?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// haces lo tuyo
		Flight::cache()->set('id_insertado_mas_reciente', $self->id);
		// ¡o lo que sea....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas establecer algunos valores predeterminados cada vez en una actualización.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeUpdate(self $self) {
		// establecer algunos valores predeterminados
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

¿Quizás tienes un caso de uso para cambiar los datos después de ser actualizados?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// haces lo tuyo
		Flight::cache()->set('id_usuario_mas_recientemente_actualizado', $self->id);
		// ¡o lo que sea....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Esto es útil si quieres que los eventos ocurran tanto cuando ocurren inserciones como actualizaciones. Te salvaré la larga explicación, pero estoy seguro de que puedes adivinar qué es.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeSave(self $self) {
		$self->ultimo_actualizado = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

¡No estoy seguro de qué querrías hacer aquí, pero no juzgo! ¡Adelante!

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeDelete(self $self) {
		echo 'Era un valiente soldado... :cry-face:';
	} 
}
```

## Contribuyendo

Por favor hazlo.

## Configuración

Cuando contribuyas, asegúrate de ejecutar `composer test-coverage` para mantener una cobertura de prueba del 100% (esto no es una cobertura de prueba unitaria real, más bien como pruebas de integración).

También asegúrate de ejecutar `composer beautify` y `composer phpcs` para corregir cualquier error de linting.

## Licencia

MIT