# FlightPHP Active Record

Um active record é mapear uma entidade de banco de dados para um objeto PHP. Falando de forma simples, se você tiver uma tabela de usuários no seu banco de dados, você pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` no seu código. Veja [exemplo básico](#exemplo-básico).

## Exemplo Básico

Vamos assumir que você tenha a seguinte tabela:

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
		// você pode configurar desta maneira
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
$conexao_banco_de_dados = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nome_de_usuário', 'senha');

// ou mysqli
$conexao_banco_de_dados = new mysqli('localhost', 'nome_de_usuário', 'senha', 'test_db');
// ou mysqli com criação não baseada em objeto
$conexao_banco_de_dados = mysqli_connect('localhost', 'nome_de_usuário', 'senha', 'test_db');

$user = new User($conexao_banco_de_dados);
$user->name = 'Bobby Tables';
$user->password = password_hash('alguma senha legal');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('outra senha legal novamente!!!');
$user->insert();
// não é possível usar $user->save() aqui ou ele pensará que é uma atualização!

echo $user->id; // 2
```

E foi tão fácil adicionar um novo usuário! Agora que existe uma linha de usuário no banco de dados, como você pode retirá-la?

```php
$user->find(1); // encontre id = 1 no banco de dados e retorne-o.
echo $user->name; // 'Bobby Tables'
```

E se você quiser encontrar todos os usuários?

```php
$usuários = $user->findAll();
```

E com uma determinada condição?

```php
$usuários = $user->like('name', '%mamma%')->findAll();
```

Veja como isso é divertido? Vamos instalar e começar!

## Instalação

Simplesmente instale com o Composer

```php
composer require flightphp/active-record 
```

## Uso

Isso pode ser usado como uma biblioteca independente ou com o Framework Flight PHP. Totalmente ao seu critério.

### Independente
Certifique-se de passar uma conexão PDO para o construtor.

```php
$conexao_pdo = new PDO('sqlite:test.db'); // isso é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

$User = new User($conexao_pdo);
```

### Framework Flight PHP
Se você estiver usando o Framework Flight PHP, você pode registrar a classe ActiveRecord como um serviço (mas honestamente, você não precisa).

```php
Flight::register('user', 'User', [ $conexao_pdo ]);

// então você pode usá-lo assim em um controlador, uma função, etc.

Flight::user()->find(1);
```

## Referência da API
### Funções CRUD

#### `find($id = null) : boolean|ActiveRecord`

Encontra um registro e atribui a este objeto atual. Se você passar um `$id` de algum tipo, ele realizará uma verificação na chave primária com esse valor. Se nada for passado, ele apenas encontrará o primeiro registro na tabela.

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

#### `dirty(array  $dirty = []): ActiveRecord`

Dados sujos se referem aos dados que foram alterados em um registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sujoe" neste momento.

$user->email = 'test@example.com'; // agora o email é considerado "sujo" pois foi alterado.
$user->update();
// agora não há dados sujos porque foram atualizados e persistidos no banco de dados

$user->password = password_hash()'novasenha'); // agora isso está sujo
$user->dirty(); // não passar nada irá limpar todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi capturado como sujo.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('uma senha diferente') ]);
$user->update(); // tanto nome quanto senha são atualizados.
```

### Métodos de Consulta SQL
#### `select(string $campo1 [, string $campo2 ... ])`

Você pode selecionar apenas alguns dos campos em uma tabela, se desejar (é mais performático em tabelas muito largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $tabela)`

Você pode escolher tecnicamente outra tabela também! Por que não?

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $nome_tabela, string $condição_join)`

Você até pode unir-se a outra tabela no banco de dados.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $condições_where)`

Você pode definir alguns argumentos where personalizados (você não pode definir parâmetros nesta declaração where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Segurança** - Você pode ser tentado a fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Por favor, NÃO FAÇA ISSO!!! Isso é suscetível ao que é conhecido como ataques de injeção de SQL. Existem muitos artigos online, pesquise por "ataques de injeção de sql php" e encontrará muitos artigos sobre esse assunto. A maneira correta de lidar com isso usando esta biblioteca é, em vez do método `where()`, você faria algo como `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $grupo_por_declaração)/groupBy(string $grupo_por_declaração)`

Agrupa seus resultados por uma determinada condição.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_declaração)/orderBy(string $order_by_declaração)`

Classifica a consulta retornada de determinada maneira.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limite)/limit(int $offset, int $limite)`

Limita a quantidade de registros retornados. Se for dado um segundo int, ele será o deslocamento, limite conforme SQL.

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
Você pode configurar vários tipos de relacionamentos usando esta biblioteca. Você pode configurar relacionamentos um->muitos e um->um entre tabelas. Isso requer um pequeno ajuste adicional na classe anteriormente.

Configurar a matriz `$relations` não é difícil, mas adivinhar a sintaxe correta pode ser confuso.

