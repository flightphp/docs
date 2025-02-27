# Flight Active Record

Um registro ativo é o mapeamento de uma entidade de banco de dados para um objeto PHP. Falando de forma simples, se você tiver uma tabela de usuários em seu banco de dados, você pode "traduzir" uma linha dessa tabela para uma classe `User` e um objeto `$user` em seu código. Veja [exemplo básico](#basic-example).

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

Agora você pode configurar uma nova classe para representar essa tabela:

```php
/**
 * Uma classe ActiveRecord é geralmente singular
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
		// você pode configurá-la assim
		parent::__construct($database_connection, 'users');
		// ou assim
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Agora assista a mágica acontecer!

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
$user->password = password_hash('uma nova senha legal!!!');
$user->insert();
// não pode usar $user->save() aqui ou ele achará que é uma atualização!

echo $user->id; // 2
```

E foi assim tão fácil adicionar um novo usuário! Agora que há uma linha de usuário no banco de dados, como você a extrai?

```php
$user->find(1); // encontra id = 1 no banco de dados e o retorna.
echo $user->name; // 'Bobby Tables'
```

E se você quiser encontrar todos os usuários?

```php
$users = $user->findAll();
```

E quanto a uma certa condição?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Veja quanto é divertido isso? Vamos instalá-lo e começar!

## Instalação

Basta instalar com o Composer

```php
composer require flightphp/active-record 
```

## Uso

Isso pode ser utilizado como uma biblioteca autônoma ou com o Framework PHP Flight. Totalmente a seu critério.

### Autônomo
Basta garantir que você passe uma conexão PDO para o construtor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

$User = new User($pdo_connection);
```

> Não quer sempre configurar sua conexão de banco de dados no construtor? Veja [Gerenciamento de Conexão de Banco de Dados](#database-connection-management) para outras ideias!

### Registrar como um método no Flight
Se você estiver usando o Framework PHP Flight, pode registrar a classe ActiveRecord como um serviço, mas honestamente, você não precisa.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// então você pode usá-la assim em um controlador, uma função, etc.

Flight::user()->find(1);
```

## Métodos `runway`

[runway](/awesome-plugins/runway) é uma ferramenta CLI para o Flight que possui um comando personalizado para esta biblioteca.

```bash
# Uso
php runway make:record nome_da_tabela_do_banco_de_dados [nome_da_classe]

# Exemplo
php runway make:record users
```

Isso criará uma nova classe no diretório `app/records/` como `UserRecord.php` com o seguinte conteúdo:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Classe ActiveRecord para a tabela users.
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
     * @var array $relations Defina os relacionamentos para o modelo
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'nome_da_relação' => [ self::HAS_MANY, 'ClasseRelacionada', 'chave_estrangeira' ],
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

Encontra um registro e atribui ao objeto atual. Se você passar um `$id` de algum tipo, ele realizará uma busca na chave primária com esse valor. Se nada for passado, ele apenas encontrará o primeiro registro na tabela.

Além disso, você pode passar outros métodos auxiliares para consultar sua tabela.

```php
// encontrar um registro com algumas condições previamente
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

Se você tiver uma chave primária baseada em texto (como um UUID), pode definir o valor da chave primária antes de inserir de duas maneiras.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'algum-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // ou $user->save();
```

ou você pode fazer com que a chave primária seja gerada automaticamente para você por meio de eventos.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// você também pode definir a chave primária assim em vez do array acima.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou como você precisar gerar seus ids únicos
	}
}
```

Se você não definir a chave primária antes de inserir, ela será definida como o `rowid` e o 
banco de dados a gerará para você, mas não persistirá porque esse campo pode não existir
em sua tabela. Por isso, é recomendável usar o evento para gerenciar isso automaticamente.

#### `update(): boolean|ActiveRecord`

Atualiza o registro atual no banco de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Insere ou atualiza o registro atual no banco de dados. Se o registro tiver um id, ele atualizará; caso contrário, ele irá inserir.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Se você tiver relacionamentos definidos na classe, ele salvará recursivamente essas relações também, se foram definidas, instanciadas e possuem dados sujos para atualizar. (v0.4.0 e superior)

#### `delete(): boolean`

Exclui o registro atual do banco de dados.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Você também pode excluir vários registros executando uma pesquisa previamente.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Dados sujos referem-se aos dados que foram alterados em um registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sujo" até esse ponto.

$user->email = 'test@example.com'; // agora o e-mail é considerado "sujo" pois foi alterado.
$user->update();
// agora não há dados sujos porque foram atualizados e persistidos no banco de dados.

$user->password = password_hash('nova_senha'); // agora isso é sujo
$user->dirty(); // passando nada limpará todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi capturado como sujo.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('uma senha diferente') ]);
$user->update(); // tanto nome quanto senha são atualizados.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Este é um alias para o método `dirty()`. É um pouco mais claro o que você está fazendo.

```php
$user->copyFrom([ 'name' => 'algo', 'password' => password_hash('uma senha diferente') ]);
$user->update(); // tanto nome quanto senha são atualizados.
```

#### `isDirty(): boolean` (v0.4.0)

Retorna `true` se o registro atual tiver sido alterado.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Redefine o registro atual para seu estado inicial. Isso é muito bom para usar em tipos de comportamento de loop.
Se você passar `true`, isso também redefinirá os dados da consulta usados para encontrar o objeto atual (comportamento padrão).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // comece com uma folha limpa
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Depois de executar um método `find()`, `findAll()`, `insert()`, `update()`, ou `save()`, você pode obter o SQL que foi construído e usá-lo para fins de depuração.

## Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Você pode selecionar apenas algumas das colunas em uma tabela, se desejar (é mais eficiente em tabelas realmente largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Você pode tecnicamente escolher outra tabela também! Por que não?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Você pode até juntar a outra tabela no banco de dados.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Você pode definir alguns argumentos where personalizados (não pode definir parâmetros nesta declaração where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Segurança** - Você pode ser tentado a fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Por favor, NÃO FAÇA ISSO!!! Isso é suscetível a ataques que são conhecidos como injeções de SQL. Existem muitos artigos online, por favor, pesquise "sql injection attacks php" e você encontrará muitos artigos sobre esse assunto. A maneira adequada de lidar com isso com esta biblioteca é, em vez desse método `where()`, você faria algo mais parecido com `$user->eq('id', $id)->eq('name', $name)->find();` Se você absolutamente precisa fazer isso, a biblioteca `PDO` possui `$pdo->quote($var)` para escapar para você. Somente após usar `quote()` você pode usá-lo em uma declaração `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupe seus resultados por uma condição particular.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Classifique a consulta retornada de uma certa maneira.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite a quantidade de registros retornados. Se um segundo int for dado, será offset, limit como em SQL.

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
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Isso será avaliado para `id = 1 AND (name = 'demo' OR name = 'test')`

