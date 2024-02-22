# FlightPHP Registo Ativo

Um registo ativo está a mapear uma entidade da base de dados para um objeto PHP. Falando claramente, se tiver uma tabela de utilizadores na sua base de dados, pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` no seu código-fonte. Veja [exemplo básico](#exemplo-básico).

## Exemplo Básico

Vamos assumir que tem a seguinte tabela:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Agora pode configurar uma nova classe para representar esta tabela:

```php
/**
 * Uma classe ActiveRecord é geralmente singular
 * 
 * É altamente recomendado adicionar as propriedades da tabela como comentários aqui
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// pode definir desta forma
		parent::__construct($database_connection, 'users');
		// ou desta forma
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Agora veja a mágica acontecer!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // isto é apenas um exemplo, provavelmente usaria uma conexão real à base de dados

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// ou mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// ou mysqli com criação não baseada em objetos
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('uma palavra-passe fixe');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('uma palavra-passe fixe novamente!!!');
$user->insert();
// não é possível usar $user->save() aqui senão pensará que é uma atualização!

echo $user->id; // 2
```

E foi tão fácil adicionar um novo utilizador! Agora que há uma linha de utilizador na base de dados, como pode retirá-la?

```php
$user->find(1); // encontrar id = 1 na base de dados e devolver
echo $user->name; // 'Bobby Tables'
```

E se quiser encontrar todos os utilizadores?

```php
$users = $user->findAll();
```

E com uma determinada condição?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Vê o quão divertido isto é? Vamos instalar e começar!

## Instalação

Simplesmente instale com o Composer

```php
composer require flightphp/active-record 
```

## Utilização

Isto pode ser usado como uma biblioteca independente ou com o Framework PHP Flight. Completamente à sua escolha.

### Independente
Apenas certifique-se de passar uma conexão PDO para o construtor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // isto é apenas um exemplo, provavelmente usaria uma conexão real à base de dados

$User = new User($pdo_connection);
```

### Framework PHP Flight
Se estiver a usar o Framework PHP Flight, pode registar a classe ActiveRecord como um serviço (mas honestamente não precisa).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// depois pode usá-lo desta forma num controlador, numa função, etc.

Flight::user()->find(1);
```

## Referência da API
### Funções CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encontre um registo e atribua-o ao objeto atual. Se passar um `$id` de algum tipo, irá efetuar uma pesquisa na chave primária com esse valor. Se nada for passado, irá simplesmente encontrar o primeiro registo na tabela.

Adicionalmente pode passar outros métodos auxiliares para consultar a sua tabela.

```php
// encontrar um registo com algumas condições de antemão
$user->notNull('password')->orderBy('id DESC')->find();

// encontrar um registo por um id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encontra todos os registos na tabela que especificar.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Insere o registo atual na base de dados.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Atualiza o registo atual na base de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

Apaga o registo atual da base de dados.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Também pode apagar múltiplos registos executando uma pesquisa antes.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Dados sujos referem-se aos dados que foram alterados num registo.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sujo" até este ponto.

$user->email = 'test@example.com'; // agora o email é considerado "sujo" porque foi alterado.
$user->update();
// agora não há dados sujos porque foram atualizados e persistidos na base de dados

$user->password = password_hash()'nova palavra-passe'); // agora isto está sujo
$user->dirty(); // não passar nada limpará todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi capturado como sujo.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('uma palavra-passe diferente') ]);
$user->update(); // ambos name e password são atualizados.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Reinicia o registo atual para o seu estado inicial. Isto é realmente útil de usar em comportamentos de loop.
Se passar `true`, também irá repor os dados de consulta que foram usados para encontrar o objeto atual (comportamento padrão).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // começar com uma tela limpa
	$user_company->user_id = $user->id;
	$user_company->company_id = $algum_id_empresa;
	$user_company->insert();
}
```

### Métodos de Consulta SQL
#### `select(string $campo1 [, string $campo2 ... ])`

Pode selecionar apenas alguns dos campos de uma tabela se desejar (é mais eficiente em tabelas muito largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $tabela)`

Pode tecnicamente escolher outra tabela também! Por que não?!

```php
$user->select('id', 'name')->from(`user`)->find();
```

#### `join(string $nome_tabela, string $condição_junção)`

Pode até mesmo juntar-se a outra tabela na base de dados.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $condições_where)`

Pode definir algumas condições where personalizadas (não pode definir parâmetros nesta declaração where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Segurança** - Pode ser tentado fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Por favor, NÃO FAÇA ISTO!!! Isto é suscetível ao que se conhece como ataques de injeção SQL. Existem muitos artigos online, por favor, procure "ataques de injeção sql php" e encontrará muitos artigos sobre este assunto. A forma correta de lidar com isto com esta biblioteca é em vez deste método `where()`, faria algo como `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupe os seus resultados por uma determinada condição.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Ordene a query retornada de uma certa maneira.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limite)/limit(int $deslocamento, int $limite)`

Limite a quantidade de registos retornados. Se for dado um segundo int, será deslocamento, limite tal como em SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### Condições WHERE
#### `equal(string $campo, mixed $valor) / eq(string $campo, mixed $valor)`

Onde `campo = $valor`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $campo, mixed $valor) / ne(string $campo, mixed $valor)`

Onde `campo <> $valor`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $campo)`

Onde `campo IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $campo) / notNull(string $campo)`

Onde `campo IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $campo, mixed $valor) / gt(string $campo, mixed $valor)`

Onde `campo > $valor`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $campo, mixed $valor) / lt(string $campo, mixed $valor)`

Onde `campo < $valor`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $campo, mixed $valor) / ge(string $campo, mixed $valor) / gte(string $campo, mixed $valor)`

Onde `campo >= $valor`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $campo, mixed $valor) / le(string $campo, mixed $valor) / lte(string $campo, mixed $valor)`

Onde `campo <= $valor`

```php
$user->le('id', 1)->find();
```

#### `like(string $campo, mixed $valor) / notLike(string $campo, mixed $valor)`

Onde `campo LIKE $valor` ou `campo NOT LIKE $valor`

```php
$user->like('name', 'de')->find();
```

#### `in(string $campo, array $valores) / notIn(string $campo, array $valores)`

Onde `campo IN($valor)` ou `campo NOT IN($valor)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $campo, array $valores)`

Onde `campo BETWEEN $valor AND $valor1`

```php
$user->between('id', [1, 2])->find();
```

### Relacionamentos
Pode configurar vários tipos de relacionamentos utilizando esta biblioteca. Pode configurar relacionamentos de um-para-muitos e de um-para-um entre tabelas. Isto requer uma configuração extra na classe antecipadamente.

Configurar o array `$relations` não é difícil, mas adivinhar a sintaxe correta pode ser confuso.

```php
protected array $relations = [
	// pode nomear a chave como quiser. O nome do ActiveRecord é provavelmente bom. Por exemplo: utilizador, contacto, cliente
	'active_record_qualquer' => [
		// requerido
		self::HAS_ONE, // este é o tipo de relacionamento

		// requerido
		'Some_Class', // este é a classe "outro" ActiveRecord a que isto fará referência

		// requerido
		'chave_local', // esta é a chave local que faz referência à junção.
		// apenas a título de informação, isto também apenas se junta à chave primária do "outro" modelo

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que deseja executar. [] se não desejar nenhum.

		// opcional
		'nome_referência_inversa' // isto é se deseja referência inversa a esta relação de volta para si mesma. Por exemplo: $user->contact->user;
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

Agora temos as referências configuradas para podermos usá-las muito facilmente!

```php
$user = new User($pdo_connection);

// encontrar o utilizador mais recente.
$user->notNull('id')->orderBy('id desc')->find();

// obter contactos usando a relação:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou podemos ir pelo outro caminho.
$contact = new Contact();

// encontrar um contacto
$contact->find();

// obter utilizador usando a relação:
echo $contact->user->name; // este é o nome do utilizador
```

Bastante fixe, não é?

### Definindo Dados Personalizados
Por vezes pode precisar de anexar algo único ao seu ActiveRecord, como um cálculo personalizado que pode ser mais fácil de anexar ao objeto e que será passado a um modelo.

#### `setCustomData(string $campo, mixed $valor)`
Anexa os dados personalizados com o método `setCustomData()`.
```php
$user->setCustomData('contagem_visitas_página', $contagem_visitas_página);
```

E então apenas o refira como uma propriedade normal do objeto.

```php
echo $user->contagem_visitas_página;
```

### Eventos

Uma outra funcionalidade fantástica desta biblioteca é sobre eventos. Os eventos são disparados em determinados momentos baseados em certos métodos que chama. São muito muito úteis para configurar dados automaticamente.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Isto é muito útil se precisar de definir uma conexão padrão ou algo assim.

```php
// index.php ou bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // não se esqueça da referência &
		// poderia fazer isto para definir automaticamente a conexão
		$config['connection'] = Flight::db();
		// ou isto
		$self->transformAndPersistConnection(Flight::db());
		
		// Também pode definir o nome da tabela desta forma.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Isto provavelmente só é útil se precisar de manipulação da consulta de cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// sempre executar id >= 0 se este for o seu estilo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este é provavelmente mais útil se precisar de executar alguma lógica sempre que este registo for buscado. Precisa de desencriptar algo? Precisa de executar uma consulta de contagem personalizada cada vez (não é eficiente mas faz o que quer)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// desencriptar algo
		$self->segredo = aSuaFunçãoDesencriptar($self->segredo, $alguma_chave);

		// talvez armazenar algo personalizado como uma consulta???
		$self->setCustomData('contagem_visualizações', $self->select('COUNT(*) count')->from('visualizações_utilizador')->eq('id_utilizador', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Isto provavelmente só é útil se precisar de manipulação da consulta de cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __```markdown
# FlightPHP Registo Ativo

Um registo ativo está a mapear uma entidade da base de dados para um objeto PHP. Falando claramente, se tiver uma tabela de utilizadores na sua base de dados, pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` no seu código-fonte. Veja [exemplo básico](#exemplo-básico).

## Exemplo Básico

Vamos assumir que tem a seguinte tabela:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Agora pode configurar uma nova classe para representar esta tabela:

```php
/**
 * Uma classe ActiveRecord é geralmente singular
 * 
 * É altamente recomendado adicionar as propriedades da tabela como comentários aqui
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// pode definir desta forma
		parent::__construct($database_connection, 'users');
		// ou desta forma
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Agora veja a mágica acontecer!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // isto é apenas um exemplo, provavelmente usaria uma conexão real à base de dados

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// ou mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// ou mysqli com criação não baseada em objetos
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('uma palavra-passe fixe');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('uma palavra-passe fixe novamente!!!');
$user->insert();
// não é possível usar $user->save() aqui senão pensará que é uma atualização!

echo $user->id; // 2
```

E foi tão fácil adicionar um novo utilizador! Agora que há uma linha de utilizador na base de dados, como pode retirá-la?

```php
$user->find(1); // encontrar id = 1 na base de dados e devolver
echo $user->name; // 'Bobby Tables'
```

E se quiser encontrar todos os utilizadores?

```php
$users = $user->findAll();
```

E com uma determinada condição?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Vê o quão divertido isto é? Vamos instalar e começar!

## Instalação

Simplesmente instale com o Composer

```php
composer require flightphp/active-record 
```

## Utilização

Isto pode ser usado como uma biblioteca independente ou com o Framework PHP Flight. Completamente à sua escolha.

### Independente
Apenas certifique-se de passar uma conexão PDO para o construtor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // isto é apenas um exemplo, provavelmente usaria uma conexão real à base de dados

$User = new User($pdo_connection);
```

### Framework PHP Flight
Se estiver a usar o Framework PHP Flight, pode registar a classe ActiveRecord como um serviço (mas honestamente não precisa).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// depois pode usá-lo desta forma num controlador, numa função, etc.

Flight::user()->find(1);
```

## Referência da API
### Funções CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encontre um registo e atribua-o ao objeto atual. Se passar um `$id` de algum tipo, irá efetuar uma pesquisa na chave primária com esse valor. Se nada for passado, irá simplesmente encontrar o primeiro registo na tabela.

Adicionalmente pode passar outros métodos auxiliares para consultar a sua tabela.

```php
// encontrar um registo com algumas condições de antemão
$user->notNull('password')->orderBy('id DESC')->find();

// encontrar um registo por um id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encontra todos os registos na tabela que especificar.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Insere o registo atual na base de dados.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Atualiza o registo atual na base de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

Apaga o registo atual da base de dados.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Também pode apagar múltiplos registos executando uma pesquisa antes.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Dados sujos referem-se aos dados que foram alterados num registo.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sujo" até este ponto.

$user->email = 'test@example.com'; // agora o email é considerado "sujo" porque foi alterado.
$user->update();
// agora não há dados sujos porque foram atualizados e persistidos na base de dados

$user->password = password_hash()'nova palavra-passe'); // agora isto está sujo
$user->dirty(); // não passar nada limpará todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi capturado como sujo.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('uma palavra-passe diferente') ]);
$user->update(); // ambos name e password são atualizados.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Reinicia o registo atual para o seu estado inicial. Isto é realmente útil de usar em comportamentos de loop.
Se passar `true`, também irá repor os dados de consulta que foram usados para encontrar o objeto atual (comportamento padrão).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // começar com uma tela limpa
	$user_company->user_id = $user->id;
	$user_company->company_id = $algum_id_empresa;
	$user_company->insert();
}
```

### Métodos de Consulta SQL
#### `select(string $campo1 [, string $campo2 ... ])`

Pode selecionar apenas alguns dos campos de uma tabela se desejar (é mais eficiente em tabelas muito largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $tabela)`

Pode tecnicamente escolher outra tabela também! Por que não?!

```php
$user->select('id', 'name')->from(`user`)->find();
```

#### `join(string $nome_tabela, string $condição_junção)`

Pode até mesmo juntar-se a outra tabela na base de dados.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $condições_where)`

Pode definir algumas condições where personalizadas (não pode definir parâmetros nesta declaração where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Segurança** - Pode ser tentado fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Por favor, NÃO FAÇA ISTO!!! Isto é suscetível ao que se conhece como ataques de injeção SQL. Existem muitos artigos online, por favor, procure "ataques de injeção sql php" e encontrará muitos artigos sobre este assunto. A forma correta de lidar com isto com esta biblioteca é em vez deste método `where()`, faria algo como `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupe os seus resultados por uma determinada condição.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Ordene a query retornada de uma certa maneira.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limite)/limit(int $deslocamento, int $limite)`

Limite a quantidade de registos retornados. Se for dado um segundo int, será deslocamento, limite tal como em SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### Condições WHERE
#### `equal(string $campo, mixed $valor) / eq(string $campo, mixed $valor)`

Onde `campo = $valor`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $campo, mixed $valor) / ne(string $campo, mixed $valor)`

Onde `campo <> $valor`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $campo)`

Onde `campo IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $campo) / notNull(string $campo)`

Onde `campo IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $campo, mixed $valor) / gt(string $campo, mixed $valor)`

Onde `campo > $valor`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $campo, mixed $valor) / lt(string $campo, mixed $valor)`

Onde `campo < $valor`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $campo, mixed $valor) / ge(string $campo, mixed $valor) / gte(string $campo, mixed $valor)`

Onde `campo >= $valor`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $campo, mixed $valor) / le(string $campo, mixed $valor) / lte(string $campo, mixed $valor)`

Onde `campo <= $valor`

```php
$user->le('id', 1)->find();
```

#### `like(string $campo, mixed $valor) / notLike(string $campo, mixed $valor)`

Onde `campo LIKE $valor` ou `campo NOT LIKE $valor`

```php
$user->like('name', 'de')->find();
```

#### `in(string $campo, array $valores) / notIn(string $campo, array $valores)`

Onde `campo IN($valor)` ou `campo NOT IN($valor)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $campo, array $valores)`

Onde `campo BETWEEN $valor AND $valor1`

```php
$user->between('id', [1, 2])->find();
```

### Relacionamentos
Pode configurar vários tipos de relacionamentos utilizando esta biblioteca. Pode configurar relacionamentos de um-para-muitos e de um-para-um entre tabelas. Isto requer uma configuração extra na classe antecipadamente.

Configurar o array `$relations` não é difícil, mas adivinhar a sintaxe correta pode ser confuso.

```php
protected array $relations = [
	// pode nomear a chave como quiser. O nome do ActiveRecord é provavelmente bom. Por exemplo: utilizador, contacto, cliente
	'active_record_qualquer' => [
		// requerido
		self::HAS_ONE, // este é o tipo de relacionamento

		// requerido
		'Some_Class', // este é a classe "outro" ActiveRecord a que isto fará referência

		// requerido
		'chave_local', // esta é a chave local que faz referência à junção.
		// apenas a título de informação, isto também apenas se junta à chave primária do "outro" modelo

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que deseja executar. [] se não desejar nenhum.

		// opcional
		'nome_referência_inversa' // isto é se deseja referência inversa a esta relação de volta para si mesma. Por exemplo: $user->contact->user;
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

Agora temos as referências configuradas para podermos usá-las muito facilmente!

```php
$user = new User($pdo_connection);

// encontrar o utilizador mais recente.
$user->notNull('id')->orderBy('id desc')->find();

// get contacts by using relation:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// or we can go the other way.
$contact = new Contact();

// encontrar um contacto
$contact->find();

// get user by using relation:
echo $contact->user->name; // este é o nome do utilizador
```

Bastante fixe, não é?

### Definindo Dados Personalizados
Por vezes pode precisar de anexar algo único ao seu ActiveRecord, como um cálculo personalizado que pode ser mais fácil de anexar ao objeto e que será passado a um modelo.

#### `setCustomData(string $campo, mixed $valor)`
Anexa os dados personalizados com o método `setCustomData()`.
```php
$user->setCustomData('contagem_visitas_página', $contagem_visitas_página);
```

E então apenas o refira como uma propriedade normal do objeto.

```php
echo $user->contagem_visitas_página;
```

### Eventos

Uma outra funcionalidade fantástica desta biblioteca é sobre eventos. Os eventos são disparados em determinados momentos baseados em certos métodos que chama. São muito muito úteis para configurar dados automaticamente.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Isto é muito útil se precisar de definir uma conexão padrão ou algo assim.

```php
// index.php ou bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // não se esqueça da referência &
		// poderia fazer isto para definir automaticamente a conexão
		$config['connection'] = Flight::db();
		// ou isto
		$self->transformAndPersistConnection(Flight::db());
		
		// Também pode definir o nome da tabela desta forma.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Isto provavelmente só é útil se precisar de manipulação da consulta de cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// sempre executar id >= 0 se este for o seu estilo
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este é provavelmente mais útil se precisar de executar alguma lógica sempre que este registo for buscado. Precisa de desencriptar algo? Precisa de executar uma consulta de contagem personalizada cada vez (não é eficiente mas faz o que quer)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// desencriptar algo
		$self->segredo = aSuaFunçãoDesencriptar($self->segredo, $alguma_chave);

		// talvez armazenar algo personalizado como uma consulta???
		$self->setCustomData('contagem_visualizações', $self->select('COUNT(*) count')->from('visualizações_utilizador')->eq('id_utilizador', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Isto provavelmente só é útil se precisar de manipulação da consulta de cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($(database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// always run id >= 0 if that's your jam
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar to `afterFind()` but you get to do it to all the records instead!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// do something cool like afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Really helpful if you need some default values set each time.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// set some sound defaults
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

Maybe you have a user case for changing data after it's inserted?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// you do you
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// or whatever....
	} 
}
```