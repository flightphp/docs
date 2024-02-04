# FlightPHP Active Record 

Um registro ativo está mapeando uma entidade de banco de dados para um objeto PHP. Falando claramente, se você tem uma tabela de usuários em seu banco de dados, você pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` em sua base de código. Veja [exemplo básico](#basic-example).

## Exemplo Básico

Vamos supor que você tenha a seguinte tabela:

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
	public function __construct($conexao_banco_de_dados)
	{
		// você pode configurá-lo desta forma
		parent::__construct($conexao_banco_de_dados, 'users');
		// ou desta forma
		parent::__construct($conexao_banco_de_dados, null, [ 'table' => 'users']);
	}
}
```

Agora veja a mágica acontecer!

```php
// para sqlite
$conexao_banco_de_dados = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

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
$user->password = password_hash('alguma senha legal novamente!!!');
$user->insert();
// não pode usar $user->save() aqui ou ele pensará que é uma atualização!

echo $user->id; // 2
```

E foi fácil adicionar um novo usuário! Agora que existe uma linha de usuário no banco de dados, como você a recupera?

```php
$user->find(1); // encontra id = 1 no banco de dados e o retorna.
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

Veja como isso é divertido? Vamos instalar e começar!

## Instalação

Simplesmente instale com o Composer

```php
composer require flightphp/active-record 
```

## Uso

Isso pode ser usado como uma biblioteca independente ou com o Framework Flight PHP. Completamente com você.

### Independente
Certifique-se apenas de passar uma conexão PDO para o construtor.

```php
$conexao_pdo = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

$User = new User($conexao_pdo);
```

### Framework Flight PHP
Se você estiver usando o Framework Flight PHP, você pode registrar a classe ActiveRecord como um serviço (mas você honestamente não precisa).

```php
Flight::register('user', 'User', [ $conexao_pdo ]);

// então você pode usá-lo assim em um controlador, uma função, etc.

Flight::user()->find(1);
```

## Referência da API
### Funções de CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encontra um registro e o atribui ao objeto atual. Se você passar um `$id` de algum tipo, ele realizará uma pesquisa na chave primária com esse valor. Se nada for passado, ele apenas encontrará o primeiro registro na tabela.

Além disso, você pode passar outros métodos auxiliares para consultar sua tabela.

```php
// encontra um registro com algumas condições antecipadamente
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
$user->email = 'teste@exemplo.com';
$user->update();
```

#### `delete(): boolean`

Exclui o registro atual do banco de dados.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Dados sujos se referem aos dados que foram alterados em um registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sujo" até este ponto.

$user->email = 'teste@exemplo.com'; // agora o e-mail é considerado "sujo" porque foi alterado.
$user->update();
// agora não há dados sujos porque foi atualizado e persistido no banco de dados

$user->password = password_hash()'novasenha'); // agora isso está sujo
$user->dirty(); // passar nada limpará todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi capturado como sujo.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('uma senha diferente') ]);
$user->update(); // tanto o nome quanto a senha são atualizados.
```

### Métodos de Consulta SQL
#### `select(string $field1 [, string $field2 ... ])`

Você pode selecionar apenas alguns dos campos em uma tabela se desejar (é mais eficiente em tabelas muito largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Você pode escolher tecnicamente outra tabela também! Por que diabos não?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $nome_tabela, string $condição_junção)`

Você também pode fazer junção com outra tabela no banco de dados.

```php
$user->join('contatos', 'contatos.user_id = usuarios.id')->find();
```

#### `where(string $condições_where)`

Você pode definir alguns argumentos where personalizados (você não pode definir parâmetros nesta declaração where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Segurança** - Você pode ser tentado a fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Por favor, NÃO FAÇA ISSO!!! Isso é suscetível ao que é conhecido como ataques de injeção de SQL. Existem muitos artigos online, por favor, pesquise "ataques de injeção de sql php" no Google e você encontrará muitos artigos sobre esse assunto. A maneira correta de lidar com isso com esta biblioteca é, em vez do método `where()`, você faria algo mais como `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Agrupa seus resultados por uma condição específica.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Classifique a consulta retornada de uma maneira específica.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Limita a quantidade de registros retornados. Se um segundo int for dado, será offset, limite como em SQL.

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

Onde `field IN($value)` ou `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Onde `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### Relacionamentos
Você pode configurar vários tipos de relacionamentos usando esta biblioteca. Você pode definir relacionamentos um->muitos e um->um entre tabelas. Isso requer uma configuração extra no classe antecipadamente.

Configurar o array `$relations` não é difícil, mas adivinhar a sintaxe correta pode ser confuso.

