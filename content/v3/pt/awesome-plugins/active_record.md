# Flight Active Record

Um active record é o mapeamento de uma entidade de banco de dados para um objeto PHP. Em termos simples, se você tem uma tabela `users` no seu banco de dados, você pode "traduzir" uma linha dessa tabela para uma classe `User` e um objeto `$user` no seu código. Veja o [exemplo básico](#basic-example).

Clique [aqui](https://github.com/flightphp/active-record) para o repositório no GitHub.

## Exemplo Básico

Vamos supor que você tenha a seguinte tabela:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Agora você pode criar uma nova classe para representar esta tabela:

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
		// você pode definir desta forma
		parent::__construct($database_connection, 'users');
		// ou desta forma
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Agora veja a mágica acontecer!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usará uma conexão real de banco de dados

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// ou mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// ou mysqli com criação não baseada em objeto
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// não use $user->save() aqui ou ele pensará que é uma atualização!

echo $user->id; // 2
```

E foi assim, simples assim, para adicionar um novo usuário! Agora que há uma linha de usuário no banco de dados, como você a recupera?

```php
$user->find(1); // encontra id = 1 no banco de dados e retorna.
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

Viu como isso é divertido? Vamos instalá-lo e começar!

## Instalação

Basta instalar com o Composer

```php
composer require flightphp/active-record 
```

## Uso

Isso pode ser usado como uma biblioteca independente ou com o Flight PHP Framework. Totalmente com você.

### Independente
Apenas certifique-se de passar uma conexão PDO ao construtor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usará uma conexão real de banco de dados

$User = new User($pdo_connection);
```

> Não quer sempre definir sua conexão de banco de dados no construtor? Veja [Gerenciamento de Conexão de Banco de Dados](#database-connection-management) para outras ideias!

### Registrar como um método no Flight
Se você estiver usando o Flight PHP Framework, pode registrar a classe ActiveRecord como um serviço, mas, sinceramente, não precisa.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// então você pode usá-lo assim em um controller, uma função, etc.

Flight::user()->find(1);
```

## Métodos `runway`

[runway](/awesome-plugins/runway) é uma ferramenta CLI para Flight que possui um comando personalizado para esta biblioteca.

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
 * Classe ActiveRecord para a tabela users.
 * @link https://docs.flightphp.com/en/v3/awesome-plugins/active-record
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
     * @var array $relations Define os relacionamentos do modelo
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
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
// encontra um registro com algumas condições antes
$user->notNull('password')->orderBy('id DESC')->find();

// encontra um registro por um id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encontra todos os registros na tabela que você especificar.

```php
$user->findAll();
```

#### `first(): ActiveRecord` (v0.8.0)

Encontra o primeiro registro que corresponde às suas condições. Se você não definiu uma ordenação, ele ordena pela chave primária de forma ascendente. Se nada corresponder, você recebe o registro sem hidratação; então verifique `isHydrated()` se você não tiver certeza de que algo foi retornado.

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

Igual ao `first()`, mas ordena pela chave primária de forma descendente. Útil para consultas do tipo "me dê o mais recente".

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

Conta as linhas que correspondem às suas condições atuais. Se você tiver um `groupBy()` na consulta, `count()` o ignora de propósito. Um único valor escalar de contagem não pode representar uma linha por grupo.

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

Retorna `true` se algum registro corresponder às suas condições. Ele executa um barato `SELECT 1 ... LIMIT 1` nos bastidores.

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

Retorna um array simples de valores de uma coluna em vez de hidratar vários objetos. Combine com `distinct()` para obter valores únicos.

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

Um atalho para `pluck()` na chave primária.

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
```

#### `isHydrated(): boolean` (v0.4.0)

Retorna `true` se o registro atual foi hidratado (buscado do banco de dados).

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

Se você tiver uma chave primária baseada em texto (como um UUID), você pode definir o valor da chave primária antes de inserir de duas maneiras.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
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
		// você também pode definir a primaryKey desta forma, em vez do array acima.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou como você precisar gerar seus ids únicos
	}
}
```

Se você não definir a chave primária antes de inserir, ela será definida como `rowid` e o banco de dados a gerará para você, mas ela não será persistida porque esse campo pode não existir na sua tabela. É por isso que é recomendado usar o evento para lidar com isso automaticamente.

#### `update(): boolean|ActiveRecord`

Atualiza o registro atual no banco de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

Atualiza uma única coluna em um registro carregado e o salva. É um atalho para `$user->dirty([ 'name' => $value ])->update()`. Você precisa de um registro carregado para este.

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

Insere ou atualiza o registro atual no banco de dados. Se o registro tiver um id, ele atualizará; caso contrário, inserirá.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Se você tiver relacionamentos definidos na classe, ele salvará recursivamente esses relacionamentos também, se eles tiverem sido definidos, instanciados e tiverem dados sujos para atualizar. (v0.4.0 e superior)

#### `delete(): boolean`

Exclui o registro atual do banco de dados.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Você também pode excluir vários registros executando uma busca antes.

```php
$user->like('name', 'Bob%')->delete();
```

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

Atualiza todos os registros que correspondem às suas condições em uma única instrução. Nenhum registro é hidratado e nenhum evento é disparado, exatamente por isso é rápido. Retorna o número de linhas afetadas.

Ele se recusa a executar sem condições WHERE, a menos que você passe `true` para o segundo argumento. Seu eu do futuro agradece.

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// sim, você realmente quer atualizar todas as linhas da tabela
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

Exclui todos os registros que correspondem às suas condições em uma única instrução. Mesma situação do `updateAll()`: sem hidratação, sem eventos, e exige condições WHERE, a menos que você passe `true`. Retorna o número de linhas excluídas. Use com cuidado!

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array $dirty = []): ActiveRecord`

Dados sujos referem-se aos dados que foram alterados em um registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sujo" até este ponto.

$user->email = 'test@example.com'; // agora o email é considerado "sujo" pois foi alterado.
$user->update();
// agora não há dados sujos porque eles foram atualizados e persistidos no banco de dados

$user->password = password_hash('new password'); // agora isso está sujo
$user->dirty(); // passar nada limpará todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi capturado como sujo.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // tanto o name quanto o password são atualizados.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Este é um alias para o método `dirty()`. Fica um pouco mais claro o que você está fazendo.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // tanto o name quanto o password são atualizados.
```

#### `isDirty(): boolean` (v0.4.0)

Retorna `true` se o registro atual foi alterado.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Redefine o registro atual para seu estado inicial. Isso é muito bom para usar em comportamentos de loop.
Se você passar `true`, também redefinirá os dados da consulta usados para encontrar o objeto atual (comportamento padrão).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // comece com uma página limpa
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Depois de executar um método `find()`, `findAll()`, `insert()`, `update()` ou `save()`, você pode obter o SQL que foi construído e usá-lo para fins de depuração.

## Transações

Precisa executar algumas gravações que precisam ser bem-sucedidas juntas? Envolva-as em `transaction()` (v0.8.0). Passe um callable e o registro entra como argumento. Se o callable lançar uma exceção, tudo será revertido e a exceção será relançada para você. Caso contrário, ele confirma e devolve o que seu callable retornou.

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// o commit acontece aqui se nada foi lançado
});
```

Transações aninhadas não são suportadas (sem savepoints), então mantenha-as simples.

## Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Você pode selecionar apenas algumas colunas de uma tabela, se quiser (é mais performático em tabelas realmente largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Você pode, tecnicamente, escolher outra tabela também! Por que não?!!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Você pode até fazer join com outra tabela no banco de dados.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Você pode definir alguns argumentos where personalizados (você não pode definir parâmetros nesta declaração where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Segurança** - Você pode ficar tentado a fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Por favor, NÃO FAÇA ISSO!!! Isso é suscetível ao que é conhecido como ataques de Injeção de SQL. Existem muitos artigos online; por favor, pesquise "sql injection attacks php" e você encontrará muitos artigos sobre esse assunto. A maneira correta de lidar com isso com esta biblioteca é, em vez deste método `where()`, fazer algo como `$user->eq('id', $id)->eq('name', $name)->find();`. Se você absolutamente precisar fazer isso, a biblioteca `PDO` tem `$pdo->quote($var)` para escapar para você. Somente depois de usar `quote()` você pode usá-lo em uma instrução `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupe seus resultados por uma condição específica.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Ordene a consulta retornada de uma determinada maneira.

