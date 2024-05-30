# ActiveRecord do Flight 

Um registro ativo está mapeando uma entidade do banco de dados para um objeto PHP. Falando claramente, se você tem uma tabela de usuários no seu banco de dados, você pode "traduzir" uma linha dessa tabela para uma classe `User` e um objeto `$user` no seu código-fonte. Veja [exemplo básico](#basic-example).

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
 * É altamente recomendado adicionar as propriedades da tabela como comentários aqui
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($conexao_banco_de_dados)
	{
		// você pode configurar assim
		parent::__construct($conexao_banco_de_dados, 'users');
		// ou assim
		parent::__construct($conexao_banco_de_dados, null, [ 'table' => 'users']);
	}
}
```

Agora veja a mágica acontecer!

```php
// para sqlite
$conexao_banco_de_dados = new PDO('sqlite:test.db'); // isso é apenas um exemplo, normalmente você usaria uma conexão de banco de dados real

// para mysql
$conexao_banco_de_dados = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nome_usuario', 'senha');

// ou mysqli
$conexao_banco_de_dados = new mysqli('localhost', 'nome_usuario', 'senha', 'test_db');
// ou mysqli com criação não baseada em objeto
$conexao_banco_de_dados = mysqli_connect('localhost', 'nome_usuario', 'senha', 'test_db');

$user = new User($conexao_banco_de_dados);
$user->name = 'Bobby Tables';
$user->password = password_hash('alguma senha legal');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('outra senha legal novamente!!!');
$user->insert();
// não é possível usar $user->save() aqui, senão ele pensará que é uma atualização!

echo $user->id; // 2
```

E foi tão fácil adicionar um novo usuário! Agora que há uma linha de usuário no banco de dados, como você a extrai?

```php
$user->find(1); // encontra id = 1 no banco de dados e retorna
echo $user->name; // 'Bobby Tables'
```

E se você quiser encontrar todos os usuários?

```php
$users = $user->findAll();
```

E com uma determinada condição?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Veja como isso é divertido? Vamos instalá-lo e começar!

## Instalação

Simplesmente instale com o Composer

```php
composer require flightphp/active-record 
```

## Uso

Isso pode ser usado como uma biblioteca independente ou com o Framework PHP Flight. Completamente com você.

### Independente
Apenas certifique-se de passar uma conexão PDO para o construtor.

```php
$conexao_pdo = new PDO('sqlite:test.db'); // isso é apenas um exemplo, normalmente você usaria uma conexão de banco de dados real

$User = new User($pdo_connection);
```

### Framework PHP Flight
Se você estiver usando o Framework PHP Flight, você pode registrar a classe ActiveRecord como um serviço (mas honestamente você não precisa).

```php
Flight::register('user', 'User', [ $conexao_pdo ]);

// então você pode usá-lo assim em um controlador, uma função, etc.

Flight::user()->find(1);
```

## Funções CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encontra um registro e atribui ao objeto atual. Se você passar um `$id` de algum tipo, ele fará uma consulta na chave primária com esse valor. Se nada for passado, ele encontrará apenas o primeiro registro da tabela.

Além disso, você pode passar outros métodos auxiliares para consultar sua tabela.

```php
// encontrar um registro com algumas condições antecipadamente
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

Retorna `true` se o registro atual foi preenchido (buscado do banco de dados).

```php
$user->find(1);
// se um registro for encontrado com dados...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Insere o registro atual no banco de dados.

```php
$user = new User($conexao_banco_de_dados);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Chaves Primárias Baseadas em Texto

Se você tiver uma chave primária baseada em texto (como um UUID), você pode definir o valor da chave primária antes de inserir de duas maneiras.

```php
$user = new User($conexao_banco_de_dados, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'algum-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // ou $user->save();
```

ou você pode ter a chave primária gerada automaticamente para você por meio de eventos.

```php
class User extends flight\ActiveRecord {
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users', [ 'primaryKey' => 'uuid' ]);
		// você também pode definir a chave primária assim em vez do array acima.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou como você precisa gerar seus IDs exclusivos
	}
}
```

Se você não definir a chave primária antes de inserir, ela será definida como `rowid` e o
banco de dados a gerará para você, mas não persistirá porque esse campo pode não existir
em sua tabela. Por isso é recomendado usar o evento para lidar automaticamente com isso
para você.

#### `update(): boolean|ActiveRecord`

Atualiza o registro atual no banco de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'teste@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Insere ou atualiza o registro atual no banco de dados. Se o registro tiver um id, ele será atualizado, caso contrário será inserido.

