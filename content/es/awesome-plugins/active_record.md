# Active Record de FlightPHP

Un active record es mapear una entidad de base de datos a un objeto PHP. En pocas palabras, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila en esa tabla a una clase `User` y un objeto `$user` en tu código. Ver [ejemplo básico](#ejemplo-básico).

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
	public function __construct($conexion_base_de_datos)
	{
		// puedes configurarlo de esta manera
		parent::__construct($conexion_base_de_datos, 'users');
		// o de esta manera
		parent::__construct($conexion_base_de_datos, null, [ 'tabla' => 'users']);
	}
}
```

¡Y ahora observa cómo sucede la magia!

```php
// para sqlite
$conexion_base_de_datos = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión real de base de datos

// para mysql
$conexion_base_de_datos = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nombre_usuario', 'contraseña');

// o mysqli
$conexion_base_de_datos = new mysqli('localhost', 'nombre_usuario', 'contraseña', 'test_db');
// o mysqli con creación no basada en objetos
$conexion_base_de_datos = mysqli_connect('localhost', 'nombre_usuario', 'contraseña', 'test_db');

$user = new User($conexion_base_de_datos);
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

¡Y así de fácil fue agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la extraes?

```php
$user->find(1); // encuentra id = 1 en la base de datos y devuélvelo.
echo $user->name; // 'Bobby Tables'
```

¿Y si quieres encontrar a todos los usuarios?

```php
$users = $user->findAll();
```

¿Qué pasa con una condición específica?

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

Esto se puede usar como una biblioteca independiente o con el Framework Flight PHP. Completamente a tu elección.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$conexion_pdo = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión real de base de datos

$User = new User($conexion_pdo);
```

### Framework Flight PHP
Si estás usando el Framework Flight PHP, puedes registrar la clase ActiveRecord como un servicio (pero honestamente no es necesario).

```php
Flight::register('usuario', 'User', [ $conexion_pdo ]);

// luego puedes usarlo así en un controlador, una función, etc.

Flight::user()->find(1);
```

## Referencia de la API
### Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y asígnalo al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave primaria con ese valor. Si no se pasa nada, simplemente encontrará el primer registro en la tabla.

Adicionalmente, se le pueden pasar otros métodos auxiliares para consultar tu tabla.

```php
// encuentra un registro con algunas condiciones previas
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
$user->email = 'prueba@example.com';
$user->update();
```

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

Datos 'dirty' se refieren a los datos que han cambiado en un registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "dirty" en este punto.

$user->email = 'prueba@example.com'; // ahora el email se considera "dirty" ya que ha cambiado.
$user->update();
// ahora no hay datos que estén 'dirty' porque se han actualizado y persistido en la base de datos

$user->password = password_hash()'nueva_contraseña'); // ahora esto está 'dirty'
$user->dirty(); // pasar nada limpiará todas las entradas 'dirty'.
$user->update(); // nada se actualizará porque no se capturó nada como 'dirty'.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('una contraseña diferente') ]);
$user->update(); // tanto el nombre como la contraseña se actualizarán.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Restablece el registro actual a su estado inicial. Esto es realmente útil para usar en comportamientos de bucle.
Si pasas `true`, también se restablecerán los datos de la consulta que se utilizaron para encontrar el objeto actual (comportamiento predeterminado).

```php
$usuarios = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_compania = new UserCompany($conexion_pdo);

foreach($usuarios as $user) {
	$user_company->reset(); // comienza con una pizarra limpia
	$user_company->user_id = $user->id;
	$user_company->company_id = $algún_id_empresa;
	$user_company->insert();
}
```

### Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Puedes seleccionar solo algunas de las columnas en una tabla si lo deseas (es más eficiente en tablas realmente anchas con muchas columnas)

```php
$user->select('id', 'nombre')->find();
```

#### `from(string $table)`

Técnicamente también puedes elegir otra tabla. ¡¿Por qué no?!

```php
$user->select('id', 'nombre')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Incluso puedes unirte a otra tabla en la base de datos.

```php
$user->join('contactos', 'contactos.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Puedes establecer algunos argumentos where personalizados (no puedes establecer parámetros en esta declaración where)

```php
$user->where('id=1 AND nombre="demo"')->find();
```

**Nota de seguridad** - Podrías sentir la tentación de hacer algo como `$user->where("id = '{$id}' AND nombre = '{$nombre}'")->find();`. ¡Por favor NO LO HAGAS! ¡¡¡Esto es susceptible a lo que se conoce como ataques de inyección SQL!!! Hay muchos artículos en línea, por favor busca en Google "ataques de inyección SQL php" y encontrarás muchos artículos sobre este tema. La forma adecuada de manejar esto con esta biblioteca es en lugar de este método `where()`, harías algo más como `$user->eq('id', $id)->eq('nombre', $nombre)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupa tus resultados por una condición particular.