// Método 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Isso será avaliado para `id = 1 OR name = 'demo'`
```

## Relacionamentos
Você pode definir vários tipos de relacionamentos usando esta biblioteca. Você pode definir relacionamentos um->muitos e um->um entre tabelas. Isso requer uma configuração extra na classe previamente.

Definir o array `$relations` não é difícil, mas adivinhar a sintaxe correta pode ser confuso.

```php
protected array $relations = [
	// você pode nomear a chave como quiser. O nome do ActiveRecord provavelmente é bom. Ex: user, contact, client
	'user' => [
		// obrigatório
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // este é o tipo de relacionamento

		// obrigatório
		'Some_Class', // esta é a classe ActiveRecord "outra" que será referenciada

		// obrigatório
		// dependendo do tipo de relacionamento
		// self::HAS_ONE = a chave estrangeira que referencia a junção
		// self::HAS_MANY = a chave estrangeira que referencia a junção
		// self::BELONGS_TO = a chave local que referencia a junção
		'local_or_foreign_key',
		// apenas FYI, isso também se junta apenas à chave primária do modelo "outro"

		// opcional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condições adicionais que você deseja ao juntar a relação
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'back_reference_name' // isso é se você quiser referenciar essa relação de volta para si mesma Ex: $user->contact->user;
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

Agora temos as referências configuradas para que possamos usá-las muito facilmente!

```php
$user = new User($pdo_connection);

// encontrar o usuário mais recente.
$user->notNull('id')->orderBy('id desc')->find();

// obter contatos usando a relação:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou podemos fazer o caminho inverso.
$contact = new Contact();

// encontrar um contato
$contact->find();

// obter usuário usando a relação:
echo $contact->user->name; // este é o nome do usuário
```

Bem legal, não é?

## Definindo Dados Personalizados
Às vezes você pode precisar anexar algo único ao seu ActiveRecord, como um cálculo personalizado que pode ser mais fácil apenas anexar ao objeto que então seria passado para, digamos, um modelo.

#### `setCustomData(string $field, mixed $value)`
Você anexa os dados personalizados com o método `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

E então, você simplesmente faz referência a ele como uma propriedade normal do objeto.

```php
echo $user->page_view_count;
```

## Eventos

Mais uma super incrível característica sobre esta biblioteca é sobre eventos. Eventos são acionados em determinados momentos com base em certos métodos que você chama. Eles são muito úteis para configurar dados automaticamente para você.

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
		// você poderia fazer isso para definir automaticamente a conexão
		$config['connection'] = Flight::db();
		// ou isso
		$self->transformAndPersistConnection(Flight::db());
		
		// Você também pode definir o nome da tabela dessa maneira.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Isso provavelmente só é útil se você precisar de uma manipulação de consulta toda vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// sempre execute id >= 0 se isso for do seu agrado
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Esse é provavelmente mais útil se você sempre precisar executar alguma lógica toda vez que este registro for buscado. Você precisa descriptografar algo? Você precisa executar uma consulta de contagem personalizada a cada vez (não performático, mas tudo bem)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// descriptografando algo
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// talvez armazenando algo personalizado como uma consulta???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Isso é provavelmente só útil se você precisar de uma manipulação de consulta toda vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// sempre execute id >= 0 se isso for do seu agrado
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Semelhante ao `afterFind()`, mas você pode fazê-lo com todos os registros!

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

Realmente útil se você precisar definir alguns valores padrão toda vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// defina alguns padrões sensatos
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

Talvez você tenha um caso de uso para mudar dados após serem inseridos?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// você faz o que quiser
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// ou qualquer outra coisa....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil se você precisar definir alguns valores padrão cada vez que uma atualização ocorrer.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// defina alguns padrões sensatos
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Talvez você tenha um caso de uso para mudar dados após serem atualizados?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// você faz o que quiser
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// ou qualquer outra coisa....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Isso é útil se você quiser que eventos aconteçam tanto quando inserções quanto atualizações ocorrerem. Vou poupar você da longa explicação, mas tenho certeza de que você pode imaginar o que é.

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

Não tenho certeza do que você gostaria de fazer aqui, mas sem julgamentos! Vá em frente!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Ele foi um soldado valente... :cry-face:';
	} 
}
```

## Gerenciamento de Conexão de Banco de Dados

Quando você estiver usando esta biblioteca, pode definir a conexão com o banco de dados de algumas maneiras diferentes. Você pode definir a conexão no construtor, pode defini-la via uma variável de configuração `$config['connection']` ou pode defini-la via `setDatabaseConnection()` (v0.4.1).

```php
$pdo_connection = new PDO('sqlite:test.db'); // por exemplo
$user = new User($pdo_connection);
// ou
$user = new User(null, [ 'connection' => $pdo_connection ]);
// ou
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Se você deseja evitar configurar sempre uma `$database_connection` toda vez que chamar um registro ativo, existem maneiras de contornar isso!

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

> **Nota:** Se você planeja fazer testes unitários, fazer assim pode adicionar alguns desafios aos testes unitários, mas, no geral, porque você pode injetar sua 
conexão com `setDatabaseConnection()` ou `$config['connection']`, não é tão complicado.

Se você precisar atualizar a conexão do banco de dados, por exemplo, se estiver executando um script CLI de longa duração e precisar atualizar a conexão de tempos em tempos, pode redefinir a conexão com `$your_record->setDatabaseConnection($pdo_connection)`.

## Contribuindo

Por favor, contribua. :D

### Configuração

Quando você contribuir, certifique-se de executar `composer test-coverage` para manter 100% de cobertura de testes (isso não é verdadeiro teste unitário, mais como teste de integração).

Além disso, certifique-se de executar `composer beautify` e `composer phpcs` para corrigir quaisquer erros de linting.

## Licença

MIT