```php
$user = new User($conexao_banco_de_dados);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Se você tiver relacionamentos definidos na classe, ele salvará recursivamente essas relações também se tiverem sido definidas, instanciadas e tiverem dados sujos para atualizar. (v0.4.0 e acima)

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

#### `dirty(array  $dirty = []): ActiveRecord`

Dados sujos referem-se aos dados que foram alterados em um registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sujo" até este ponto.

$user->email = 'teste@example.com'; // agora o email é considerado "sujo" porque foi alterado.
$user->update();
// agora não há dados sujos porque foram atualizados e persistidos no banco de dados

$user->password = password_hash()'nova_senha'); // agora isso está sujo
$user->dirty(); // passar nada limpará todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi capturado como sujo.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('outra senha') ]);
$user->update(); // tanto o nome quanto a senha são atualizados.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Este é um alias para o método `dirty()`. É um pouco mais claro o que você está fazendo.

```php
$user->copyFrom([ 'name' => 'algo', 'password' => password_hash('outra senha') ]);
$user->update(); // tanto o nome quanto a senha são atualizados.
```

#### `isDirty(): boolean` (v0.4.0)

Retorna `true` se o registro atual foi alterado.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'teste@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Redefine o registro atual para seu estado inicial. Isso é realmente bom de usar em comportamentos de loop.
Se você passar `true`, também redefinirá os dados da consulta que foram usados para encontrar o objeto atual (comportamento padrão).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($conexao_banco_de_dados);

foreach($users as $user) {
	$user_company->reset(); // comece com uma tela limpa
	$user_company->user_id = $user->id;
	$user_company->company_id = $algum_id_empresa;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Depois de executar um método `find()`, `findAll()`, `insert()`, `update()`, ou `save()` você pode obter o SQL que foi construído e usá-lo para fins de depuração.

## Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Você pode selecionar apenas algumas das colunas em uma tabela se desejar (é mais eficiente em tabelas muito largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Você pode escolher outra tabela também! Por que diabos não?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Você também pode juntar-se a outra tabela no banco de dados.

```php
$user->join('contatos', 'contatos.id_usuario = usuarios.id')->find();
```

#### `where(string $where_conditions)`

Você pode definir alguns argumentos where personalizados (você não pode definir parâmetros nesta instrução where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Segurança** - Você pode ser tentado a fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. POR FAVOR NÃO FAÇA ISSO!!! Isso é suscetível ao que é conhecido como ataques de injeção de SQL. Há muitos artigos online, por favor pesquise "ataques de injeção de sql php" e você encontrará muitos artigos sobre esse assunto. A maneira correta de lidar com isso com esta biblioteca é em vez do método `where()`, você faria algo mais como `$user->eq('id', $id)->eq('name', $name)->find();` Se você absolutamente tiver que fazer isso, a biblioteca `PDO` tem `$pdo->quote($var)` para escapar isso para você. Somente após usar `quote()` você pode usá-lo em uma declaração `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

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

Limite a quantidade de registros retornados. Se um segundo inteiro for fornecido, ele será um deslocamento, limitando apenas como no SQL.

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
Você pode definir vários tipos de relacionamentos usando esta biblioteca. Você pode definir relacionamentos um para muitos e um para um entre tabelas. Isso requer uma configuração um pouco extra na classe antecipadamente.

Configurar o `$relations` array não é difícil, mas adivinhar a sintaxe correta pode ser confuso.

```php
protected array $relations = [
	// você pode nomear a chave como desejar. O nome do ActiveRecord é provavelmente bom. Ex: usuário, contato, cliente
	'utilisateur' => [
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
		'chave_local_ou_estrageira',
		// só para informação, isso também só junta à chave primária do modelo "outro"

		// opcional
		[ 'eq' => [ 'id_cliente', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // condições adicionais que você deseja ao juntar a relação
		// $registro->eq('id_cliente', 5)->select('COUNT(*) as count')->limit(5))

		// opcional
		'nome_referencia_de_retorno' // isso é se você quiser fazer referência a esse relacionamento de volta para si mesmo Ex: $user->contato->usuário;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contatos' => [ self::HAS_MANY, Contato::class, 'id_usuario' ],
		'contato' => [ self::HAS_ONE, Contato::class, 'id_usuario' ],
	];

	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}
}

class Contato extends ActiveRecord{
	protected array $relations = [
		'usuario' => [ self::BELONGS_TO, User::class, 'id_usuario' ],
		'usuario_com_referencia_de_volta' => [ self::BELONGS_TO, User::class, 'id_usuario', [], 'contato' ],
	];
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'contatos');
	}
}
```

Agora que as referências estão configuradas, podemos usá-las muito facilmente!

```php
$user = new User($conexao_pdo);

// encontrar o usuário mais recente.
$user->notNull('id')->orderBy('id desc')->find();

// obter contatos usando a relação:
foreach($user->contatos as $contato) {
	echo $contato->id;
}

// ou podemos ir pelo outro caminho.
$contato = new Contato();

// encontrar um contato
$contato->find();

// obter usuário usando a relação:
echo $contato->usuario->nome; // este é o nome do usuário
```

Muito legal, né?