```php
$user->orderBy('name DESC')->find();
```

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()` e `orderBy()` aceitam fragmentos SQL brutos, o que é bom quando você está codificando `'name DESC'`. Se o nome da coluna vier de entrada do usuário (por exemplo, um cabeçalho de tabela ordenável), use `orderByColumn()` em vez disso. Apenas nomes de colunas simples e caminhos `table.column` são permitidos, e a direção deve ser `ASC` ou `DESC`, então não há nada para injetar.

```php
// $sortColumn vem da requisição
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limite a quantidade de registros retornados. Se um segundo inteiro for fornecido, ele será offset, limit como no SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

Adiciona `DISTINCT` à sua próxima consulta. Funciona no select normal e com `pluck()`. `count()` o ignora, já que colocar `DISTINCT` em uma única linha agregada não faz nada.

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
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

É possível envolver suas condições em uma instrução OR. Isso é feito com os métodos `startWrap()` e `endWrap()` ou preenchendo o 3º parâmetro da condição após o campo e o valor.

```php
// Método 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Isso será avaliado como `id = 1 AND (name = 'demo' OR name = 'test')`

// Método 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Isso será avaliado como `id = 1 OR name = 'demo'`
```

## Escopos

Escopos (v0.8.0) são cadeias de consulta reutilizáveis, definidas como métodos de instância simples em sua classe que retornam `$this`. Depois de escrever um, ele encadeia como qualquer outro método de consulta.

