# FlightPHP Active Record

Un active record es mapear una entidad de base de datos a un objeto PHP. Hablando claramente, si tienes una tabla de usuarios en tu base de datos, puedes "traducir" una fila en esa tabla a una clase `User` y a un objeto `$user` en tu base de código. Ver [ejemplo básico](#ejemplo-básico).

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
 * Una clase ActiveRecord generalmente es singular
 * 
 * Se recomienda encarecidamente agregar las propiedades de la tabla como comentarios aquí
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($conexion_base_datos)
	{
		// puedes configurarlo de esta manera
		parent::__construct($conexion_base_datos, 'users');
		// o de esta manera
		parent::__construct($conexion_base_datos, null, [ 'table' => 'users']);
	}
}
```

¡Ahora observa cómo sucede la magia!

```php
// para sqlite
$conexion_base_datos = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarás una conexión de base de datos real

// para mysql
$conexion_base_datos = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'usuario', 'contraseña');

// o mysqli
$conexion_base_datos = new mysqli('localhost', 'usuario', 'contraseña', 'test_db');
// o mysqli con creación que no sea basada en objetos
$conexion_base_datos = mysqli_connect('localhost', 'usuario', 'contraseña', 'test_db');

$user = new User($conexion_base_datos);
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

¡Y así de fácil fue agregar un nuevo usuario! Ahora que hay una fila de usuario en la base de datos, ¿cómo la sacamos?

```php
$user->find(1); // encuentra id = 1 en la base de datos y devuélvelo.
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

¿Ves qué divertido es esto? ¡Vamos a instalarlo y comenzar!

## Instalación

Simplemente instala con Composer

```php
composer require flightphp/active-record 
```

## Uso

Esto puede ser utilizado como una biblioteca independiente o con el Marco de PHP Flight. Totalmente depende de ti.

### Independiente
Solo asegúrate de pasar una conexión PDO al constructor.

```php
$conexion_pdo = new PDO('sqlite:test.db'); // esto es solo un ejemplo, probablemente usarás una conexión de base de datos real

$Usuario = new User($conexion_pdo);
```

### Marco de PHP Flight
Si estás utilizando el Marco de PHP Flight, puedes registrar la clase ActiveRecord como un servicio (pero honestamente no es necesario).

```php
Flight::register('usuario', 'Usuario', [ $conexion_pdo ]);

// luego puedes usarlo así en un controlador, una función, etc.

Flight::usuario()->find(1);
```

## Referencia de API
### Funciones CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encuentra un registro y asígnalo al objeto actual. Si pasas un `$id` de algún tipo, realizará una búsqueda en la clave principal con ese valor. Si no se pasa nada, solo encontrará el primer registro en la tabla.

Además, puedes pasarle otros métodos auxiliares para consultar tu tabla.

```php
// busca un registro con algunas condiciones de antemano
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

Los datos sucios se refieren a los datos que han sido cambiados en un registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sucio" en este momento.

$user->email = 'test@example.com'; // ahora el correo electrónico se considera "sucio" ya que ha cambiado.
$user->update();
// ahora no hay datos sucios porque se han actualizado y persistido en la base de datos

$user->password = password_hash()'nuevacontraseña'); // ahora esto está sucio
$user->dirty(); // pasar nada limpiará todas las entradas sucias.
$user->update(); // nada se actualizará porque no se capturó nada como sucio.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('una contraseña diferente') ]);
$user->update(); // tanto el nombre como la contraseña se actualizan.
```

### Métodos de Consulta SQL
#### `select(string $campo1 [, string $campo2 ... ])`

Puedes seleccionar solo algunos de los campos en una tabla si lo deseas (es más eficiente en tablas realmente anchas con muchas columnas).

```php
$user->select('id', 'name')->find();
```

#### `from(string $tabla)`

Técnicamente también puedes elegir otra tabla. ¿Por qué no?

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $nombre_tabla, string $condicion_unión)`

Incluso puedes unirte a otra tabla en la base de datos.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $condiciones_where)`

Puedes establecer algunos argumentos where personalizados (no puedes establecer parámetros en esta declaración where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de seguridad** - Podrías sentirte tentado a hacer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ¡Por favor NO HAGAS ESTO! ¡Esto es susceptible a lo que se conoce como ataques de inyección SQL. Hay muchos artículos en línea, por favor busca en Google "ataques de inyección sql php" y encontrarás muchos artículos sobre este tema. La forma correcta de manejar esto con esta biblioteca es en lugar de este método `where()`, harías algo más como `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $declaración_group_by)/groupBy(string $declaración_group_by)`

Agrupa tus resultados por una condición particular.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $declaración_order_by)/orderBy(string $declaración_order_by)`