## Definindo Dados Personalizados
Às vezes, você pode precisar anexar algo único ao seu ActiveRecord, como um cálculo personalizado que pode ser mais fácil de anexar ao objeto para ser passado, por exemplo, para um modelo.

#### `setCustomData(string $field, mixed $value)`
Você anexa os dados personalizados com o método `setCustomData()`.
```php
$user->setCustomData('contagem_visualizacoes_pagina', $contagem_visualizacoes_pagina);
```

E então você simplesmente o referencia como uma propriedade normal do objeto.

```php
echo $user->contagem_visualizacoes_pagina;
```

## Eventos

Uma característica muito incrível sobre esta biblioteca é sobre os eventos. Os eventos são acionados em determinados momentos com base em certos métodos que você chama. Eles são muito, muito úteis na configuração de dados automaticamente para você.

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

	protected function onConstruct(self $self, array &$config) { // não esqueça a referência de &
		// você poderia fazer isso para definir automaticamente a conexão
		$config['connection'] = Flight::db();
		// ou isso
		$self->transformAndPersistConnection(Flight::db());
		
		// Você também pode definir o nome da tabela desta forma.
		$config['table'] = 'usuarios';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Isso provavelmente só é útil se você precisar de uma manipulação da consulta toda vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function beforeFind(self $self) {
		// sempre executar id >= 0 se for do seu interesse
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este é provavelmente mais útil se você precisar executar alguma lógica sempre que este registro for buscado. Você precisa descriptografar algo? Precisa executar uma consulta de contagem personalizada toda vez (não performático, mas tanto faz)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function afterFind(self $self) {
		// descriptografando algo
		$self->secreto = suaFuncaoDescriptografar($self->secreto, $alguma_chave);

		// talvez armazenando algo personalizado como uma consulta???
		$self->setCustomData('contagem_visualizacoes', $self->selecionar('COUNT(*) contagem')->de('visualizacoes_usuarios')->eq('id_usuario', $self->id)['contagem']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Isso provavelmente só é útil se você precisar de uma manipulação da consulta toda vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function beforeFindAll(self $self) {
		// sempre executar id >= 0 se for do seu interesse
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar a `afterFind()` mas você faz isso para todos os registros!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// faça algo legal como em afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil se você precisa que alguns valores padrão sejam definidos sempre.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function beforeInsert(self $self) {
		// definir alguns valores padrão
		if(!$self->data_criacao) {
			$self->data_criacao = gmdate('Y-m-d');
		}

		if(!$self->senha) {
			$self->senha = password_hash((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

Talvez você tenha um caso de uso para alterar dados após a inserção?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// faça o que quiser
		Flight::cache()->set('id_insercao_mais_recente', $self->id);
		// ou qualquer outra coisa....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil se você precisa que alguns valores padrão sejam definidos sempre em uma atualização.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function beforeInsert(self $self) {
		// definir alguns valores padrão
		if(!$self->data_atualizacao) {
			$self->data_atualizacao = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Talvez você tenha um caso de uso para alterar dados após uma atualização?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// faça o que quiser
		Flight::cache()->set('id_usuario_atualizado_recentemente', $self->id);
		// ou qualquer outra coisa....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Isso é útil se você quer que eventos aconteçam tanto quando inserções quanto atualizações acontecem. Vou poupar a longa explicação, mas tenho certeza de que você pode adivinhar do que se trata.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function beforeSave(self $self) {
		$self->ultima_atualizacao = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Não tenho certeza do que você gostaria de fazer aqui, mas sem julgamentos aqui! Vá em frente!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function beforeDelete(self $self) {
		echo 'Ele foi um bravo soldado... :cry-face:';
	} 
}
```

## Gerenciamento de Conexão com o Banco de Dados

Quando você está usando esta biblioteca, você pode definir a conexão do banco de dados de algumas maneiras diferentes. Você pode definir a conexão no construtor, você pode configurá-la via uma variável de configuração `$config['connection']` ou você pode configurá-la via `setDatabaseConnection()` (v0.4.1).

```php
$conexao_pdo = new PDO('sqlite:test.db'); // por exemplo
$user = new User($conexao_pdo);
// ou
$user = new User(null, [ 'connection' => $conexao_pdo ]);
// ou
$user = new User();
$user->setDatabaseConnection($conexao_pdo);
```

Se você precisar atualizar a conexão do banco de dados, por exemplo, se estiver executando um script CLI de longa execução e precisar atualizar a conexão periodicamente, você pode redefinir a conexão com `$seu_registro->setDatabaseConnection($conexao_pdo)`.

## Contribuição

Por favor faça. :D

## Configuração

Ao contribuir, certifique-se de executar `composer test-coverage` para manter 100% de cobertura dos testes (isso não é uma cobertura verdadeira de teste unitário, mais como testes de integração).

Também certifique-se de executar `composer beautify` e `composer phpcs` para corrigir quaisquer erros de lint.

## Licença

MIT