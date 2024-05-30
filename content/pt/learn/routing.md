## Encaminhamento

> **Nota:** Quer entender mais sobre encaminhamento? Confira a página ["por que um framework?"](/learn/why-frameworks) para uma explicação mais detalhada.

O encaminhamento básico no Flight é feito combinando um padrão de URL com uma função de retorno ou um array de uma classe e método.

```php
Flight::route('/', function(){
    echo 'olá mundo!';
});
```

> As rotas são combinadas na ordem em que são definidas. A primeira rota a corresponder a uma requisição será invocada.

### Callbacks/Funções
O callback pode ser qualquer objeto que seja invocável. Então você pode usar uma função regular:

```php
function hello(){
    echo 'olá mundo!';
}

Flight::route('/', 'hello');
```

### Classes
Você também pode usar um método estático de uma classe:

```php
class Saudacao {
    public static function hello() {
        echo 'olá mundo!';
    }
}

Flight::route('/', [ 'Saudacao','hello' ]);
```

Ou criando um objeto primeiro e depois chamando o método:

```php

// Saudacao.php
class Saudacao
{
    public function __construct() {
        $this->name = 'João da Silva';
    }

    public function hello() {
        echo "Olá, {$this->name}!";
    }
}

// index.php
$saudacao = new Saudacao();

Flight::route('/', [ $saudacao, 'hello' ]);
// Também é possível fazer isso sem criar o objeto primeiro
// Nota: Nenhum argumento será injetado no construtor
Flight::route('/', [ 'Saudacao', 'hello' ]);
```

#### Injeção de Dependência via DIC (Container de Injeção de Dependência)
Se você deseja utilizar injeção de dependência via um container (PSR-11, PHP-DI, Dice, etc), o
único tipo de rotas em que isso está disponível é ou criando diretamente o objeto você mesmo
e usando o container para criar seu objeto ou você pode usar strings para definir a classe e
método a serem chamados. Você pode ir para a página de [Injeção de Dependência](/learn/extending) para 
mais informações.

Aqui está um exemplo rápido:

```php

use flight\database\PdoWrapper;

// Saudacao.php
class Saudacao
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// faça algo com $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Olá, mundo! Meu nome é {$name}!";
	}
}

// index.php

// Configure o container com os parâmetros necessários
// Veja a página de Injeção de Dependência para mais informações sobre PSR-11
$dice = new \Dice\Dice();

// Não se esqueça de reatribuir a variável com '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Registre o manipulador do container
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Rotas como de costume
Flight::route('/hello/@id', [ 'Saudacao', 'hello' ]);
// ou
Flight::route('/hello/@id', 'Saudacao->hello');
// ou
Flight::route('/hello/@id', 'Saudacao::hello');

Flight::start();
```