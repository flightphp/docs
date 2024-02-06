# ActiveRecord do FlightPHP

Um registro ativo é mapear uma entidade de banco de dados para um objeto PHP. Falando claramente, se você tem uma tabela de usuários em seu banco de dados, você pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` em sua base de código. Veja [exemplo básico](#basic-example).

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
 * Uma classe ActiveRecord é geralmente singular
 * 
 * É altamente recomendável adicionar as propriedades da tabela como comentários aqui
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($conexao_de_banco_de_dados)
	{
		// você pode configurar assim
		parent::__construct($conexao_de_banco_de_dados, 'users');
		// ou assim
		parent::__construct($conexao_de_banco_de_dados, null, [ 'table' => 'users']);
	}
}
```

Agora veja a mágica acontecer!

```php
// para sqlite
$conexao_de_banco_de_dados = new PDO('sqlite:test.db'); // isto é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

// para mysql
$conexao_de_banco_de_dados = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nome_de_usuário', 'senha');

// ou mysqli
$conexao_de_banco_de_dados = new mysqli('localhost', 'nome_de_usuário', 'senha', 'test_db');
// ou mysqli com criação não baseada em objeto
$conexao_de_banco_de_dados = mysqli_connect('localhost', 'nome_de_usuário', 'senha', 'test_db');

$user = new User($conexao_de_banco_de_dados);
$user->name = 'Bobby Tables';
$user->password = password_hash('alguma senha legal');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('alguma senha legal novamente!!!');
$user->insert();
// não dá para usar $user->save() aqui senão ele pensará que é uma atualização!

echo $user->id; // 2
```

E foi fácil assim adicionar um novo usuário! Agora que há uma linha de usuário no banco de dados, como você a recupera?

```php
$user->find(1); // encontre id = 1 no banco de dados e retorne-o.
echo $user->name; // 'Bobby Tables'
```

E se você quiser encontrar todos os usuários?

```php
$users = $user->findAll();
```

E com uma certa condição?

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

Isso pode ser usado como uma biblioteca autônoma ou com o Framework PHP Flight. Completamente de sua escolha.

### Autônomo 
Apenas certifique-se de passar uma conexão PDO para o construtor.

```php
$conexao_pdo = new PDO('sqlite:test.db'); // isto é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

$User = new User($conexao_pdo);
```

### Framework PHP Flight
Se você estiver usando o Framework PHP Flight, você pode registrar a classe ActiveRecord como um serviço (mas honestamente, você não precisa).

```php
Flight::register('user', 'User', [ $conexao_pdo ]);

// então você pode usá-lo assim em um controlador, em uma função, etc.

Flight::user()->find(1);
```

## Referência da API
### Funções CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encontra um registro e o atribui ao objeto atual. Se você passar um `$id` de algum tipo, ele realizará uma pesquisa na chave primária com esse valor. Se nada for passado, encontrará apenas o primeiro registro na tabela.

Adicionalmente, você pode passar outros métodos auxiliares para consultar sua tabela.

```php
// encontre um registro com algumas condições antecipadas
$user->notNull('password')->orderBy('id DESC')->find();

// encontre um registro por um id específico
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Encontra todos os registros na tabela que você especificar.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Insere o registro atual no banco de dados.

```php
$user = new User($conexao_pdo);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Atualiza o registro atual no banco de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

Exclui o registro atual do banco de dados.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []) : ActiveRecord`

Dados sujos referem-se aos dados que foram alterados em um registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sujo" até este ponto.

$user->email = 'test@example.com'; // agora o email é considerado "sujo" pois foi alterado.
$user->update();
// agora não há dados sujos porque foram atualizados e persistidos no banco de dados

$user->password = password_hash()'novasenha'; // agora isso está sujo
$user->dirty(); // passar nada limpará todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi capturado como sujo.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('uma senha diferente') ]);
$user->update(); // tanto o nome quanto a senha são atualizados.
```

### Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Você pode selecionar apenas algumas das colunas em uma tabela, se desejar (é mais eficiente em tabelas muito largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Você tecnicamente pode escolher outra tabela também! Por que não?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Você também pode fazer uma junção com outra tabela no banco de dados.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Você pode definir alguns argumentos where personalizados (você não pode definir parâmetros nesta instrução where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Observação de Segurança** - Você pode ser tentado a fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. POR FAVOR, NÃO FAÇA ISSO!!! Isso é suscetível ao que é conhecido como ataques de injeção de SQL. Existem muitos artigos online, por favor, pesquise "sql injection attacks php" no Google e você encontrará muitos artigos sobre este assunto. A maneira correta de lidar com isso nesta biblioteca é em vez deste método `where()`, você faria algo mais como `$user->eq('id', $id)->eq('name', $name)->find();`

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

