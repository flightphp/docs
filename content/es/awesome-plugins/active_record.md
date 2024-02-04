# FlightPHP Active Record 

Un registro activo mapea una entidad de la base de datos a un objeto PHP. Hablando claramente, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila en esa tabla a una clase `Usuario` y a un objeto `$usuario` en tu código. Consulta [ejemplo básico](#basic-example).

## Ejemplo Básico

Supongamos que tienes la siguiente tabla:

```sql
CREATE TABLE usuarios (
	id INTEGER PRIMARY KEY, 
	nombre TEXT, 
	contraseña TEXT 
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
 * @property string $nombre
 * @property string $contraseña
 */ 
class Usuario extends flight\ActiveRecord {
	public function __construct($conexion_base_datos)
	{
		// puedes configurarlo de esta manera
		parent::__construct($conexion_base_datos, 'usuarios');
		// o de esta manera
		parent::__construct($conexion_base_datos, null, [ 'table' => 'usuarios']);
	}
}
```

¡Ahora observa cómo sucede la magia!

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
$usuario->nombre = 'Bobby Tables';
$usuario->contraseña = password_hash('una contraseña genial');
$usuario->insert();
// o $usuario->save();

echo $usuario->id; // 1

$usuario->nombre = 'Joseph Mamma';
$usuario->contraseña = password_hash('¡otra contraseña genial!');
$usuario->insert();
// ¡no se puede usar $usuario->save() aquí o pensará que es una actualización!

echo $usuario->id; // 2
```

¡Y fue así de fácil agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la extraes?

```php
$usuario->find(1); // encuentra id = 1 en la base de datos y devuélvelo.
echo $usuario->nombre; // 'Bobby Tables'
```

¿Y si quieres encontrar todos los usuarios?

```php
$usuarios = $usuario->findAll();
```

¿Qué tal con una cierta condición?

```php
$usuarios = $usuario->like('nombre', '%mamma%')->findAll();
```

¿Ves qué divertido es? ¡Vamos a instalarlo y empezar!

## Instalación

Simplemente instala con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto se puede usar como una biblioteca independiente o con el Marco de Trabajo Flight PHP. Completamente tu decisión.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$conexion_pdo = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión real a la base de datos

$Usuario = new Usuario($conexion_pdo);
```

### Marco de Trabajo Flight PHP
Si estás utilizando el Marco de Trabajo Flight PHP, puedes registrar la clase ActiveRecord como un servicio (pero sinceramente no es necesario).

```php
Flight::register('usuario', 'Usuario', [ $conexion_pdo ]);

// luego puedes usarlo así en un controlador, una función, etc.

Flight::usuario()->find(1);
```

## Referencia de API
### Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y asígnalo al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave principal con ese valor. Si no pasa nada, simplemente encontrará el primer registro en la tabla.

Además, puedes pasarle otros métodos auxiliares para consultar tu tabla.

```php
// busca un registro con algunas condiciones previas
$usuario->notNull('contraseña')->orderBy('id DESC')->find();

// busca un registro por un id específico
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
$usuario->nombre = 'demo';
$usuario->contraseña = md5('demo');
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

#### `dirty(array  $dirty = []): ActiveRecord`

Los datos sucios se refieren a los datos que se han cambiado en un registro.

```php
$usuario->greaterThan('id', 0)->orderBy('id desc')->find();

// en este punto, no hay nada "sucio".

$usuario->email = 'test@example.com'; // ahora el correo electrónico se considera "sucio" ya que ha cambiado.
$usuario->update();
// ahora no hay datos sucios porque se han actualizado y persistido en la base de datos

$usuario->contraseña = password_hash()'nuevacontraseña'); // ahora esto es sucio
$usuario->dirty(); // no pasar nada limpiará todas las entradas sucias.
$usuario->update(); // no se actualizará nada porque no se capturó como sucio.