Ordena la consulta devuelta de cierta manera.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $límite)/limit(int $desplazamiento, int $límite)`

Limita la cantidad de registros devueltos. Si se da un segundo entero, será desplazamiento, el límite como en SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### Condiciones WHERE
#### `igual(string $campo, mixed $valor) / eq(string $campo, mixed $valor)`

Donde `campo = $valor`

```php
$user->eq('id', 1)->find();
```

#### `noIgual(string $campo, mixed $valor) / ne(string $campo, mixed $valor)`

Donde `campo <> $valor`

```php
$user->ne('id', 1)->find();
```

#### `esNulo(string $campo)`

Donde `campo IS NULL`

```php
$user->isNull('id')->find();
```
#### `noNulo(string $campo) / notNull(string $campo)`

Donde `campo IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `mayorQue(string $campo, mixed $valor) / gt(string $campo, mixed $valor)`

Donde `campo > $valor`

```php
$user->gt('id', 1)->find();
```

#### `menorQue(string $campo, mixed $valor) / lt(string $campo, mixed $valor)`

Donde `campo < $valor`

```php
$user->lt('id', 1)->find();
```
#### `mayorIgualQue(string $campo, mixed $valor) / ge(string $campo, mixed $valor) / gte(string $campo, mixed $valor)`

Donde `campo >= $valor`

```php
$user->ge('id', 1)->find();
```
#### `menorIgualQue(string $campo, mixed $valor) / le(string $campo, mixed $valor) / lte(string $campo, mixed $valor)`

Donde `campo <= $valor`

```php
$user->le('id', 1)->find();
```

#### `como(string $campo, mixed $valor) / notLike(string $campo, mixed $valor)`

Donde `campo LIKE $valor` o `campo NOT LIKE $valor`

```php
$user->like('name', 'de')->find();
```

#### `en(string $campo, array $valores) / notIn(string $campo, array $valores)`

Donde `campo IN($valor)` o `campo NOT IN($valor)`

```php
$user->in('id', [1, 2])->find();
```

#### `entre(string $campo, array $valores)`

Donde `campo BETWEEN $valor AND $valor1`

```php
$user->between('id', [1, 2])->find();
```

### Relaciones
Puedes establecer varios tipos de relaciones usando esta biblioteca. Puedes establecer relaciones uno a muchos y uno a uno entre tablas. Esto requiere una configuración adicional en la clase de antemano.

Configurar el array `$relations` no es difícil, pero adivinar la sintaxis correcta puede ser confuso.

```php
protected array $relations = [
	// puedes nombrar la clave como desees. El nombre del ActiveRecord probablemente es bueno. Por ejemplo: usuario, contacto, cliente
	'cualquier_active_record' => [
		// requerido
		self::HAS_ONE, // este es el tipo de relación

		// requerido
		'Alguna_Clase', // esta es la clase ActiveRecord "otra" a la que hará referencia

		// requerido
		'clave_local', // esta es la clave local que hace referencia a la unión.
		// solo como información, esto también se une solo a la clave principal del modelo "otro"

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que deseas ejecutar. [] si no quieres ninguno.

		// opcional
		'nombre_referencia_inversa' // esto es si deseas hacer referencia inversa a esta relación de vuelta a sí misma Ej: $user->contact->user;
	];
]
```

```php
class Usuario extends ActiveRecord{
	protected array $relations = [
		'contactos' => [ self::HAS_MANY, Contact::class, 'id_usuario' ],
		'contacto' => [ self::HAS_ONE, Contact::class, 'id_usuario' ],
	];

	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}
}

class Contacto extends ActiveRecord{
	protected array $relations = [
		'usuario' => [ self::BELONGS_TO, Usuario::class, 'id_usuario' ],
		'usuario_con_referencia_inversa' => [ self::BELONGS_TO, Usuario::class, 'id_usuario', [], 'contacto' ],
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

// obtener contactos usando relación:
foreach($usuario->contactos as $contacto) {
	echo $contacto->id;
}

// o podemos ir en la otra dirección.
$contacto = new Contacto();

// encontrar un contacto
$contacto->find();

// obtener usuario usando relación:
echo $contacto->usuario->name; // este es el nombre de usuario
```

¡Bastante genial, ¿verdad?

### Configuración de Datos Personalizados
A veces es posible que debas adjuntar algo único a tu ActiveRecord, como un cálculo personalizado que podría ser más fácil simplemente adjuntarlo al objeto que luego se pasaría a un template.

#### `setCustomData(string $field, mixed $value)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Y luego simplemente lo haces referencia como una propiedad normal del objeto.

```php
echo $user->page_view_count;
```

### Eventos

Otra característica súper impresionante de esta biblioteca son los eventos. Los eventos se activan en ciertos momentos basados en ciertos métodos que llamas. Son muy, muy útiles para configurar datos automáticamente.

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
		$config['connection'] = Flight::db();
		// o esto
		$self->transformAndPersistConnection(Flight::db());
		
		// También puedes establecer el nombre de la tabla de esta manera.
		$config['table'] = 'usuarios';
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
		// siempre ejecutar id >= 0 si eso te gusta
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Probablemente sea más útil si siempre necesitas ejecutar alguna lógica cada vez que se obtiene este registro. ¿Necesitas descifrar algo? ¿Necesitas ejecutar una consulta de recuento personalizada cada vez (no es eficiente pero lo que sea)?