Limite a quantidade de registros retornados. Se um segundo int for fornecido, ele será offset, limit como em SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### Condições WHERE
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

Onde `field LIKE $value` or `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Onde `field IN($value)` or `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Onde `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### Relacionamentos
Você pode definir vários tipos de relacionamentos usando esta biblioteca. Você pode configurar relacionamentos de um para muitos e um para um entre tabelas. Isso requer um pouco mais de configuração na classe antecipadamente.

Configurar o array `$relations` não é difícil, mas adivinhar a sintaxe correta pode ser confuso.

```php
protected array $relations = [
	// você pode nomear a chave como quiser. O nome do ActiveRecord provavelmente é bom. Ex: usuário, contato, cliente
	'whatever_active_record' => [
		// obrigatório
		self::HAS_ONE, // este é o tipo de relacionamento

		// obrigatório
		'Some_Class', // esta é a classe ActiveRecord "outra" que isso fará referência

		// obrigatório
		'chave_local', // esta é a chave local que faz referência à junção.
		// apenas para sua informação, isso também se junta apenas à chave primária do modelo "outra"

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que você deseja executar. [] se você não quiser nenhum.

		// opcional
		'nome_de_referência_retroativa' // isso é se você quiser referenciar esse relacionamento de volta para si mesmo Ex: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($conexao_de_banco_de_dados)
	{
		parent::__construct($conexao_de_banco_de_dados, 'users');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($conexao_de_banco_de_dados)
	{
		parent::__construct($conexao_de_banco_de_dados, 'contacts');
	}
}
```

Agora temos as referências configuradas para que possamos usá-las facilmente!

```php
$user = new User($conexao_pdo);

// encontre o usuário mais recente.
$user->notNull('id')->orderBy('id desc')->find();

// obtenha contatos usando a relação:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou podemos ir na direção oposta.
$contact = new Contact();

// encontre um contato
$contact->find();

// obtenha o usuário usando a relação:
echo $contact->user->name; // este é o nome do usuário
```

Bem legal, não é?

### Definindo Dados Personalizados
Às vezes, você pode precisar anexar algo único ao seu ActiveRecord, como um cálculo personalizado que pode ser mais fácil apenas anexar ao objeto que seria então passado, digamos, a um modelo.

#### `setCustomData(string $field, mixed $value)`
Você anexa os dados personalizados com o método `setCustomData()`.
```php
$user->setCustomData('contagem_de_visualização_da_página', $contagem_de_visualização_da_página);
```

E então você simplesmente o referencia como uma propriedade normal do objeto.

```php
echo $user->contagem_de_visualização_da_página;
```

### Eventos

Outra funcionalidade super incrível sobre esta biblioteca é sobre eventos. Os eventos são acionados em determinados momentos com base em determinados métodos que você chama. Eles são muito úteis para configurar dados automaticamente para você.

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
		// você poderia fazer isso para configurar automaticamente a conexão
		$config['connection'] = Flight::db();
		// ou isso
		$self->transformAndPersistConnection(Flight::db());
		
		// Você também pode definir o nome da tabela desta forma.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Isso provavelmente só é útil se você precisar de uma manipulação na consulta todas as vezes.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_de_banco_de_dados)
	{
		parent::__construct($conexao_de_banco_de_dados, 'users');
	}

	protected function beforeFind(self $self) {
		// sempre executar id >= 0 se isso lhe interessar
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este é provavelmente mais útil se você sempre precisa executar alguma lógica toda vez que este registro for buscado. Você precisa descriptografar algo? Você precisa executar uma consulta de contagem personalizada a cada vez (não é eficiente, mas o que você quiser)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_de_banco_de_dados)
	{
		parent::__construct($conexao_de_banco_de_dados, 'users');
	}

	protected function afterFind(self $self) {
		// descriptografar algo
		$self->segredo = suaFunçãoDeDescriptografia($self->segredo, $alguma_chave);

		// talvez armazenando algo personalizado como uma consulta???
		$self->setCustomData('contagem_visualização', $self->select('COUNT(*) count')->from('visualizações_de_usuário')->eq('id_do_usuário', $user->id)['contagem']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Isso provavelmente só é útil se você precisar de uma manipulação na consulta todas as vezes.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_de_banco_de_dados)
	{
		parent::__construct($conexao_de_banco_de_dados, 'users');
	}

	protected function beforeFindAll(self $self) {
		// sempre executar id >= 0 se isso lhe interessar
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Similar `afterFind()` mas você pode fazer isso com todos os registros!

```php
```

## License

MIT