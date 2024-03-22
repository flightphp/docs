# ActiveRecord do Flight

Um active record mapeia uma entidade de banco de dados para um objeto PHP. Falando simplesmente, se você tem uma tabela de usuários em seu banco de dados, você pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` em seu código. Veja o [exemplo básico](#exemplo-básico).

## Exemplo Básico

Vamos assumir que você tenha a seguinte tabela:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Agora você pode configurar uma nova classe para representar essa tabela:

```php
/**
 * Uma classe ActiveRecord geralmente é singular
 * 
 * É altamente recomendável adicionar as propriedades da tabela como comentários aqui
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// você pode configurá-lo desta forma
		parent::__construct($database_connection, 'users');
		// ou desta forma
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Agora veja a mágica acontecer!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usaria uma conexão real com o banco de dados

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// ou mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// ou mysqli com criação não baseada em objeto
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('alguma senha legal');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('outra senha legal!!!');
$user->insert();
// não é possível usar $user->save() aqui ou ele pensará que é uma atualização!

echo $user->id; // 2
```

E foi tão fácil adicionar um novo usuário! Agora que há uma linha de usuário no banco de dados, como você a extrai?

```php
$user->find(1); // encontra id = 1 no banco de dados e o retorna.
echo $user->name; // 'Bobby Tables'
```

E se você quiser encontrar todos os usuários?

```php
$users = $user->findAll();
```

E com uma condição específica?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Veja como isso é divertido? Vamos instalar e começar!

## Instalação

Simplesmente instale com o Composer

```php
composer require flightphp/active-record 
```

## Uso

Isso pode ser usado como uma biblioteca independente ou com o Flight PHP Framework. Completamente com você.

### Independente
Apenas certifique-se de passar uma conexão PDO para o construtor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usaria uma conexão real com o banco de dados

$User = new User($pdo_connection);
```

### Flight PHP Framework
Se você está usando o Flight PHP Framework, pode registrar a classe ActiveRecord como um serviço (mas honestamente, você não precisa).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// então você pode usá-lo assim em um controlador, uma função, etc.

Flight::user()->find(1);
```

## Funções CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encontra um registro e o atribui ao objeto atual. Se você passar um `$id` de algum tipo, ele realizará uma consulta na chave primária com esse valor. Se nada for passado, ele simplesmente encontrará o primeiro registro na tabela.

Além disso, você pode passar outros métodos auxiliares para consultar sua tabela.

```php
// encontrar um registro com algumas condições antecipadas
$user->notNull('password')->orderBy('id DESC')->find();

// encontrar um registro por um id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encontra todos os registros na tabela que você especificar.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Retorna `true` se o registro atual foi recuperado (buscado no banco de dados).

```php
$user->find(1);
// se um registro for encontrado com dados...
$user->isHydrated(); // verdadeiro
```

#### `insert(): boolean|ActiveRecord`

Insere o registro atual no banco de dados.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Atualiza o registro atual no banco de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'teste@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Insere ou atualiza o registro atual no banco de dados. Se o registro tiver um id, ele será atualizado, caso contrário, será inserido.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Se você tiver relacionamentos definidos na classe, ele salvará recursivamente essas relações também se forem definidas, instanciadas e tiverem dados para atualizar. (v0.4.0 e acima)

#### `delete(): boolean`

Exclui o registro atual do banco de dados.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Você também pode excluir vários registros executando uma pesquisa antes.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array $dirty = []): ActiveRecord`

Dados "dirty" se referem aos dados que foram alterados em um registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "dirty" até este ponto.

$user->email = 'teste@example.com'; // agora o email é considerado "dirty" pois foi alterado.
$user->update();
// agora não há dados "dirty" porque foram atualizados e persistidos no banco de dados

$user->password = password_hash()'novasenha'); // agora isso está "dirty"
$user->dirty(); // passar nada limpará todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi capturado como dirty.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('uma senha diferente') ]);
$user->update(); // tanto o nome quanto a senha são atualizados.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Este é um alias para o método `dirty()`. É um pouco mais claro o que você está fazendo.

```php
$user->copyFrom([ 'name' => 'algo', 'password' => password_hash('uma senha diferente') ]);
$user->update(); // tanto o nome quanto a senha são atualizados.
```

#### `isDirty(): boolean` (v0.4.0)

Retorna `true` se o registro atual foi alterado.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'teste@email.com';
$user->isDirty(); // verdadeiro
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Redefine o registro atual para seu estado inicial. Isso é realmente bom de usar em comportamentos de loop.
Se você passar `true`, ele também redefinirá os dados da consulta que foram usados para encontrar o objeto atual (comportamento padrão).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // comece com uma base limpa
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Depois de rodar um método `find()`, `findAll()`, `insert()`, `update()` ou `save()`, você pode obter o SQL que foi construído e usá-lo para fins de depuração.

## Métodos de Consulta SQL

#### `select(string $campo1 [, string $campo2 ... ])`

Você pode selecionar apenas algumas das colunas em uma tabela se desejar (é mais eficiente em tabelas realmente largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $tabela)`

Você também pode escolher outra tabela! Por que diabos não?

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $nome_tabela, string $condição_join)`

Você também pode juntar-se a outra tabela no banco de dados.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $condições_where)`

Você pode definir algumas condições where personalizadas (você não pode definir parâmetros nesta declaração where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Segurança** - Você pode ser tentado a fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. POR FAVOR, NÃO FAÇA ISSO!!! Isso é suscetível ao que é conhecido como ataques de injeção de SQL. Há muitos artigos online, por favor, pesquise "SQL injection attacks php" e você encontrará muitos artigos sobre esse assunto. A maneira correta de lidar com isso com esta biblioteca é, em vez deste método `where()`, você faria algo mais como `$user->eq('id', $id)->eq('name', $name)->find();` Se você absolutamente tiver que fazer isso, a biblioteca `PDO` possui `$pdo->quote($var)` para escapar isso para você. Somente após usar `quote()` você pode usá-lo em uma instrução `where()`.

#### `group(string $grupo_por_declaração)/groupBy(string $grupo_por_declaração)`

Agrupe seus resultados por uma condição específica.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Ordene a consulta retornada de uma maneira específica.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite a quantidade de registros retornados. Se um segundo int for dado, ele será offset, limit igual ao SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## Condições WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Onde `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Onde `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Onde `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Onde `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Onde `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Onde `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Onde `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Onde `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Onde `field LIKE $value` ou `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Onde `field IN($value)` ou `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Onde `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

## Relacionamentos
Você pode definir vários tipos de relacionamentos usando esta biblioteca. Você pode definir relacionamentos um->muitos e um->um entre tabelas. Isso requer uma configuração um pouco mais detalhada na classe antecipadamente.

Configurar o array `$relations` não é difícil, mas adivinhar a sintaxe correta pode ser confuso.

```php

protected array $relations = [
	// você pode nomear a chave como quiser. O nome do ActiveRecord provavelmente é bom. Ex: user, contact, client
	'user' => [
		// obrigatório
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // este é o tipo de relacionamento

		// obrigatório
		'Some_Class', // este é a classe ActiveRecord "outro" que será referenciada

		// necessário
		// dependendo do tipo de relacionamento
		// self::HAS_ONE = a chave estrangeira que faz referência à junção
		// self::HAS_MANY = a chave estrangeira que faz referência à junção
		// self::BELONGS_TO = a chave local que faz referência à associação
		'local_or_foreign_key',
		// só para avisar, isso também se junta à chave primária do modelo "outro"

		// opcional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condições adicionais que você deseja ao unir a relação
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'back_reference_name' // isso é se você quiser referenciar esse relacionamento de volta a si mesmo Ex: $user->contact->user;
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

Agora que as referências estão configuradas, você pode usá-las muito facilmente!

```php
$user = new User($pdo_connection);

// encontrar o usuário mais recente.
$user->notNull('id')->orderBy('id desc')->find();

// obter contatos usando a relação:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou podemos fazer o contrário.
$contact = new Contact();

// encontrar um contato
$contact->find();

// obter usuário usando a relação:
echo $contact->user->name; // este é o nome do usuário
```

Bem legal, né?

## Definindo Dados Personalizados
Às vezes, você pode precisar anexar algo único ao seu ActiveRecord, como um cálculo personalizado que pode ser mais fácil de apenas anexar ao objeto e depois passar para um modelo, por exemplo.

#### `setCustomData(string $field, mixed $value)`
Você anexa os dados personalizados com o método `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

E então você simplesmente o referencia como uma propriedade normal do objeto.

```php
echo $user->page_view_count;
```

## Eventos

Outra funcionalidade super incrível sobre esta biblioteca é sobre eventos. Os eventos são acionados em momentos específicos com base em certos métodos que você chama. Eles são muito, muito úteis para configurar dados automaticamente para você.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Isso é realmente útil se você precisar configurar uma conexão padrão ou algo assim.

```php
// index.php or bootstrap.php```markdown
# ActiveRecord do Flight

Um active record está mapeando uma entidade de banco de dados para um objeto PHP. Falando claramente, se você tiver uma tabela de usuários em seu banco de dados, você pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` em sua base de código. Veja o [exemplo básico](#exemplo-básico).

## Exemplo Básico

Vamos assumir que você tenha a seguinte tabela:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Agora você pode configurar uma nova classe para representar essa tabela:

```php
/**
 * Uma classe ActiveRecord geralmente é singular
 * 
 * É altamente recomendável adicionar as propriedades da tabela como comentários aqui
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// você pode configurá-lo desta forma
		parent::__construct($database_connection, 'users');
		// ou desta forma
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Agora veja a mágica acontecer!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usaria uma conexão real com o banco de dados

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// ou mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// ou mysqli com criação não baseada em objeto
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('alguma senha legal');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('outra senha legal!!!');
$user->insert();
// não é possível usar $user->save() aqui ou ele pensará que é uma atualização!

echo $user->id; // 2
```

E foi tão fácil adicionar um novo usuário! Agora que há uma linha de usuário no banco de dados, como você a extrai?

```php
$user->find(1); // encontra id = 1 no banco de dados e o retorna.
echo $user->name; // 'Bobby Tables'
```

E se você quiser encontrar todos os usuários?

```php
$users = $user->findAll();
```

E com uma condição específica?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Veja como isso é divertido? Vamos instalar e começar!

## Instalação

Simplesmente instale com o Composer

```php
composer require flightphp/active-record 
```

## Uso

Isso pode ser usado como uma biblioteca independente ou com o Flight PHP Framework. Completamente com você.

### Independente
Apenas certifique-se de passar uma conexão PDO para o construtor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usaria uma conexão real com o banco de dados

$User = new User($pdo_connection);
```

### Flight PHP Framework
Se você está usando o Flight PHP Framework, pode registrar a classe ActiveRecord como um serviço (mas honestamente, você não precisa).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// então você pode usá-lo assim em um controlador, uma função, etc.

Flight::user()->find(1);
```

## Funções CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encontra um registro e o atribui ao objeto atual. Se você passar um `$id` de algum tipo, ele realizará uma consulta na chave primária com esse valor. Se nada for passado, ele simplesmente encontrará o primeiro registro na tabela.

Além disso, você pode passar outros métodos auxiliares para consultar sua tabela.

```php
// encontrar um registro com algumas condições antecipadas
$user->notNull('password')->orderBy('id DESC')->find();

// encontrar um registro por um id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encontra todos os registros na tabela que você especificar.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Retorna `true` se o registro atual foi recuperado (buscado no banco de dados).

```php
$user->find(1);
// se um registro for encontrado com dados...
$user->isHydrated(); // verdadeiro
```

#### `insert(): boolean|ActiveRecord`

Insere o registro atual no banco de dados.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Atualiza o registro atual no banco de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'teste@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Insere ou atualiza o registro atual no banco de dados. Se o registro tiver um id, ele será atualizado, caso contrário, será inserido.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Se você tiver relacionamentos definidos na classe, ele salvará recursivamente essas relações também se forem definidas, instanciadas e tiverem dados para atualizar. (v0.4.0 e acima)

#### `delete(): boolean`

Exclui o registro atual do banco de dados.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Você também pode excluir vários registros executando uma pesquisa antes.

```php
$user->like('name', 'Bob%')->delete();
```
...
```