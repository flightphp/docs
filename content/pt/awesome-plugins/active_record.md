## Active Record do Flight

Um active record é mapear uma entidade de banco de dados para um objeto PHP. Falando claramente, se você tem uma tabela de usuários em seu banco de dados, você pode "traduzir" uma linha nessa tabela para uma classe `User` e um objeto `$user` em seu código-fonte. Consulte [exemplo básico](#exemplo-básico).

## Exemplo Básico

Vamos supor que você tenha a seguinte tabela:

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
 * Uma classe ActiveRecord é normalmente singular
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
		// você pode configurá-lo desta forma
		parent::__construct($database_connection, 'users');
		// ou desta forma
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Agora observe a mágica acontecer!

```php
// para sqlite
$database_connection = new PDO('sqlite:test.db'); // isto é apenas um exemplo, você provavelmente usaria uma conexão de banco de dados real

// para mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'usuário', 'senha');

// ou mysqli
$database_connection = new mysqli('localhost', 'usuário', 'senha', 'test_db');
// ou mysqli com criação não baseada em objeto
$database_connection = mysqli_connect('localhost', 'usuário', 'senha', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('alguma senha legal');
$user->insert();
// ou $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('outra senha legal!!!');
$user->insert();
// não pode usar $user->save() aqui senão pensará que é uma atualização!

echo $user->id; // 2
```

E foi tão fácil adicionar um novo usuário! Agora que existe uma linha de usuário no banco de dados, como você a retira?

```php
$user->find(1); // encontrar id = 1 no banco de dados e retorná-lo.
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

Veja como isso é divertido? Vamos instalá-lo e começar!

## Instalação

Simplesmente instale com o Composer

```php
composer require flightphp/active-record 
```