```php
$user->select('COUNT(*) as count')->groupBy('nombre')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Ordena la consulta devuelta de una manera específica.

```php
$user->orderBy('nombre DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limita la cantidad de registros devueltos. Si se da un segundo entero, será el desplazamiento, igual que en SQL.

```php
$user->orderby('nombre DESC')->limit(0, 10)->findAll();
```

### Condiciones WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Donde `campo = $valor`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Donde `campo <> $valor`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Donde `campo IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Donde `campo IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Donde `campo > $valor`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Donde `campo < $valor`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Donde `campo >= $valor`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Donde `campo <= $valor`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Donde `campo LIKE $valor` o `campo NOT LIKE $valor`

```php
$user->like('nombre', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Donde `campo IN($valor)` o `campo NOT IN($valor)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Donde `campo BETWEEN $valor AND $valor1`

```php
$user->between('id', [1, 2])->find();
```

### Relaciones
Puedes establecer varios tipos de relaciones usando esta biblioteca. Puedes establecer relaciones uno a muchos y uno a uno entre tablas. Esto requiere una configuración adicional en la clase de antemano.

Configurar el array `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como quieras. El nombre de la ActiveRecord probablemente está bien. Ej: usuario, contacto, cliente
	'whatever_active_record' => [
		// requerido
		self::HAS_ONE, // este es el tipo de relación

		// requerido
		'Clase_Algo', // esta es la clase ActiveRecord "otra" que hará referencia

		// requerido
		'clave_local', // esta es la clave local que hace referencia a la unión.
		// solo para tu información, también se une a la clave primaria del modelo "otro"

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que deseas ejecutar. [] si no quieres ninguno.

		// opcional
		'nombre_referencia_inversa' // esto es si deseas hacer referencia inversa a esta relación de vuelta a sí misma Ej: $usuario->contacto->usuario;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contactos' => [ self::HAS_MANY, Contacto::class, 'user_id' ],
		'contacto' => [ self::HAS_ONE, Contacto::class, 'user_id' ],
	];

	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'users');
	}
}

class Contacto extends ActiveRecord{
	protected array $relations = [
		'usuario' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'usuario_con_referencia' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contacto' ],
	];
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'contactos');
	}
}
```

¡Ahora tenemos las referencias configuradas para que las podamos usar muy fácilmente!

```php
$user = new User($conexion_pdo);

// encuentra el usuario más reciente.
$user->notNull('id')->orderBy('id desc')->find();

// obtén contactos usando la relación:
foreach($user->contactos as $contacto) {
	echo $contacto->id;
}

// o podemos ir en la otra dirección.
$contacto = new Contacto();

// encuentra un contacto
$contacto->find();

// obtén un usuario usando la relación:
echo $contacto->usuario->nombre; // este es el nombre del usuario
```

Bastante genial, ¿verdad?

### Configuración de Datos Personalizados
A veces puedes necesitar adjuntar algo único a tu ActiveRecord, como un cálculo personalizado que quizás sea más fácil simplemente adjuntar al objeto que se pasaría a, por ejemplo, una plantilla.

#### `setCustomData(string $field, mixed $value)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$user->setCustomData('cantidad_vistas_pagina', $cantidad_vistas_pagina);
```

Y luego simplemente lo referencias como una propiedad normal de objeto.

```php
echo $user->cantidad_vistas_pagina;
```

### Eventos

Una característica aún más increíble de esta biblioteca es acerca de los eventos. Los eventos se desencadenan en ciertos momentos en función de ciertos métodos que llamas. Son muy, muy útiles para configurar datos automáticamente.

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
		// podrías hacer esto para configurar automáticamente la conexión
		$config['conexion'] = Flight::db();
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
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'users');
	}

	protected function beforeFind(self $self) {
		// siempre ejecuta id >= 0 si eso es lo que te gusta
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Probablemente sea más útil si necesitas ejecutar alguna lógica cada vez que se recupere este registro. ¿Necesitas descifrar algo? ¿Necesitas ejecutar una consulta de recuento personalizada cada vez (no es eficiente pero lo que sea)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'users');
	}

	protected function afterFind(self $self) {
		// descifrando algo
		$self->secreto = tuFuncionDescifrar($self->secreto, $alguna_clave);

		// tal vez almacenar algo personalizado como una consulta???
		$self->setCustomData('cantidad_vistas', $self->select('COUNT(*) count')->from('vistas_usuario')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Esto probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datosprotected function beforeFindAll(self $self) {
		// siempre ejecuta id >= 0 si eso es lo que te gusta
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar a `afterFind()` ¡pero puedes hacerlo con todos los registros en su lugar!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// haz algo chévere como afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas establecer algunos valores predeterminados cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'users');
	}

	protected function beforeInsert(self $self) {
		// establece algunos valores predeterminados
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

¡Tal vez tengas un caso de uso para cambiar datos después de insertarlos!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'users');
	}

	protected function afterInsert(self $self) {
		// tú decides
		Flight::cache()->set('id_inserción_más_reciente', $self->id);
		// ¡o lo que sea!
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas establecer algunos valores predeterminados cada vez que actualizas.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'users');
	}

	protected function beforeInsert(self $self) {
		// establece algunos valores predeterminados
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

¡Quizás tengas un caso de uso para cambiar datos después de actualizarlos!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'users');
	}

	protected function afterInsert(self $self) {
		// haz lo tuyo
		Flight::cache()->set('id_usuario_actualizado_más_reciente', $self->id);
		// ¡o lo que sea!
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Esto es útil si quieres que los eventos ocurran tanto cuando se insertan como actualizan los registros. Te ahorraré la larga explicación, pero estoy seguro de que puedes adivinar qué es.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'users');
	}

	protected function beforeSave(self $self) {
		$self->ultima_actualización = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

¡No estoy seguro de qué querrías hacer aquí, pero no juzgo! ¡Adelante!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexion_base_de_datos)
	{
		parent::__construct($conexion_base_de_datos, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Era un valiente soldado... :llorando:';
	} 
}
```

## Contribuciones

Por favor hazlo.

### Configuración

Cuando contribuyas, asegúrate de ejecutar `composer test-coverage` para mantener una cobertura de prueba del 100% (esto no es una verdadera cobertura de pruebas unitarias, más bien pruebas de integración).

Asegúrate también de ejecutar `composer beautify` y `composer phpcs` para corregir cualquier error de linteo.

## Licencia

MIT