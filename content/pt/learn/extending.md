# Extensão

Flight é projetado para ser um framework extensível. O framework vem com um conjunto
de métodos e componentes padrão, mas permite que você mapeie seus próprios métodos,
registre suas próprias classes ou até mesmo substitua classes e métodos existentes.

Se você está procurando por um DIC (Dependency Injection Container), vá para a
página [Dependency Injection Container](dependency-injection-container).

## Mapeamento de Métodos

Para mapear seu próprio método personalizado simples, você usa a função `map`:

```php
// Mapeie seu método
Flight::map('hello', function (string $name) {
  echo "olá $name!";
});

// Chame seu método personalizado
Flight::hello('Bob');
```

Embora seja possível criar métodos personalizados simples, é recomendado apenas criar
funções padrão em PHP. Isso tem autocompletar em IDEs e é mais fácil de ler.
O equivalente do código acima seria:

```php
function hello(string $name) {
  echo "olá $name!";
}

hello('Bob');
```

Isso é mais utilizado quando você precisa passar variáveis para o método para obter um valor esperado.
Usando o método `register()` como mostrado abaixo é mais para passar configuração
e então chamar sua classe pré-configurada.

## Registrando Classes

Para registrar sua própria classe e configurá-la, você usa a função `register`:

```php
// Registre sua classe
Flight::register('user', User::class);

// Obtenha uma instância de sua classe
$user = Flight::user();
```

O método de registro também permite que você passe parâmetros para o construtor de sua classe
Assim, quando você carrega sua classe personalizada, ela será pré-inicializada.
Você pode definir os parâmetros do construtor passando um array adicional.
Aqui está um exemplo de carregar uma conexão de banco de dados:

```php
// Registrar classe com parâmetros do construtor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtenha uma instância de sua classe
// Isso criará um objeto com os parâmetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// e se você precisasse mais tarde em seu código, basta chamar o mesmo método novamente
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Se você passar um parâmetro de retorno de chamada adicional, ele será executado imediatamente
após a construção da classe. Isso permite que você execute quaisquer procedimentos de configuração para o
novo objeto. A função de retorno de chamada recebe um parâmetro, uma instância do novo objeto.

```php
// O retorno de chamada receberá o objeto que foi construído
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por padrão, toda vez que você carrega sua classe, você obterá uma instância compartilhada.
Para obter uma nova instância de uma classe, basta passar `false` como parâmetro:

```php
// Instância compartilhada da classe
$compartilhado = Flight::db();

// Nova instância da classe
$nova = Flight::db(false);
```

Lembre-se de que os métodos mapeados têm precedência sobre as classes registradas. Se você
declarar ambos com o mesmo nome, apenas o método mapeado será invocado.

## Sobrepondo Métodos do Framework

Flight permite que você sobreponha sua funcionalidade padrão para atender às suas próprias necessidades,
sem ter que modificar nenhum código. Você pode ver todos os métodos que você pode sobrepor [aqui](/learn/api).

Por exemplo, quando o Flight não consegue corresponder uma URL a uma rota, ele invoca o método `notFound`
que envia uma resposta genérica de `HTTP 404`. Você pode substituir esse comportamento
usando o método `map`:

```php
Flight::map('notFound', function() {
  // Mostrar página de erro personalizada 404
  include 'errors/404.html';
});
```

Flight também permite que você substitua componentes centrais do framework.
Por exemplo, você pode substituir a classe de Router padrão por sua própria classe personalizada:

```php
// Registre sua classe personalizada
Flight::register('router', MyRouter::class);

// Quando o Flight carrega a instância do Router, ele carregará sua classe
$myrouter = Flight::router();
```

Métodos do Framework como `map` e `register` não podem ser sobrepostos. Você
irá receber um erro se tentar fazer isso.