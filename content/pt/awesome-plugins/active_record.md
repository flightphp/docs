# Flight Active Record

Um active record é o mapeamento de uma entidade de banco de dados para um objeto PHP. Falando de forma simples, se você tem uma tabela de usuários em seu banco de dados, você pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` em seu código. Veja [exemplo básico](#basic-example).

Clique [aqui](https://github.com/flightphp/active-record) para o repositório no GitHub.

## Exemplo Básico

Vamos assumir que você tem a seguinte tabela:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY,
	name TEXT,
	password TEXT
);
```

Agora você pode configurar uma nova classe para representar esta tabela:

```php
/**
 * Uma classe ActiveRecord geralmente é singular
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
		// você pode configurá-la desta maneira
		parent::__construct($database_connection, 'users');
		// ou desta maneira
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Agora observe a mágica acontecer!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// ou mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// ou mysqli com criação não baseada em objeto
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('uma senha legal');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('uma senha legal de novo!!!');
$user->insert();
// não pode usar $user->save() aqui ou pensará que é uma atualização!

echo $user->id; // 2
```

E foi tão fácil adicionar um novo usuário! Agora que há uma linha de usuário no banco de dados, como você a extrai?

```php
$user->find(1); // encontra id = 1 no banco de dados e retorna.
echo $user->name; // 'Bobby Tables'
```

E se você quiser encontrar todos os usuários?

```php
$users = $user->findAll();
```

E que tal com uma determinada condição?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Veja como isso é divertido? Vamos instalá-lo e começar!

## Instalação

Basta instalar com o Composer

```php
composer require flightphp/active-record
```

## Uso

Isso pode ser usado como uma biblioteca independente ou com o Flight PHP Framework. Totalmente a seu critério.

### Independente
Apenas certifique-se de passar uma conexão PDO para o construtor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

$User = new User($pdo_connection);
```

> Não quer sempre definir sua conexão de banco de dados no construtor? Veja [Gerenciamento de Conexão de Banco de Dados](#database-connection-management) para outras ideias!

### Registrar como um método no Flight
Se você estiver usando o Flight PHP Framework, pode registrar a classe ActiveRecord como um serviço, mas você honestamente não precisa.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// então você pode usá-lo assim em um controlador, uma função, etc.

Flight::user()->find(1);
```

## Métodos `runway`