$usuario->dirty([ 'nombre' => 'algo', 'contraseña' => password_hash('una contraseña diferente') ]);
$usuario->update(); // se actualizarán tanto el nombre como la contraseña.
```

### Métodos de Consulta SQL
#### `select(string $campo1 [, string $campo2 ... ])`

Puedes seleccionar solo algunos de los campos en una tabla si así lo deseas (es más eficiente en tablas realmente largas con muchos campos)

```php
$usuario->select('id', 'nombre')->find();
```

#### `from(string $tabla)`

Técnicamente, ¡también podrías elegir otra tabla! ¡¿Por qué demonios no?!

```php
$usuario->select('id', 'nombre')->from('usuario')->find();
```

#### `join(string $nombre_tabla, string $condicion_join)`

Incluso puedes unirte a otra tabla en la base de datos.

```php
$usuario->join('contactos', 'contactos.id_usuario = usuarios.id')->find();
```

#### `where(string $condiciones_where)`

Puedes establecer algunos argumentos where personalizados aquí (no puedes establecer parámetros en esta declaración where)

```php
$usuario->where('id=1 AND nombre="demo"')->find();
```

**Nota de Seguridad** - Podrías sentirte tentado a hacer algo como `$usuario->where("id = '{$id}' AND nombre = '{$nombre}'")->find();`. ¡¡¡POR FAVOR NO HAGAS ESTO!!! Esto es susceptible a lo que se conoce como ataques de inyección SQL. Hay muchos artículos en línea, por favor, busca en Google "ataques de inyección SQL php" y encontrarás muchos artículos sobre este tema. La forma correcta de manejar esto con esta biblioteca es en lugar de este método `where()`, harías algo más como `$usuario->eq('id', $id)->eq('nombre', $nombre)->find();`

#### `group(string $sentencia_group_by)/groupBy(string $sentencia_group_by)`

Agrupa tus resultados por una condición particular.

```php
$usuario->select('COUNT(*) as count')->groupBy('nombre')->findAll();
```

#### `order(string $sentencia_order_by)/orderBy(string $sentencia_order_by)`

Ordena la consulta devuelta de cierta manera.

```php
$usuario->orderBy('nombre DESC')->find();
```

#### `limit(string $limite)/limit(int $inicio, int $limite)`

Limita la cantidad de registros devueltos. Si se da un segundo entero, será un desplazamiento, límite igual que en SQL.

```php
$usuario->orderby('nombre DESC')->limit(0, 10)->findAll();
```

### Condiciones WHERE
#### `equal(string $campo, mixed $valor) / eq(string $campo, mixed $valor)`

Donde `campo = $valor`

```php
$usuario->eq('id', 1)->find();
```

#### `notEqual(string $campo, mixed $valor) / ne(string $campo, mixed $valor)`

Donde `campo <> $valor`

```php
$usuario->ne('id', 1)->find();
```

#### `isNull(string $campo)`

Donde `campo IS NULL`

```php
$usuario->isNull('id')->find();
```
#### `isNotNull(string $campo) / notNull(string $campo)`

Donde `campo IS NOT NULL`

```php
$usuario->isNotNull('id')->find();
```

#### `greaterThan(string $campo, mixed $valor) / gt(string $campo, mixed $valor)`

Donde `campo > $valor`

```php
$usuario->gt('id', 1)->find();
```

#### `lessThan(string $campo, mixed $valor) / lt(string $campo, mixed $valor)`

Donde `campo < $valor`

```php
$usuario->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $campo, mixed $valor) / ge(string $campo, mixed $valor) / gte(string $campo, mixed $valor)`

Donde `campo >= $valor`

```php
$usuario->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $campo, mixed $valor) / le(string $campo, mixed $valor) / lte(string $campo, mixed $valor)`

Donde `campo <= $valor`

```php
$usuario->le('id', 1)->find();
```

#### `like(string $campo, mixed $valor) / notLike(string $campo, mixed $valor)`

Donde `campo LIKE $valor` o `campo NOT LIKE $valor`

```php
$usuario->like('nombre', 'de')->find();
```

#### `in(string $campo, array $valores) / notIn(string $campo, array $valores)`

Donde `campo IN($valor)` o `campo NOT IN($valor)`

```php
$usuario->in('id', [1, 2])->find();
```

#### `between(string $campo, array $valores)`

Donde `campo BETWEEN $valor AND $valor1`

```php
$usuario->between('id', [1, 2])->find();
```

### Relaciones
Puedes establecer varios tipos de relaciones usando esta biblioteca. Puedes establecer relaciones uno a muchos y uno a uno entre tablas. Esto requiere una configuración adicional en la clase de antemano.

Configurar el array `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como desees. El nombre del ActiveRecord probablemente es bueno. Ej .: usuario, contacto, cliente
	'registro_activo_cualquiera' => [
		// requerido
		self::HAS_ONE, // este es el tipo de relación

		// requerido
		'Algun_Clase', // este es la clase ActiveRecord "otra" que hará referencia a esta

		// requerido
		'clave_local', // esta es la clave local que hace referencia a la unión.
		// solo por tu información, esto también se une solo a la clave principal del modelo "otro"

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que deseas ejecutar. [] si no quieres ninguno.

		// opcional
		'nombre_referencia_inversa' // esto es si deseas referenciar esta relación nuevamente a sí misma Ej .: $usuario->contacto->usuario;
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
		'usuario_con_referencia' => [ self::BELONGS_TO, Usuario::class, 'id_usuario', [], 'contacto' ],
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

// encuentra el usuario más reciente.
$usuario->notNull('id')->orderBy('id desc')->find();

// obtén contactos usando la relación:
foreach($usuario->contactos as $contacto) {
	echo $contacto->id;
}

// o podemos ir en la otra dirección.
$contacto = new Contacto();

// encuentra un contacto
$contacto->find();

// obtén un usuario usando la relación:
echo $contacto->usuario->nombre; // este es el nombre del usuario
```

Bastante genial ¿verdad?

### Establecimiento de Datos Personalizados
A veces es posible que necesites adjuntar algo único a tu ActiveRecord, como un cálculo personalizado que podría ser más fácil simplemente adjuntar al objeto que luego se pasaría, digamos, a una plantilla.

#### `setCustomData(string $campo, mixed $valor)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$usuario->setCustomData('cantidad_vistas_pagina', $cantidad_vistas_pagina);
```

Y luego simplemente lo referencia como una propiedad de objeto normal.

```php
echo $usuario->cantidad_vistas_pagina;
```

### Eventos

Otra característica súper increíble de esta biblioteca se trata de eventos. Los eventos se activan en ciertos momentos basados en ciertos métodos que llamas. Son muy útiles para configurar datos automáticamente.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Esto es realmente útil si necesitas configurar una conexión predeterminada o algo así.

```php
// index.php o bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
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

Probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeFind(self $self) {
		// siempre ejecuta id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Probablemente sea más útil si siempre necesitas ejecutar alguna lógica cada vez que se recupera este registro. ¿Necesitas descifrar algo? ¿Necesitas ejecutar una consulta de conteo personalizada cada vez (no eficiente pero lo que sea)?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function afterFind(self $self) {
		// descifrando algo
		$self->secreto = tuFuncionDescifrar($self->secreto, $alguna_clave);

		// tal vez almacenar algo personalizado como una consulta???
		$self->setCustomData('cantidad_vistas', $self->select('COUNT(*) count')->from('vistas_usuario')->eq('id_usuario', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeFindAll(self $self) {
		// siempre ejecuta id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar a `afterFind()` ¡pero puedes hacerlo con todos los registros en su lugar!

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected```es
# Registro Activo de FlightPHP

Un registro activo mapea una entidad de base de datos a un objeto PHP. Hablando claramente, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila en esa tabla a una clase `Usuario` y a un objeto `$usuario` en tu código. Consulta [ejemplo básico](#ejemplo-básico).

## Ejemplo Básico

Supongamos que tienes la siguiente tabla:

```sql
CREATE TABLE usuarios (
	id INTEGER PRIMARY KEY, 
	nombre TEXT, 
	contraseña TEXT 
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
 * @property string $nombre
 * @property string $contraseña
 */ 
class Usuario extends flight\ActiveRecord {
	public function __construct($conexion_base_datos)
	{
		// puedes configurarlo de esta manera
		parent::__construct($conexion_base_datos, 'usuarios');
		// o de esta manera
		parent::__construct($conexion_base_datos, null, [ 'table' => 'usuarios']);
	}
}
```

¡Ahora observa cómo sucede la magia!

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
$usuario->nombre = 'Bobby Tables';
$usuario->contraseña = password_hash('una contraseña genial');
$usuario->insert();
// o $usuario->save();

echo $usuario->id; // 1

$usuario->nombre = 'Joseph Mamma';
$usuario->contraseña = password_hash('¡otra contraseña genial!');
$usuario->insert();
// ¡no se puede usar $usuario->save() aquí o pensará que es una actualización!

echo $usuario->id; // 2
```

¡Y fue así de fácil agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la extraes?

```php
$usuario->find(1); // encuentra id = 1 en la base de datos y devuélvelo.
echo $usuario->nombre; // 'Bobby Tables'
```

¿Y si quieres encontrar todos los usuarios?

```php
$usuarios = $usuario->findAll();
```

¿Qué tal con una cierta condición?

```php
$usuarios = $usuario->like('nombre', '%mamma%')->findAll();
```

¿Ves qué divertido es? ¡Vamos a instalarlo y empezar!

## Instalación

Simplemente instala con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto se puede usar como una biblioteca independiente o con el Marco de Trabajo Flight PHP. Completamente tu decisión.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$conexion_pdo = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarías una conexión real a la base de datos

$Usuario = new Usuario($conexion_pdo);
```

### Marco de Trabajo Flight PHP
Si estás utilizando el Marco de Trabajo Flight PHP, puedes registrar la clase ActiveRecord como un servicio (pero sinceramente no es necesario).

```php
Flight::register('usuario', 'Usuario', [ $conexion_pdo ]);

// luego puedes usarlo así en un controlador, una función, etc.

Flight::usuario()->find(1);
```

## Referencia de API
### Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y asígnalo al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave principal con ese valor. Si no pasa nada, simplemente encontrará el primer registro en la tabla.

Además, puedes pasarle otros métodos auxiliares para consultar tu tabla.

```php
// busca un registro con algunas condiciones previas
$usuario->notNull('contraseña')->orderBy('id DESC')->find();

// busca un registro por un id específico
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
$usuario->nombre = 'demo';
$usuario->contraseña = md5('demo');
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

#### `dirty(array  $dirty = []): ActiveRecord`

Los datos sucios se refieren a los datos que se han cambiado en un registro.

```php
$usuario->greaterThan('id', 0)->orderBy('id desc')->find();

// en este punto, no hay nada "sucio".

$usuario->email = 'test@example.com'; // ahora el correo electrónico se considera "sucio" ya que ha cambiado.
$usuario->update();
// ahora no hay datos sucios porque se han actualizado y persistido en la base de datos

$usuario->contraseña = password_hash()'nuevacontraseña'); // ahora esto es sucio
$usuario->dirty(); // no pasar nada limpiará todas las entradas sucias.
$usuario->update(); // no se actualizará nada porque no se capturó como sucio.

$usuario->dirty([ 'nombre' => 'algo', 'contraseña' => password_hash('una contraseña diferente') ]);
$usuario->update(); // se actualizarán tanto el nombre como la contraseña.
```

### Métodos de Consulta SQL
#### `select(string $campo1 [, string $campo2 ... ])`

Puedes seleccionar solo algunos de los campos en una tabla si así lo deseas (es más eficiente en tablas realmente largas con muchos campos)

```php
$usuario->select('id', 'nombre')->find();
```

#### `from(string $tabla)`

Técnicamente, ¡también podrías elegir otra tabla! ¡¿Por qué demonios no?!

```php
$usuario->select('id', 'nombre')->from('usuario')->find();
```

#### `join(string $nombre_tabla, string $condicion_join)`

Incluso puedes unirte a otra tabla en la base de datos.

```php
$usuario->join('contactos', 'contactos.id_usuario = usuarios.id')->find();
```

#### `where(string $condiciones_where)`

Puedes establecer algunos argumentos where personalizados aquí (no puedes establecer parámetros en esta declaración where)

```php
$usuario->where('id=1 AND nombre="demo"')->find();
```

**Nota de Seguridad** - Podrías sentirte tentado a hacer algo como `$usuario->where("id = '{$id}' AND nombre = '{$nombre}'")->find();`. ¡¡¡POR FAVOR NO HAGAS ESTO!!! Esto es susceptible a lo que se conoce como ataques de inyección SQL. Hay muchos artículos en línea, por favor, busca en Google "ataques de inyección SQL php" y encontrarás muchos artículos sobre este tema. La forma correcta de manejar esto con esta biblioteca es en lugar de este método `where()`, harías algo más como `$usuario->eq('id', $id)->eq('nombre', $nombre)->find();`

#### `group(string $sentencia_group_by)/groupBy(string $sentencia_group_by)`

Agrupa tus resultados por una condición particular.

```php
$usuario->select('COUNT(*) as count')->groupBy('nombre')->findAll();
```

#### `order(string $sentencia_order_by)/orderBy(string $sentencia_order_by)`

Ordena la consulta devuelta de cierta manera.

```php
$usuario->orderBy('nombre DESC')->find();
```

#### `limit(string $limite)/limit(int $inicio, int $limite)`

Limita la cantidad de registros devueltos. Si se da un segundo entero, será un desplazamiento, límite igual que en SQL.

```php
$usuario->orderby('nombre DESC')->limit(0, 10)->findAll();
```

### Condiciones WHERE
#### `equal(string $campo, mixed $valor) / eq(string $campo, mixed $valor)`

Donde `campo = $valor`

```php
$usuario->eq('id', 1)->find();
```

#### `notEqual(string $campo, mixed $valor) / ne(string $campo, mixed $valor)`

Donde `campo <> $valor`

```php
$usuario->ne('id', 1)->find();
```

#### `isNull(string $campo)`

Donde `campo IS NULL`

```php
$usuario->isNull('id')->find();
```
#### `isNotNull(string $campo) / notNull(string $campo)`

Donde `campo IS NOT NULL`

```php
$usuario->isNotNull('id')->find();
```

#### `greaterThan(string $campo, mixed $valor) / gt(string $campo, mixed $valor)`

Donde `campo > $valor`

```php
$usuario->gt('id', 1)->find();
```

#### `lessThan(string $campo, mixed $valor) / lt(string $campo, mixed $valor)`

Donde `campo < $valor`

```php
$usuario->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $campo, mixed $valor) / ge(string $campo, mixed $valor) / gte(string $campo, mixed $valor)`

Donde `campo >= $valor`

```php
$usuario->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $campo, mixed $valor) / le(string $campo, mixed $valor) / lte(string $campo, mixed $valor)`

Donde `campo <= $valor`

```php
$usuario->le('id', 1)->find();
```

#### `like(string $campo, mixed $valor) / notLike(string $campo, mixed $valor)`

Donde `campo LIKE $valor` o `campo NOT LIKE $valor`

```php
$usuario->like('nombre', 'de')->find();
```

#### `in(string $campo, array $valores) / notIn(string $campo, array $valores)`

Donde `campo IN($valor)` o `campo NOT IN($valor)`

```php
$usuario->in('id', [1, 2])->find();
```

#### `between(string $campo, array $valores)`

Donde `campo BETWEEN $valor AND $valor1`

```php
$usuario->between('id', [1, 2])->find();
```

### Relaciones
Puedes establecer varios tipos de relaciones usando esta biblioteca. Puedes establecer relaciones uno a muchos y uno a uno entre tablas. Esto requiere una configuración adicional en la clase de antemano.

Configurar el array `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como desees. El nombre del ActiveRecord probablemente es bueno. Ej .: usuario, contacto, cliente
	'registro_activo_cualquiera' => [
		// requerido
		self::HAS_ONE, // este es el tipo de relación

		// requerido
		'Algun_Clase', // este es la clase ActiveRecord "otra" que hará referencia a esta

		// requerido
		'clave_local', // esta es la clave local que hace referencia a la unión.
		// solo por tu información, esto también se une solo a la clave principal del modelo "otro"

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que deseas ejecutar. [] si no quieres ninguno.

		// opcional
		'nombre_referencia_inversa' // esto es si deseas referenciar esta relación nuevamente a sí misma Ej .: $usuario->contacto->usuario;
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
		'usuario_con_referencia' => [ self::BELONGS_TO, Usuario::class, 'id_usuario', [], 'contacto' ],
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

// encuentra el usuario más reciente.
$usuario->notNull('id')->orderBy('id desc')->find();

// obtén contactos usando la relación:
foreach($usuario->contactos as $contacto) {
	echo $contacto->id;
}

// o podemos ir en la otra dirección.
$contacto = new Contacto();

// encuentra un contacto
$contacto->find();

// obtén un usuario usando la relación:
echo $contacto->usuario->nombre; // este es el nombre del usuario
```

Bastante genial ¿verdad?

### Establecimiento de Datos Personalizados
A veces es posible que necesites adjuntar algo único a tu ActiveRecord, como un cálculo personalizado que podría ser más fácil simplemente adjuntar al objeto que luego se pasaría, digamos, a una plantilla.

#### `setCustomData(string $campo, mixed $valor)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$usuario->setCustomData('cantidad_vistas_pagina', $cantidad_vistas_pagina);
```

Y luego simplemente lo referencia como una propiedad de objeto normal.

```php
echo $usuario->cantidad_vistas_pagina;
```

### Eventos

Otra característica súper increíble de esta biblioteca se trata de eventos. Los eventos se activan en ciertos momentos basados en ciertos métodos que llamas. Son muy útiles para configurar datos automáticamente.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Esto es realmente útil si necesitas configurar una conexión predeterminada o algo así.

```php
// index.php o bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
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

Probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeFind(self $self) {
		// siempre ejecuta id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Probablemente sea más útil si siempre necesitas ejecutar alguna lógica cada vez que se recupera este registro. ¿Necesitas descifrar algo? ¿Necesitas ejecutar una consulta de conteo personalizada cada vez (no eficiente pero lo que sea)?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function afterFind(self $self) {
		// descifrando algo
		$self->secreto = yourDecryptFunction($self->secreto, $some_key);

		// tal vez almacenar algo custom como una consulta???
		$self->setCustomData('contador_vistas', $self->select('COUNT(*) count')->from('vistas_usuario')->eq('id_usuario', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Probablemente solo sea útil si necesitas una manipulación de consulta cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeFindAll(self $self) {
		// siempre ejecuta id >= 0 si eso es lo tuyo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar a `afterFind()` ¡pero puedes hacerlo con todos los registros en su lugar!

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

```es
	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// haz algo genial como después de encontrar()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas algunos valores predeterminados configurados cada vez.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
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

¿Tal vez tienes un caso de uso para cambiar datos después de que se inserten?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// haz tu cosa
		Flight::cache()->set('id_insercion_mas_reciente', $self->id);
		// o lo que sea....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas algunos valores predeterminados configurados cada vez que se actualice.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeInsert(self $self) {
		// establece algunos valores predeterminados sólidos
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

¿Tal vez tienes un caso de uso para cambiar datos después de que se actualicen?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// haz tu cosa
		Flight::cache()->set('id_usuario_mas_recientemente_actualizado', $self->id);
		// o lo que sea....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Esto es útil si deseas que los eventos ocurran tanto cuando se insertan como cuando se actualizan. Te ahorraré la larga explicación, pero seguro puedes adivinar qué es.

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

¡No estoy seguro de qué querrías hacer aquí, pero no hay juicios aquí! ¡Adelante!

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

## Contribución

Por favor.

### Configuración

Cuando contribuyas, asegúrate de ejecutar `composer test-coverage` para mantener una cobertura de prueba del 100% (esto no es una cobertura de prueba unitaria real, más bien como pruebas de integración).

También asegúrate de ejecutar `composer beautify` y `composer phpcs` para corregir cualquier error de linting.

## Licencia

MIT
```