```php
class User extends flight\ActiveRecord {

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	public function active(): self
	{
		return $this->eq('status', 'active');
	}

	public function recent(int $days = 7): self
	{
		return $this->ge('created_at', date('Y-m-d', strtotime("-{$days} days")));
	}
}

// e agora suas consultas parecem frases
(new User($pdo_connection))->active()->recent(30)->findAll();
```

Você também pode chamar um escopo pelo nome com `scope()`, o que é útil quando o nome do escopo vem de outro lugar no seu código. Ele lança uma `BadMethodCallException` se o método não existir.

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## Relacionamentos
Você pode definir vários tipos de relacionamentos usando esta biblioteca. Você pode definir relacionamentos um->muitos e um->um entre tabelas. Isso requer uma pequena configuração extra na classe antecipadamente.

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
		// self::HAS_ONE = a chave estrangeira que referencia o join
		// self::HAS_MANY = a chave estrangeira que referencia o join
		// self::BELONGS_TO = a chave local que referencia o join
		'local_or_foreign_key',
		// apenas para informação, isso também só faz join com a chave primária do "outro" modelo

		// opcional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condições adicionais que você deseja ao juntar o relacionamento
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'back_reference_name' // isto é se você quiser fazer uma referência inversa deste relacionamento de volta a ele mesmo Ex: $user->contact->user;
	]
];
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

Agora temos as referências configuradas, então podemos usá-las facilmente!

```php
$user = new User($pdo_connection);

// encontra o usuário mais recente.
$user->notNull('id')->orderBy('id desc')->find();

// obtém contatos usando o relacionamento:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou podemos ir pelo outro caminho.
$contact = new Contact();

// encontra um contato
$contact->find();

// obtém o usuário usando o relacionamento:
echo $contact->user->name; // este é o nome do usuário
```

Muito legal, né?

### Carregamento Antecipado (Eager Loading)

#### Visão Geral
O carregamento antecipado resolve o problema de consultas N+1 carregando os relacionamentos antecipadamente. Em vez de executar uma consulta separada para cada relacionamento de registro, o carregamento antecipado busca todos os dados relacionados em apenas uma consulta adicional por relacionamento.

> **Nota:** O carregamento antecipado está disponível apenas para v0.7.0 e superior.

#### Uso Básico
Use o método `with()` para especificar quais relacionamentos carregar antecipadamente:
```php
// Carrega usuários com seus contatos em 2 consultas em vez de N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Sem consulta adicional!
    }
}
```

#### Múltiplos Relacionamentos
Carregue vários relacionamentos de uma vez:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Tipos de Relacionamentos

##### HAS_MANY
```php
// Carrega antecipadamente todos os contatos de cada usuário
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts já está carregado como um array
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// Carrega antecipadamente um contato para cada usuário
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact já está carregado como um objeto
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Carrega antecipadamente os usuários pais de todos os contatos
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user já está carregado
    echo $c->user->name;
}
```
##### Com find()
O carregamento antecipado funciona tanto com 
`findAll()`
 quanto com 
`find()`
:

```php
$user = $user->with('contacts')->find(1);
// Usuário e todos os seus contatos carregados em 2 consultas
```
#### Benefícios de Performance
Sem carregamento antecipado (problema N+1):
```php
$users = $user->findAll(); // 1 consulta
foreach ($users as $u) {
    $contacts = $u->contacts; // N consultas (uma por usuário!)
}
// Total: 1 + N consultas
```

Com carregamento antecipado:

```php
$users = $user->with('contacts')->findAll(); // 2 consultas no total
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 consultas adicionais!
}
// Total: 2 consultas (1 para usuários + 1 para todos os contatos)
```
Para 10 usuários, isso reduz as consultas de 11 para 2 - uma redução de 82%!

#### Notas Importantes
- O carregamento antecipado é totalmente opcional - o carregamento preguiçoso ainda funciona como antes
- Relacionamentos já carregados são pulados automaticamente
- Referências inversas funcionam com carregamento antecipado
- Callbacks de relacionamento são respeitados durante o carregamento antecipado