[runway](https://docs.flightphp.com/awesome-plugins/runway) é uma ferramenta CLI para o Flight que possui um comando personalizado para esta biblioteca.

```bash
# Uso
php runway make:record database_table_name [class_name]

# Exemplo
php runway make:record users
```

Isso criará uma nova classe no diretório `app/records/` como `UserRecord.php` com o seguinte conteúdo:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe ActiveRecord para a tabela de usuários.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
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
     * @var array $relations Define os relacionamentos para o modelo
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Construtor
     * @param mixed $databaseConnection A conexão com o banco de dados
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## Funções CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encontra um registro e o atribui ao objeto atual. Se você passar um `$id` de algum tipo, ele fará uma busca na chave primária com esse valor. Se nada for passado, ele apenas encontrará o primeiro registro na tabela.

Além disso, você pode passar outros métodos auxiliares para consultar sua tabela.

```php
// encontrar um registro com algumas condições prévias
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

Retorna `true` se o registro atual tiver sido hidratado (buscado do banco de dados).

```php
$user->find(1);
// se um registro for encontrado com dados...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Insere o registro atual no banco de dados.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Chaves Primárias Baseadas em Texto

Se você tiver uma chave primária baseada em texto (como um UUID), pode definir o valor da chave primária antes de inserir de uma das duas maneiras.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // ou $user->save();
```

ou você pode fazer a chave primária ser gerada automaticamente para você através de eventos.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// você também pode definir a primaryKey dessa maneira em vez do array acima.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou como você precisa gerar seus ids únicos
	}
}
```

Se você não definir a chave primária antes de inserir, ela será definida como `rowid` e o 
banco de dados a gerará para você, mas não será persistente porque esse campo pode não existir
na sua tabela. Por isso, é recomendável usar o evento para lidar automaticamente com isso 
para você.

#### `update(): boolean|ActiveRecord`

Atualiza o registro atual no banco de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
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

**Nota:** Se você tiver relacionamentos definidos na classe, eles também serão salvos recursivamente se tiverem sido definidos, instanciados e tiverem dados sujos a serem atualizados. (v0.4.0 e acima)

#### `delete(): boolean`

Deleta o registro atual do banco de dados.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Você também pode deletar vários registros executando uma pesquisa previamente.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array $dirty = []): ActiveRecord`

Dados sujos referem-se aos dados que foram alterados em um registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sujo" até este ponto.

$user->email = 'test@example.com'; // agora o email é considerado "sujo" pois foi alterado.
$user->update();
// agora não há dados sujos porque foram atualizados e persistidos no banco de dados

$user->password = password_hash('nova_senha'); // agora isto está sujo
$user->dirty(); // passando nada irá limpar todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi capturado como sujo.

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

Retorna `true` se o registro atual tiver sido alterado.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Reinicializa o registro atual para seu estado inicial. Isso é realmente bom para usar em comportamentos do tipo loop.
Se você passar `true`, também redefinirá os dados da consulta que foram usados para encontrar o objeto atual (comportamento padrão).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // comece com uma nova configuração
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Depois de você executar um método `find()`, `findAll()`, `insert()`, `update()`, ou `save()`, você pode obter o SQL que foi gerado e usá-lo para fins de depuração.

## Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Você pode selecionar apenas algumas das colunas em uma tabela se quiser (é mais eficiente em tabelas realmente largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Você pode tecnicamente escolher outra tabela também! Por que não?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Você pode até mesmo unir a outra tabela no banco de dados.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Você pode definir alguns argumentos personalizados where (você não pode definir parâmetros nesta declaração where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Segurança** - Você pode ser tentado a fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Por favor, NÃO FAÇA ISSO!!! Isso é susceptível ao que se conhece como ataques de injeção SQL. Há muitos artigos online, por favor Google "sql injection attacks php" e você encontrará vários artigos sobre este assunto. A maneira correta de lidar com isso com esta biblioteca é, em vez deste método `where()`, você faria algo mais como `$user->eq('id', $id)->eq('name', $name)->find();` Se você absolutamente tiver que fazer isso, a biblioteca `PDO` tem `$pdo->quote($var)` para escapar para você. Somente após usar `quote()` você pode usá-lo em uma declaração `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupe seus resultados por uma condição específica.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Classifique a consulta retornada de uma determinada maneira.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite a quantidade de registros retornados. Se um segundo inteiro for dado, ele será deslocado, limite apenas como no SQL.

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

### Condições OR

É possível envolver suas condições em uma declaração OR. Isso é feito com o método `startWrap()` e `endWrap()` ou preenchendo o 3º parâmetro da condição após o campo e o valor.

```php
// Método 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'teste')->endWrap('OR')->find();
// Isso será avaliado como `id = 1 AND (name = 'demo' OR name = 'teste')`

// Método 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Isso será avaliado como `id = 1 OR name = 'demo'`
```

## Relacionamentos
Você pode definir vários tipos de relacionamentos usando esta biblioteca. Você pode definir relacionamentos um->muitos e um->um entre tabelas. Isso requer um pouco de configuração extra na classe com antecedência.

Definir o array `$relations` não é difícil, mas adivinhar a sintaxe correta pode ser confuso.

```php
protected array $relations = [
	// você pode nomear a chave como quiser. O nome do ActiveRecord é provavelmente bom. Ex: user, contact, client
	'user' => [
		// requerido
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // este é o tipo de relacionamento

		// requerido
		'Some_Class', // esta é a classe ActiveRecord "outra" que será referenciada

		// requerido
		// dependendo do tipo de relacionamento
		// self::HAS_ONE = a chave estrangeira que referencia a união
		// self::HAS_MANY = a chave estrangeira que referencia a união
		// self::BELONGS_TO = a chave local que referencia a união
		'local_or_foreign_key',
		// apenas para sua informação, isso também só une à chave primária do "outro" modelo

		// opcional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condições adicionais que você deseja ao unir o relacionamento
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'back_reference_name' // isso é se você quiser referenciar esse relacionamento de volta para si mesmo Ex: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord {
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord {
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

Agora temos as referências configuradas para que possamos usá-las com muita facilidade!

```php
$user = new User($pdo_connection);

// encontre o usuário mais recente.
$user->notNull('id')->orderBy('id desc')->find();

// obtenha os contatos usando a relação:
foreach ($user->contacts as $contact) {
	echo $contact->id;
}