```php
class Usuario extends flight\ActiveRecord {
	
	public function __construct($conexion_base_datos)
	{
		parent::__construct($conexion_base_datos, 'usuarios');
	}

	protected function afterFind(self $self) {
		// descifrando algo
		$self->secreto = tuFuncionDescifrar($self->secreto, $algunafuncionclave);

		// tal vez almacenar algo personalizado como una consulta???
		$self->setCustomData('vistas', $self->select('COUNT(*) count')->from('vistas_usuario')->eq('id_usuario', $self->id)['count']; 
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
		// siempre ejecutar id >= 0 si eso te gusta
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

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// hacer algo genial como en afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil si necesitas establecer algunos valores predeterminados cada vez.

```php
class Usuario```markdown
### Configuración de Datos Personalizados
A veces es posible que debas adjuntar algo único a tu ActiveRecord, como un cálculo personalizado que podría ser más fácil simplemente adjuntarlo al objeto que luego se pasaría a un template.

#### `setCustomData(string $field, mixed $value)`
Adjuntas los datos personalizados con el método `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Y luego simplemente lo haces referencia como una propiedad normal del objeto.

```php
echo $user->page_view_count;
```

### Eventos

Otra característica súper impresionante de esta biblioteca son los eventos. Los eventos se activan en ciertos momentos basados en ciertos métodos que llamas. Son muy, muy útiles para configurar datos automáticamente.

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
		$config['connection'] = Flight::db();
		// o esto
		$self->transformAndPersistConnection(Flight::db());
		
		// También puedes establecer el nombre de la tabla de esta manera.
		$config['table'] = 'usuarios';
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
		// siempre ejecutar id >= 0 si eso te gusta
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Probablemente sea más útil si siempre necesitas ejecutar alguna lógica cada vez que se obtiene este registro. ¿Necesitas descifrar algo? ¿Necesitas ejecutar una consulta de recuento personalizada cada vez (no es eficiente pero lo que sea)?

```php
class Usuario extends flight\ActiveRecord {
	
public function __construct($conexion_base_datos)
{
	parent::__construct($conexion_base_datos, 'usuarios');
}

protected function afterFind(self $self) {
	// descifrando algo
	$self->secreto = tuFuncionDescifrar($self->secreto, $algunafuncionclave);

	// tal vez almacenar algo personalizado como una consulta???
	$self->setCustomData('vistas', $self->select('COUNT(*) count')->from('vistas_usuario')->eq('id_usuario', $self->id)['count']; 
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
	// siempre ejecutar id >= 0 si eso te gusta
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

protected function afterFindAll(array $results) {

	foreach($results as $self) {
		// hacer algo genial como en afterFind()
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

¿Quizás tienes un caso de uso para cambiar los datos después de que se insertan?

```php
class Usuario extends flight\ActiveRecord {
	
public function __construct($conexion_base_datos)
{
	parent::__construct($conexion_base_datos, 'usuarios');
}

protected function afterInsert(self $self) {
	// ¡tú haces lo tuyo!
	Flight::cache()->set('ultimo_id_insertado', $self->id);
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

protected function beforeInsert(self $self) {
	// establecer algunos valores predeterminados
	if(!$self->updated_date) {
		$self->updated_date = gmdate('Y-m-d');
	}
} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

¿Quizás tienes un caso de uso para cambiar datos después de que se actualizan?

```php
class Usuario extends flight\ActiveRecord {
	
public function __construct($conexion_base_datos)
{
	parent::__construct($conexion_base_datos, 'usuarios');
}

protected function afterInsert(self $self) {
	// tú haces lo tuyo
	Flight::cache()->set('usuario_actualizado_recientemente_id', $self->id);
	// o lo que sea....
} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Esto es útil si deseas que los eventos sucedan tanto cuando se insertan como cuando se actualizan. Te ahorraré la larga explicación, pero estoy seguro de que puedes adivinar de qué se trata.

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

¡No estoy seguro de qué querrías hacer aquí, pero sin juicios aquí! ¡Adelante!

```php
class Usuario extends flight\ActiveRecord {
	
public function __construct($conexion_base_datos)
{
	parent::__construct($conexion_base_datos, 'usuarios');
}

protected function beforeDelete(self $self) {
	echo 'Fue un valiente soldado... :cry-face:';
} 
}
```

## Contribuir

Por favor.

### Configuración

Cuando contribuyas, asegúrate de ejecutar `composer test-coverage` para mantener una cobertura de pruebas del 100% (esto no es una verdadera cobertura de pruebas unitarias, más bien como pruebas de integración).

También asegúrate de ejecutar `composer beautify` y `composer phpcs` para corregir cualquier error de formato.

## Licencia

MIT
```