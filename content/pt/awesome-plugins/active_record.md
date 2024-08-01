# Flight Active Record 

Um registro ativo é mapear uma entidade de banco de dados para um objeto PHP. Falando claramente, se você tem uma tabela de usuários no seu banco de dados, você pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` em sua base de código. Veja [exemplo básico](#basic-example).

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
	public function __construct($conexão_banco_de_dados)
	{
		// você pode defini-la assim
		parent::__construct($conexão_banco_de_dados, 'users');
		// ou desta forma
		parent::__construct($conexão_banco_de_dados, null, [ 'table' => 'users']);
	}
}
```

Agora veja a mágica acontecer!

```php
// para sqlite
$conexão_banco_de_dados = new PDO('sqlite:test.db'); // isso é apenas um exemplo, provavelmente você usaria uma conexão de banco de dados real

// para mysql
$conexão_banco_de_dados = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'nome_de_usuário', 'senha');

// ou mysqli
$conexão_banco_de_dados = new mysqli('localhost', 'nome_de_usuário', 'senha', 'test_db');
// ou mysqli com criação não baseada em objeto
$conexão_banco_de_dados = mysqli_connect('localhost', 'nome_de_usuário', 'senha', 'test_db');

$user = new User($conexão_banco_de_dados);
$user->name = 'Bobby Tables';
$user->password = password_hash('alguma senha legal');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('outra senha legal novamente!!!');
$user->insert();
// não é possível usar $user->save() aqui senão pensará que é uma atualização!

echo $user->id; // 2
```

E foi tão fácil adicionar um novo usuário! Agora que há uma linha de usuário no banco de dados, como você a extrai?

```php
$user->find(1); // encontre id = 1 no banco de dados e retorne
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

Basta instalar com o Composer

```php
composer require flightphp/active-record 
```

## Uso

Isso pode ser usado como uma biblioteca independente ou com o Framework PHP Flight. Totalmente com você.

### Independente
Apenas certifique-se de passar uma conexão PDO para o construtor.

```php
$conexão_pdo = new PDO('sqlite:test.db'); // isso é apenas um exemplo, provavelmente você usaria uma conexão de banco de dados real

$User = new User($conexão_pdo);
```

> Não quer sempre definir sua conexão de banco de dados no construtor? Veja [Gerenciamento de Conexão de Banco de Dados](#database-connection-management) para outras ideias!

### Registrar como um método no Flight
Se estiver usando o Framework PHP Flight, você pode registrar a classe ActiveRecord como serviço, mas honestamente não precisa.

```php
Flight::register('user', 'User', [ $conexão_pdo ]);

// e então você pode usar assim em um controlador, uma função, etc.

Flight::user()->find(1);
```

## Métodos `runway`

[runway](https://docs.flightphp.com/awesome-plugins/runway) é uma ferramenta CLI para o Flight que possui um comando personalizado para esta biblioteca. 

```bash
# Uso
php runway make:record nome_tabela_banco_dados [nome_classe]

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
		// 'nome_relacao' => [ self::HAS_MANY, 'ClasseRelacionada', 'chave_estrang.'],
	];

    /**
     * Constructor
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

Encontre um registro e o atribua ao objeto atual. Se você passar um `$id` de algum tipo, ele fará uma busca na chave primária com esse valor. Se nada for passado, encontrará apenas o primeiro registro na tabela.

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

Retorna `true` se o registro atual foi hidratado (obtido do banco de dados).

```php
$user->find(1);
// se um registro for encontrado com dados...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Insere o registro atual no banco de dados.

```php
$user = new User($conexão_banco_de_dados);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Chaves Primárias Baseadas em Texto

Se você tiver uma chave primária baseada em texto (como um UUID), você pode definir o valor da chave primária antes de inserir de duas maneiras.

```php
$user = new User($conexão_banco_de_dados, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // ou $user->save();
```

ou você pode ter a chave primária gerada automaticamente para você por meio de eventos.

```php
class User extends flight\ActiveRecord {
	public function __construct($conexão_banco_de_dados)
	{
		parent::__construct($conexão_banco_de_dados, 'users', [ 'primaryKey' => 'uuid' ]);
		// você também pode definir a chave primária assim em vez do array acima.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // ou como você precisa gerar seus ids únicos
	}
}
```

Se você não definir a chave primária antes de inserir, ela será definida como o `rowid` e o
banco de dados a gerará para você, mas não será persistida porque esse campo pode não existir
em sua tabela. É por isso que é recomendado usar o evento para lidar automaticamente com isso
para você.

#### `update(): boolean|ActiveRecord`

Atualiza o registro atual no banco de dados.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Insere ou atualiza o registro atual no banco de dados. Se o registro tiver um id, ele atualizará, caso contrário, ele inserirá.

```php
$user = new User($conexão_banco_de_dados);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Nota:** Se você tiver relacionamentos definidos na classe, ele salvará recursivamente essas relações também se elas tiverem sido definidas, instanciadas e tiverem dados sujos para atualizar. (v0.4.0 e acima)

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

#### `dirty(array  $dirty = []): ActiveRecord`

Dados sujos se referem aos dados que foram alterados em um registro.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nada está "sujo" até este ponto.

$user->email = 'test@example.com'; // agora o email é considerado "sujo" porque foi alterado.
$user->update();
// agora não há dados sujos porque foram atualizados e persistidos no banco de dados

$user->password = password_hash()'nova senha'); // agora isso é sujo
$user->dirty(); // passando nada limpará todas as entradas sujas.
$user->update(); // nada será atualizado porque nada foi considerado sujo.

$user->dirty([ 'name' => 'algo', 'password' => password_hash('uma senha diferente') ]);
$user->update(); // tanto o nome quanto a senha são atualizados.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Isso é um alias para o método `dirty()`. É um pouco mais claro o que você está fazendo.

```php
$user->copyFrom([ 'name' => 'algo', 'password' => password_hash('uma senha diferente') ]);
$user->update(); // tanto o nome quanto a senha são atualizados.
```

#### `isDirty(): boolean` (v0.4.0)

Retorna `true` se o registro atual foi alterado.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Redefine o registro atual para seu estado inicial. Isso é realmente útil para usar em comportamentos de loop.
Se você passar `true`, também redefinirá os dados da consulta que foram usados para encontrar o objeto atual (comportamento padrão).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($conexão_banco_de_dados);

foreach($users as $user) {
	$user_company->reset(); // comece com uma tela limpa
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Depois de executar um método `find()`, `findAll()`, `insert()`, `update()` ou `save()`, você pode obter o SQL que foi construído e usá-lo para fins de depuração.

## Métodos de Consulta SQL
#### `select(string $campo1 [, string $campo2 ... ])`

Você pode selecionar apenas alguns dos campos em uma tabela, se desejar (é mais eficiente em tabelas realmente largas com muitas colunas)

```php
$user->select('id', 'name')->find();
```

#### `from(string $tabela)`

Você tecnicamente pode escolher outra tabela também! Por que não?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $nome_tabela, string $condição_junção)`

Você pode até juntar-se a outra tabela no banco de dados.

```php
$user->join('contatos', 'contatos.user_id = users.id')->find();
```

#### `where(string $condições_where)`

Você pode definir alguns argumentos where personalizados (você não pode definir parâmetros nesta declaração where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Nota de Segurança** - Você pode ser tentado a fazer algo como `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Por favor, NÃO FAÇA ISSO!!! Isso é susceptível a ataques de injeção de SQL. Há muitos artigos online, por favor, procure por "e...