// ou podemos ir pelo outro caminho.
$contact = new Contact();

// encontre um contato
$contact->find();

// obtenha o usuário usando a relação:
echo $contact->user->name; // este é o nome do usuário
```

Bacana, né?

## Definindo Dados Personalizados
Às vezes, você pode precisar anexar algo único ao seu ActiveRecord, como um cálculo personalizado que pode ser mais fácil de anexar ao objeto que seria passado para, digamos, um template.

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

Mais uma super função incrível sobre esta biblioteca é sobre eventos. Eventos são acionados em certos momentos com base em certos métodos que você chama. Eles são muito úteis para configurar dados para você automaticamente.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Isso é realmente útil se você precisar definir uma conexão padrão ou algo assim.

```php
// index.php ou bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // não se esqueça da referência &
		// você pode fazer isso para definir automaticamente a conexão
		$config['connection'] = Flight::db();
		// ou isso
		$self->transformAndPersistConnection(Flight::db());
		
		// Você também pode definir o nome da tabela dessa maneira.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Isso provavelmente só será útil se você precisar de uma manipulação de consulta a cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// sempre execute id >= 0 se isso for o que você deseja
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este provavelmente será mais útil se você sempre precisar executar alguma lógica toda vez que este registro for buscado. Você precisa de criptografia de algo? Você precisa executar uma consulta de contagem personalizada toda vez (não é performático, mas tudo bem)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// desencriptando algo
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// talvez armazenar algo personalizado como uma consulta???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Isso provavelmente só será útil se você precisar de uma manipulação de consulta a cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// sempre execute id >= 0 se isso for o que você deseja
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Semelhante ao `afterFind()`, mas você pode aplicá-lo a todos os registros!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// faça algo legal como no afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil se você precisar definir alguns valores padrão a cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// defina alguns padrões razoáveis
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

Talvez você tenha um caso de uso para alterar dados após serem inseridos?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// você faz o que quiser
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// ou o que quiser....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil se você precisar definir alguns valores padrão a cada vez em uma atualização.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeUpdate(self $self) {
		// defina alguns padrões razoáveis
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Talvez você tenha um caso de uso para alterar dados após serem atualizados?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterUpdate(self $self) {
		// você faz o que quiser
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// ou o que quiser....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Isso é útil se você quiser que eventos aconteçam tanto quando inserções quanto atualizações ocorrerem. Vou evitar a longa explicação, mas com certeza você pode adivinhar o que é.

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

Não tenho certeza sobre o que você gostaria de fazer aqui, mas não julgo! Vá em frente!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Ele foi um soldado corajoso... :cry-face:';
	} 
}
```

## Gerenciamento de Conexão de Banco de Dados

Quando você usa esta biblioteca, pode definir a conexão com o banco de dados de várias maneiras diferentes. Você pode definir a conexão no construtor, pode defini-la por meio de uma variável de configuração `$config['connection']` ou pode defini-la através de `setDatabaseConnection()` (v0.4.1).

```php
$pdo_connection = new PDO('sqlite:test.db'); // por exemplo
$user = new User($pdo_connection);
// ou
$user = new User(null, [ 'connection' => $pdo_connection ]);
// ou
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Se você quiser evitar definir sempre um `$database_connection` toda vez que chamar um record ativo, há maneiras de contornar isso!

```php
// index.php ou bootstrap.php
// Defina isso como uma classe registrada no Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// E agora, nenhum argumento é necessário!
$user = new User();
```

> **Nota:** Se você planeja fazer testes unitários, fazê-lo dessa forma pode adicionar alguns desafios aos testes unitários, mas, no geral, como você pode injetar sua conexão com `setDatabaseConnection()` ou `$config['connection']`, não é tão ruim.

Se você precisar atualizar a conexão com o banco de dados, por exemplo, se estiver executando um script CLI de longa duração e precisar atualizar a conexão de tempos em tempos, você pode redefinir a conexão com `$your_record->setDatabaseConnection($pdo_connection)`.

## Contribuindo

Por favor, faça isso. :D

### Configuração

Quando você contribuir, certifique-se de executar `composer test-coverage` para manter 100% de cobertura de testes (isso não é cobertura de testes unitários reais, mais como testes de integração).

Além disso, certifique-se de executar `composer beautify` e `composer phpcs` para corrigir qualquer erro de lint. 

## Licença

MIT