#### Limitações
- Carregamento antecipado aninhado (ex.: 
`with(['contacts.addresses'])`
) não é suportado atualmente
- Restrições de carregamento antecipado via closures não são suportadas nesta versão

## Definindo Dados Personalizados
Às vezes você pode precisar anexar algo único ao seu ActiveRecord, como um cálculo personalizado que pode ser mais fácil de apenas anexar ao objeto que será passado, por exemplo, a um template.

#### `setCustomData(string $field, mixed $value)`
Você anexa os dados personalizados com o método `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

E então você simplesmente referencia como uma propriedade normal de objeto.

```php
echo $user->page_view_count;
```

## Timestamps

Se sua tabela tiver colunas `created_at` e `updated_at`, você pode fazer a biblioteca preenchê-las para você (v0.8.0). Defina `protected bool $timestamps = true;` na sua classe e ela definirá `created_at` e `updated_at` quando você inserir, e `updated_at` quando você atualizar. O formato é `Y-m-d H:i:s`. Se você definir qualquer uma das colunas manualmente, a biblioteca deixa seu valor como está.

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

Sua tabela realmente precisa dessas colunas, ou as inserções e atualizações falharão.

## Eventos

Mais um recurso super incrível desta biblioteca são os eventos. Eventos são acionados em determinados momentos com base em certos métodos que você chama. Eles são muito, muito úteis para configurar dados automaticamente para você.

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

	protected function onConstruct(self $self, array &$config) { // não esqueça a referência &
		// você poderia fazer isso para definir automaticamente a conexão
		$config['connection'] = Flight::db();
		// ou isso
		$self->transformAndPersistConnection(Flight::db());
		
		// Você também pode definir o nome da tabela desta forma.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Isso provavelmente só é útil se você precisar de uma manipulação de consulta a cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// sempre execute id >= 0 se essa é a sua praia
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este é provavelmente mais útil se você sempre precisar executar alguma lógica toda vez que este registro for buscado. Você precisa descriptografar algo? Você precisa executar uma consulta de contagem personalizada a cada vez (não performático, mas tanto faz)?

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

Isso provavelmente só é útil se você precisar de uma manipulação de consulta a cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// sempre execute id >= 0 se essa é a sua praia
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Semelhante ao `afterFind()`, mas você pode fazer isso para todos os registros!

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

Muito útil se você precisar definir alguns valores padrão a cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// define alguns padrões sólidos
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

Talvez você tenha um caso de uso para alterar dados depois que eles são inseridos?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// você faz você
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// ou o que quer que seja....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Muito útil se você precisar definir alguns valores padrão a cada atualização.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// define alguns padrões sólidos
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Talvez você tenha um caso de uso para alterar dados depois que eles são atualizados?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// você faz você
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// ou o que quer que seja....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Isso é útil se você quiser que eventos aconteçam tanto em inserções quanto em atualizações. Vou poupar você da longa explicação, mas tenho certeza que você pode adivinhar o que é.

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

Não sei o que você gostaria de fazer aqui, mas sem julgamentos! Vá em frente!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Ele era um soldado corajoso... :cry-face:';
	} 
}
```

## Gerenciamento de Conexão de Banco de Dados

Quando você estiver usando esta biblioteca, você pode definir a conexão de banco de dados de algumas maneiras diferentes. Você pode definir a conexão no construtor, pode defini-la através da variável de configuração `$config['connection']` ou pode defini-la via `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // por exemplo
$user = new User($pdo_connection);
// ou
$user = new User(null, [ 'connection' => $pdo_connection ]);
// ou
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Se você quiser evitar sempre definir um `$database_connection` toda vez que chamar um active record, existem maneiras de contornar isso!

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

// E agora, sem argumentos necessários!
$user = new User();
```

> **Nota:** Se você está planejando fazer testes unitários, fazer desta forma pode adicionar alguns desafios aos testes unitários, mas no geral, como você pode injetar sua 
conexão com `setDatabaseConnection()` ou `$config['connection']`, não é tão ruim.

Se você precisar atualizar a conexão de banco de dados, por exemplo, se estiver executando um script CLI de longa duração e precisar atualizar a conexão de vez em quando, você pode redefinir a conexão com `$your_record->setDatabaseConnection($pdo_connection)`.

## Contribuindo

Por favor, contribua. :D

### Configuração

Quando você contribuir, certifique-se de executar `composer test-coverage` para manter 100% de cobertura de testes (isso não é cobertura de teste unitário de verdade, é mais como teste de integração).

Também certifique-se de executar `composer beautify` e `composer phpcs` para corrigir quaisquer erros de linting.

## Licença

MIT