```php
protected array $relations = [
	// você pode nomear a chave como quiser. O nome do ActiveRecord é provavelmente bom. Por exemplo: user, contact, client
	'registro_ativo_qualquer' => [
		// obrigatório
		self::HAS_ONE, // este é o tipo de relacionamento

		// obrigatório
		'Alguma_Classe', // esta é a classe ActiveRecord "outra" que esta referência

		// obrigatório
		'chave_local', // esta é a chave_local que faz referência à junção.
		// apenas para sua informação, isso também se junta apenas à chave primária do modelo "outro"

		// opcional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // métodos personalizados que deseja executar. [] se você não deseja nenhum.

		// opcional
		'nome_de_referencia_retornada' // isto é se você deseja retornar este relacionamento de volta a si mesmo por exemplo: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_com_referencia' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'contacts');
	}
}
```

Agora temos as referências configuradas para que possamos usá-las muito facilmente!

```php
$user = new User($conexao_pdo);

// encontrar o usuário mais recente.
$user->notNull('id')->orderBy('id_desc')->find();

// obter contatos usando o relacionamento:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// ou podemos ir pelo outro caminho.
$contact = new Contact();

// encontrar um contato
$contact->find();

// obter usuário usando o relacionamento:
echo $contact->user->name; // este é o nome do usuário
```

Muito legal, não é?

### Configurando Dados Personalizados
Às vezes você pode precisar anexar algo único ao seu ActiveRecord, como um cálculo personalizado que talvez seja mais fácil apenas anexar ao objeto que seria passado para um modelo, por exemplo.

#### `setCustomData(string $campo, mixed $valor)`
Você anexa os dados personalizados com o método `setCustomData()`.
```php
$user->setCustomData('contagem_de_visualizações', $contagem_de_páginas);
```

E então você simplesmente faz referência a ele como uma propriedade de objeto normal.

```php
echo $user->contagem_de_páginas;
```

### Eventos

Uma característica realmente incrível sobre esta biblioteca são os eventos. Os eventos são acionados em certos momentos com base em determinados métodos que você chama. Eles são muito úteis para configurar dados automaticamente para você.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Isso é realmente útil se você precisa configurar uma conexão padrão ou algo assim.

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

Isso só é útil se você precisa de uma manipulação de consulta cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected function beforeFind(self $self) {
		// sempre execute id >= 0 se isso for o que você gosta
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Este é provavelmente mais útil se você sempre precisa executar alguma lógica toda vez que este registro é buscado. Você precisa descriptografar algo? Você precisa executar uma consulta de contagem personalizada toda vez (não é performático, mas tanto faz)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected function afterFind(self $self) {
		// descriptografando algo
		$self->secreto = suaFunçãoDeDescriptografia($self->secreto, $alguma_chave);

		// talvez armazenando algo personalizado como uma consulta???
		$self->setCustomData('contagem_de_visualizações', $self->select('COUNT(*) count')->from('visualizações_de_usuário')->eq('id_de_usuário', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Isso só é útil se você precisa de uma manipulação de consulta cada vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// sempre execute id >= 0 se isso for o que você gosta
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Semelhante ao `afterFind()` mas você pode fazer isso com todos os registros!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// faça algo legal como em afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Realmente útil se você precisa definir alguns valores padrão toda vez.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected function beforeInsert(self $self) {
		// defina alguns valores padrão
		if(!$self->data_criada) {
			$self->data_criada = gmdate('Y-m-d');
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
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected function afterInsert(self $self) {
		// você faça o que quiser
		Flight::cache()->set('id_de_inserção_mais_recente', $self->id);
		// ou o que quer que seja....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Realmente útil se você precisa definir alguns valores padrão toda vez em uma atualização.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($conexao_banco_de_dados)
	{
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected function beforeInsert(self $self) {
		// defina alguns valores padrão
		if(!$self->data_atualizada) {
			$self->data_atualizada = gmdate('Y-m-d');
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
		parent::__construct($conexao_banco_de_dados, 'users');
	}

	protected function afterInsert(self $self) {
		// você faça o que quiser
		Flight::cache()->set('id_de_usuário_atualizado_mais_recente', $self->id);
		// ou o que quer que seja....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Isso é útil se você deseja que eventos aconteçam tanto ao inserir quanto ao atualizar. Vou poupar você da longa explicação, mas tenho certeza que você pode adivinhar o que é.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeSave(self $self) {
		$self->última_atualização = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Não tenho certeza do que você gostaria de fazer aqui, mas sem julgamentos aqui! Vá em frente!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Ele foi um bravo soldado... :cry-face:';
	} 
}
```

## Contribuindo

Por favor, contribua.

### Configuração

Ao contribuir, certifique-se de executar `composer test-coverage` para manter a cobertura de testes em 100% (isso não é verdadeira cobertura de testes de unidade, mais como testes de integração).

Certifique-se também de executar `composer beautify` e `composer phpcs` para corrigir quaisquer erros de linting.

## Licença

MIT