```php
protected array $relations = [
	// você pode nomear a chave como quiser. O nome do ActiveRecord é provavelmente bom. Ex: usuário, contato, cliente
	'active_record_qualquer' => [
		// obrigatório
		self::HAS_ONE, // este é o tipo de relacionamento

		// obrigatório
		'Alguma_Classe', // esta é a classe ActiveRecord "outra" a que isso fará referência

		// obrigatório
		'chave_local', // esta é a chave_local que faz referência à junção.
		// só para você saber, isso também só se junta à chave primária do modelo "outro"

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que você deseja executar. [] se você não quiser nenhum.

		// opcional
		'nome_referência_retorno' // isso é se você deseja referenciar esse relacionamento de volta a si mesmo Ex: $user->contato->usuario;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contatos' => [ self::HAS_MANY, Contato::class, 'id_usuario' ],
		'contato' => [ self::HAS_ONE, Contact::class, 'id_usuario' ],
	];

	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'usuario' => [ self::BELONGS_TO, User::class, 'id_usuario' ],
		'usuario_com_referencia' => [ self::BELONGS_TO, User::class, 'id_usuario', [], 'contato' ],
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

// encontra o usuário mais recente.
$user->notNull('id')->orderBy('id desc')->find();

// obtenha contatos usando relação:
foreach($user->contatos as $contato) {
	echo $contato->id;
}

// ou podemos ir no caminho oposto.
$contato = new Contact();

// encontra um contato
$contato->find();

// obtenha usuário usando relação:
echo $contato->usuario->name; // este é o nome do usuário
```

Bastante legal, não é?

### Definindo Dados Personalizados
Às vezes, você pode precisar anexar algo único ao seu ActiveRecord, como um cálculo personalizado que pode ser mais fácil de anexar ao objeto que seria então passado para um modelo, por exemplo.

#### `setCustomData(string $campo, mixed $valor)`
Você anexa os dados personalizados com o método `setCustomData()`.
```php
$user->setCustomData('contador_pagina_visualizacao', $contagem_visualizacao_pagina);
```

E então você simplesmente faz referência a ele como uma propriedade de objeto normal.

```php
echo $user->contador_pagina_visualizacao;
```

### Eventos

Um recurso super incrível a mais sobre esta biblioteca é sobre eventos. Os eventos são acionados em momentos específicos com base em certos métodos que você chama. Eles são muito, muito úteis para configurar dados automaticamente para você.

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
		$config['conexao'] = Flight::db();
		// ou isso
		$self->transformAndPersistConnection(Flight::db());
		
		// Você também pode definir o nome da tabela desta maneira.
		$config['tabela'] = 'usuarios';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Isso provavelmente só é útil se você precisar de manipulação de consulta sempre.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function beforeFind(self $self) {
		// sempre executa id >= 0 se for do seu gosto
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este é provavelmente mais útil se você sempre precisar executar alguma lógica sempre que esse registro for buscado. Você precisa descriptografar algo? Você precisa executar uma consulta de contagem personalizada toda vez (não performático, mas tudo bem)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function afterFind(self $self) {
		// descriptografando algo
		$self->segredo = suaFuncaoDescriptografar($self->segredo, $alguma_chave);

		// talvez armazenando algo customizado como uma consulta???
		$self->setCustomData('contagem_visualizações', $self->select('COUNT(*) count')->from('visualizações_usuarios')->eq('id_usuario', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Isso provavelmente só é útil se você precisar de manipulação de consultas sempre.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function beforeFindAll(self $self) {
		// sempre executa id >= 0 se for do seu gosto
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Semelhante ao `afterFind()` mas você pode aplicar a todos os registros em vez disso!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// faça algo legal como afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil se você precisar de alguns valores padrão definidos sempre.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function beforeInsert(self $self) {
		// defina alguns valores padrão
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

Talvez você tenha um caso de uso para alterar dados após serem inseridos?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// você decide
		Flight::cache()->set('id_inserido_mais_recente', $self->id);
		// ou qualquer outra coisa....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil se você precisar de alguns valores padrão definidos sempre em uma atualização.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function beforeInsert(self $self) {
		// defina alguns valores padrão
		if(!$self->data_atualizacao) {
			$self->data_atualizacao = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Talvez você tenha um caso de uso para alterar dados após serem atualizados?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'usuarios');
	}

	protected function afterInsert(self $self) {
		// você decide
		Flight::cache()->set('id_usuario_atualizado_mais_recente', $self->id);
		// ou qualquer outra coisa....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Isso é útil se você quiser que eventos aconteçam tanto quando inserções ou atualizações acontecerem. Vou poupar você da longa explicação, mas tenho certeza de que você pode adivinhar o que é.

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

## Contribuição

Por favor, sim.

### Configuração

Ao contribuir, certifique-se de executar `composer test-coverage` para manter uma cobertura de teste de 100% (isso não é uma verdadeira cobertura de teste unitário, mais como teste de integração).

Certifique-se também de executar `composer beautify` e `composer phpcs` para corrigir quaisquer erros de lint.

## Licença

MIT